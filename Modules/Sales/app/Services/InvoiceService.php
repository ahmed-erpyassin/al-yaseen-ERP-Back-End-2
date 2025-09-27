<?php

namespace Modules\Sales\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\InvoiceRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\app\Services\BookNumberingService;
use Modules\Customers\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\FinancialAccounts\Models\Currency;

class InvoiceService
{
    public function index(Request $request)
    {
        try {

            $customerSearch = $request->get('customer_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Sale::query()
                ->where('type', SalesTypeEnum::INVOICE)
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->whereHas('customer', function ($q) use ($customerSearch) {
                        $q->where('name', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing offers: ' . $e->getMessage());
        }
    }

    public function store(InvoiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;
                $branchId = $request->user()->branch_id ?? 1; // Default branch if not set

                // Initialize BookNumberingService
                $bookNumberingService = new BookNumberingService();

                // Generate book code and invoice number
                $bookingData = $bookNumberingService->generateInvoiceBookAndNumber($companyId, $request->journal_id);

                // Get customer details for auto-population
                $customer = Customer::find($request->customer_id);
                $customerEmail = $request->customer_email ?? $customer->email ?? null;

                // Get currency details and exchange rate
                $currency = Currency::find($request->currency_id);
                $exchangeRate = $request->exchange_rate ?? 1.0;

                // Prepare invoice data
                $invoiceData = [
                    'type' => SalesTypeEnum::INVOICE,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'status' => 'draft',

                    // Auto-generated fields
                    'book_code' => $bookingData['book_code'],
                    'invoice_number' => $bookingData['invoice_number'],
                    'journal_number' => $bookingData['journal_number'],
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),

                    // Customer information
                    'customer_id' => $request->customer_id,
                    'customer_email' => $customerEmail,
                    'licensed_operator' => $request->licensed_operator,

                    // Currency and financial
                    'currency_id' => $request->currency_id,
                    'exchange_rate' => $exchangeRate,
                    'due_date' => $request->due_date,

                    // Financial fields
                    'cash_paid' => $request->cash_paid ?? 0,
                    'checks_paid' => $request->checks_paid ?? 0,
                    'allowed_discount' => $request->allowed_discount ?? 0,
                    'discount_percentage' => $request->discount_percentage ?? 0,
                    'tax_percentage' => $request->tax_percentage ?? 0,
                    'is_tax_inclusive' => $request->is_tax_inclusive ?? false,
                    'notes' => $request->notes,

                    // Audit fields
                    'created_by' => $userId,
                ];

                // Create the invoice
                $invoice = Sale::create($invoiceData);

                // Process invoice items
                if ($request->has('items') && is_array($request->items)) {
                    $this->processInvoiceItems($invoice, $request->items, $companyId, $userId);
                }

                // Calculate totals
                $this->calculateInvoiceTotals($invoice);

                return $invoice->load(['items', 'customer', 'currency']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating sales invoice: ' . $e->getMessage());
        }
    }

    /**
     * Process invoice items with auto-population
     */
    private function processInvoiceItems(Sale $invoice, array $items, $companyId, $userId)
    {
        $serialNumber = 1;

        foreach ($items as $itemData) {
            // Get item details for auto-population
            $item = Item::find($itemData['item_id']);

            if (!$item) {
                continue; // Skip invalid items
            }

            // Auto-populate item details
            $itemNumber = $item->item_number ?? 'ITM-' . str_pad($item->id, 6, '0', STR_PAD_LEFT);
            $itemName = $item->item_name_en ?? $item->item_name_ar ?? 'Unknown Item';

            // Get unit price (first sale price from warehouse)
            $unitPrice = $itemData['unit_price'] ?? $item->first_sale_price ?? 0;

            // Calculate totals
            $quantity = $itemData['quantity'];
            $subtotal = $quantity * $unitPrice;
            $discountAmount = 0;
            $discountPercentage = $itemData['discount_rate'] ?? 0;

            if ($discountPercentage > 0) {
                $discountAmount = $subtotal * ($discountPercentage / 100);
            }

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = 0;
            $taxRate = $itemData['tax_rate'] ?? 0;

            if ($taxRate > 0) {
                $taxAmount = $afterDiscount * ($taxRate / 100);
            }

            $total = $afterDiscount + $taxAmount;

            // Create sale item
            SaleItem::create([
                'sale_id' => $invoice->id,
                'item_id' => $itemData['item_id'],
                'unit_id' => $itemData['unit_id'],
                'serial_number' => $serialNumber,
                'item_number' => $itemNumber,
                'item_name' => $itemName,
                'description' => $itemData['description'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_rate' => $discountPercentage,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'total_foreign' => $total,
                'total_local' => $total * $invoice->exchange_rate,
                'total' => $total,
            ]);

            $serialNumber++;
        }
    }

    /**
     * Calculate invoice totals
     */
    private function calculateInvoiceTotals(Sale $invoice)
    {
        $items = $invoice->items;

        $totalWithoutTax = $items->sum(function ($item) {
            $subtotal = $item->quantity * $item->unit_price;
            $discount = $subtotal * ($item->discount_rate / 100);
            return $subtotal - $discount;
        });

        $totalTax = $items->sum(function ($item) {
            $subtotal = $item->quantity * $item->unit_price;
            $discount = $subtotal * ($item->discount_rate / 100);
            $afterDiscount = $subtotal - $discount;
            return $afterDiscount * ($item->tax_rate / 100);
        });

        $totalAmount = $totalWithoutTax + $totalTax;
        $remainingBalance = $totalAmount - $invoice->cash_paid - $invoice->checks_paid;

        // Update invoice totals
        $invoice->update([
            'total_without_tax' => $totalWithoutTax,
            'tax_amount' => $totalTax,
            'total_foreign' => $totalAmount,
            'total_local' => $totalAmount * $invoice->exchange_rate,
            'total_amount' => $totalAmount,
            'remaining_balance' => $remainingBalance,
        ]);
    }

    /**
     * Show invoice details
     */
    public function show($id)
    {
        try {
            $invoice = Sale::with(['items.item', 'items.unit', 'customer', 'currency', 'employee'])
                ->where('type', SalesTypeEnum::INVOICE)
                ->findOrFail($id);

            return $invoice;
        } catch (Exception $e) {
            throw new \Exception('Error fetching invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update invoice
     */
    public function update(InvoiceRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $invoice = Sale::where('type', SalesTypeEnum::INVOICE)->findOrFail($id);

                // Check if invoice can be updated
                if ($invoice->status === 'invoiced') {
                    throw new \Exception('Cannot update an invoiced invoice');
                }

                $data = $request->validated();
                $userId = $request->user()->id;

                // Update basic invoice data
                $invoice->update(array_merge($data, [
                    'updated_by' => $userId,
                ]));

                // Update items if provided
                if ($request->has('items') && is_array($request->items)) {
                    // Delete existing items
                    $invoice->items()->delete();

                    // Create new items
                    $this->processInvoiceItems($invoice, $request->items, $invoice->company_id, $userId);
                }

                // Recalculate totals
                $this->calculateInvoiceTotals($invoice);

                return $invoice->load(['items', 'customer', 'currency']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error updating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete invoice
     */
    public function destroy($id)
    {
        try {
            $invoice = Sale::where('type', SalesTypeEnum::INVOICE)->findOrFail($id);

            // Check if invoice can be deleted
            if ($invoice->status === 'invoiced') {
                throw new \Exception('Cannot delete an invoiced invoice');
            }

            $invoice->delete();
            return true;
        } catch (Exception $e) {
            throw new \Exception('Error deleting invoice: ' . $e->getMessage());
        }
    }
}
