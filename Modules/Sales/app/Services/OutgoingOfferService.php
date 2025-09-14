<?php

namespace Modules\Sales\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request as Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\OutgoingOfferRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;

class OutgoingOfferService
{

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
                $companyId = 101 ?? $request->company_id;
                $userId = $request->user()->id ?? $request->user_id;

                $data = [
                    'type' => SalesTypeEnum::QUOTATION,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'status' => 'draft',
                    'created_by' => $userId,
                ] + $request->validated();

                // Calculate totals
                $this->calculateTotals($data, $request->validated()['items']);

                $offer = Sale::create($data);

                // Create sale items
                $this->createSaleItems($offer, $request->validated()['items']);

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
        $totalWithoutTax = 0;
        $totalTaxAmount = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $discount = $subtotal * ($item['discount_rate'] / 100);
            $afterDiscount = $subtotal - $discount;
            $tax = $afterDiscount * ($item['tax_rate'] / 100);

            $totalWithoutTax += $afterDiscount;
            $totalTaxAmount += $tax;
            $totalAmount += $afterDiscount + $tax;
        }

        $data['total_without_tax'] = $totalWithoutTax;
        $data['tax_amount'] = $totalTaxAmount;
        $data['total_amount'] = $totalAmount;
        $data['remaining_balance'] = $totalAmount - ($data['cash_paid'] ?? 0) - ($data['checks_paid'] ?? 0);
    }

    private function createSaleItems($offer, $items)
    {
        foreach ($items as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $discount = $subtotal * ($item['discount_rate'] / 100);
            $afterDiscount = $subtotal - $discount;
            $tax = $afterDiscount * ($item['tax_rate'] / 100);

            SaleItem::create([
                'sale_id' => $offer->id,
                'item_id' => $item['item_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_rate' => $item['discount_rate'],
                'tax_rate' => $item['tax_rate'],
                'total_foreign' => $item['total_foreign'] ?? $afterDiscount + $tax,
                'total_local' => $item['total_local'] ?? $afterDiscount + $tax,
                'total' => $item['total'] ?? $afterDiscount + $tax,
            ]);
        }
    }
}
