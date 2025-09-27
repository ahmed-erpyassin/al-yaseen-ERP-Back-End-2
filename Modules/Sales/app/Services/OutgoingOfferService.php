<?php

namespace Modules\Sales\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request as Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\app\Services\BookNumberingService;
use Modules\Sales\app\Services\SalesCalculationService;
use Modules\Sales\Http\Requests\OutgoingOfferRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Customers\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\ExchangeRate;
use Modules\Companies\Models\Company;

class OutgoingOfferService
{
    protected BookNumberingService $bookNumberingService;
    protected SalesCalculationService $calculationService;

    public function __construct(
        BookNumberingService $bookNumberingService,
        SalesCalculationService $calculationService
    ) {
        $this->bookNumberingService = $bookNumberingService;
        $this->calculationService = $calculationService;
    }

    public function index(Request $request)
    {
        try {
            $customerSearch = $request->get('customer_search', null);
            $status = $request->get('status', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 15);

            $query = Sale::query()
                ->with(['customer', 'currency', 'user', 'items'])
                ->where('type', SalesTypeEnum::QUOTATION)
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->whereHas('customer', function ($q) use ($customerSearch) {
                        $q->where('company_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('first_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('second_name', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->when($status, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->orderBy($sortBy, $sortOrder);

            return $request->has('per_page') ? $query->paginate($perPage) : $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing offers: ' . $e->getMessage());
        }
    }

    public function store(OutgoingOfferRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id ?? 101;
                $userId = $request->user()->id;
                $validatedData = $request->validated();

                // Generate book code and invoice number
                $numberingData = $this->bookNumberingService->generateBookAndInvoiceNumber(
                    $companyId,
                    $validatedData['journal_id'] ?? null
                );

                // Get customer data for auto-population
                $customer = Customer::find($validatedData['customer_id']);

                // Get currency exchange rate
                $exchangeRate = $this->getCurrencyExchangeRate($validatedData['currency_id']);

                $data = [
                    'type' => SalesTypeEnum::QUOTATION,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'status' => 'draft',
                    'created_by' => $userId,

                    // Auto-generated fields
                    'code' => $numberingData['book_code'],
                    'invoice_number' => (string) $numberingData['invoice_number'],
                    'journal_number' => $numberingData['journal_number'],
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),

                    // Customer contact fields (auto-populated from customer if not provided)
                    'email' => $validatedData['email'] ?? $customer?->email,
                    'licensed_operator' => $validatedData['licensed_operator'] ?? $customer?->licensed_operator,

                    // Exchange rate
                    'exchange_rate' => $exchangeRate,
                ] + $validatedData;

                // Calculate totals
                $this->calculateTotals($data, $validatedData['items']);

                $offer = Sale::create($data);

                // Create sale items with enhanced data
                $this->createSaleItems($offer, $validatedData['items']);

                return $offer->load(['customer', 'currency', 'items']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating outgoing offer: ' . $e->getMessage());
        }
    }

    public function update(OutgoingOfferRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $offer = Sale::quotations()->findOrFail($id);

                if ($offer->status !== 'draft') {
                    throw new \Exception('Cannot update offer that is not in draft status');
                }

                $data = $request->validated();
                $data['updated_by'] = $request->user()->id;

                // Calculate totals
                $this->calculateTotals($data, $request->validated()['items']);

                $offer->update($data);

                // Update sale items
                $offer->items()->delete();
                $this->createSaleItems($offer, $request->validated()['items']);

                return $offer->load(['customer', 'currency', 'items']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error updating outgoing offer: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $offer = Sale::quotations()->findOrFail($id);

            if ($offer->status !== 'draft') {
                throw new \Exception('Cannot delete offer that is not in draft status');
            }

            $offer->delete();
            return true;
        } catch (Exception $e) {
            throw new \Exception('Error deleting outgoing offer: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        try {
            $offer = Sale::quotations()->findOrFail($id);

            if ($offer->status !== 'draft') {
                throw new \Exception('Only draft offers can be approved');
            }

            $offer->update(['status' => 'approved']);
            return $offer;
        } catch (Exception $e) {
            throw new \Exception('Error approving outgoing offer: ' . $e->getMessage());
        }
    }

    public function send($id)
    {
        try {
            $offer = Sale::quotations()->findOrFail($id);

            if (!in_array($offer->status, ['draft', 'approved'])) {
                throw new \Exception('Only draft or approved offers can be sent');
            }

            $offer->update(['status' => 'sent']);
            return $offer;
        } catch (Exception $e) {
            throw new \Exception('Error sending outgoing offer: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        try {
            $offer = Sale::quotations()->findOrFail($id);

            if ($offer->status === 'invoiced') {
                throw new \Exception('Cannot cancel an invoiced offer');
            }

            $offer->update(['status' => 'cancelled']);
            return $offer;
        } catch (Exception $e) {
            throw new \Exception('Error cancelling outgoing offer: ' . $e->getMessage());
        }
    }

    private function calculateTotals(&$data, $items)
    {
        // Use the comprehensive calculation service
        $this->calculationService->calculateSaleTotals($data, $items);

        // Calculate remaining balance
        $data['remaining_balance'] = $this->calculationService->calculateRemainingBalance(
            $data['total_amount'],
            $data['cash_paid'] ?? 0,
            $data['checks_paid'] ?? 0
        );
    }

    private function createSaleItems($offer, $items)
    {
        foreach ($items as $itemData) {
            // Get item details for auto-population
            $item = Item::find($itemData['item_id']);

            $saleItemData = [
                'sale_id' => $offer->id,
                'item_id' => $itemData['item_id'],
                'unit_id' => $itemData['unit_id'] ?? $item?->unit_id,
                'item_number' => $itemData['item_number'] ?? $item?->item_number,
                'item_name' => $itemData['item_name'] ?? $item?->name,
                'description' => $itemData['description'] ?? $item?->description,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'] ?? $item?->first_sale_price ?? 0,
                'discount_rate' => $itemData['discount_rate'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
            ];

            // Calculate item total
            $subtotal = $saleItemData['quantity'] * $saleItemData['unit_price'];
            $discount = $subtotal * ($saleItemData['discount_rate'] / 100);
            $afterDiscount = $subtotal - $discount;
            $tax = $afterDiscount * ($saleItemData['tax_rate'] / 100);
            $total = $afterDiscount + $tax;

            $saleItemData['total_foreign'] = $itemData['total_foreign'] ?? $total;
            $saleItemData['total_local'] = $itemData['total_local'] ?? $total;
            $saleItemData['total'] = $itemData['total'] ?? $total;

            SaleItem::create($saleItemData);
        }
    }

    /**
     * Get currency exchange rate
     */
    private function getCurrencyExchangeRate($currencyId): float
    {
        // Try to get the latest exchange rate
        $exchangeRate = ExchangeRate::where('currency_id', $currencyId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($exchangeRate) {
            return $exchangeRate->rate;
        }

        // Fallback to default exchange rate
        return 1.0;
    }
}
