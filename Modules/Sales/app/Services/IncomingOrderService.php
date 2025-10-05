<?php

namespace Modules\Sales\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\IncomingOrderRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Customers\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Companies\Models\Company;

class IncomingOrderService
{
    public function index(Request $request)
    {
        try {
            $query = Sale::query()
                ->where('type', SalesTypeEnum::INCOMING_ORDER)
                ->with(['customer', 'currency', 'employee', 'branch', 'company', 'items']);

            // Search by Order Number (from/to range)
            if ($request->filled('order_number_from')) {
                $query->where('invoice_number', '>=', $request->order_number_from);
            }
            if ($request->filled('order_number_to')) {
                $query->where('invoice_number', '<=', $request->order_number_to);
            }
            if ($request->filled('order_number')) {
                $query->where('invoice_number', 'like', '%' . $request->order_number . '%');
            }

            // Search by Customer Name
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('company_name', 'like', '%' . $request->customer_name . '%')
                      ->orWhere('first_name', 'like', '%' . $request->customer_name . '%')
                      ->orWhere('second_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            // Search by Date (exact date or date range)
            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            // Search by Amount (range)
            if ($request->filled('amount_from')) {
                $query->where('total_amount', '>=', $request->amount_from);
            }
            if ($request->filled('amount_to')) {
                $query->where('total_amount', '<=', $request->amount_to);
            }
            if ($request->filled('amount')) {
                $query->where('total_amount', $request->amount);
            }

            // Search by Currency
            if ($request->filled('currency_id')) {
                $query->where('currency_id', $request->currency_id);
            }

            // Search by Licensed Operator
            if ($request->filled('licensed_operator')) {
                $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
            }

            // Search by Status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by Book Code
            if ($request->filled('book_code')) {
                $query->where('book_code', 'like', '%' . $request->book_code . '%');
            }

            // General search across multiple fields
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('invoice_number', 'like', '%' . $searchTerm . '%')
                      ->orWhere('book_code', 'like', '%' . $searchTerm . '%')
                      ->orWhere('licensed_operator', 'like', '%' . $searchTerm . '%')
                      ->orWhere('notes', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                          $customerQuery->where('company_name', 'like', '%' . $searchTerm . '%')
                                       ->orWhere('first_name', 'like', '%' . $searchTerm . '%')
                                       ->orWhere('second_name', 'like', '%' . $searchTerm . '%')
                                       ->orWhere('customer_number', 'like', '%' . $searchTerm . '%');
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort fields
            $allowedSortFields = [
                'id', 'invoice_number', 'book_code', 'date', 'time', 'due_date',
                'total_amount', 'total_without_tax', 'tax_amount', 'exchange_rate',
                'status', 'licensed_operator', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100); // Limit to 100 items per page

            return $query->paginate($perPage);

        } catch (\Exception $e) {
            throw new \Exception('Error fetching incoming orders: ' . $e->getMessage());
        }
    }

    public function store(IncomingOrderRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();
                $companyId = $user->company_id ?? $request->company_id ?? 1;
                $userId = $user->id;

                $data = $request->validated();

                // Auto-generate fields if not provided
                if (empty($data['book_code'])) {
                    $data['book_code'] = Sale::generateBookCode($companyId);
                }

                if (empty($data['invoice_number'])) {
                    $data['invoice_number'] = Sale::generateInvoiceNumber($companyId);
                }

                if (empty($data['date'])) {
                    $data['date'] = now()->toDateString();
                }

                if (empty($data['time'])) {
                    $data['time'] = now()->toTimeString();
                }

                // Get live exchange rate if needed
                if (!empty($data['currency_id'])) {
                    $currency = Currency::find($data['currency_id']);
                    if ($currency && $currency->code !== 'USD') {
                        $liveRate = $this->getLiveExchangeRate($currency->code);
                        if ($liveRate) {
                            $data['exchange_rate'] = $liveRate;
                        }
                    }
                }

                // Set default values
                $data['type'] = SalesTypeEnum::INCOMING_ORDER;
                $data['company_id'] = $companyId;
                $data['user_id'] = $userId;
                $data['status'] = $data['status'] ?? 'draft';
                $data['created_by'] = $userId;
                $data['updated_by'] = $userId;

                // Calculate totals
                $this->calculateTotals($data, $request->validated()['items']);

                $order = Sale::create($data);

                // Create sale items
                $this->createSaleItems($order, $request->validated()['items']);

                return $order->load(['customer', 'currency', 'items', 'employee', 'branch']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating incoming order: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing incoming order
     */
    public function update(IncomingOrderRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $order = Sale::incomingOrders()->findOrFail($id);

                // Check if order can be updated
                if ($order->status === 'invoiced') {
                    throw new \Exception('Cannot update an invoiced order');
                }

                $data = $request->validated();
                $userId = Auth::id();

                // Update exchange rate if currency changed
                if (!empty($data['currency_id']) && $data['currency_id'] != $order->currency_id) {
                    $currency = Currency::find($data['currency_id']);
                    if ($currency && $currency->code !== 'USD') {
                        $liveRate = $this->getLiveExchangeRate($currency->code);
                        if ($liveRate) {
                            $data['exchange_rate'] = $liveRate;
                        }
                    }
                }

                // Set update fields
                $data['updated_by'] = $userId;

                // Calculate totals
                $this->calculateTotals($data, $request->validated()['items']);

                // Update the order
                $order->update($data);

                // Update sale items
                $order->items()->delete(); // Soft delete existing items
                $this->createSaleItems($order, $request->validated()['items']);

                return $order->load(['customer', 'currency', 'items', 'employee', 'branch']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error updating incoming order: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific incoming order
     */
    public function show($id)
    {
        try {
            $order = Sale::incomingOrders()
                ->with(['customer', 'currency', 'employee', 'branch', 'company', 'items.item', 'createdBy', 'updatedBy'])
                ->findOrFail($id);

            return $order;
        } catch (Exception $e) {
            throw new \Exception('Error fetching incoming order: ' . $e->getMessage());
        }
    }

    /**
     * Delete an incoming order (soft delete)
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $order = Sale::incomingOrders()->findOrFail($id);

                // Check if order can be deleted
                if ($order->status === 'invoiced') {
                    throw new \Exception('Cannot delete an invoiced order');
                }

                // Soft delete the order and its items
                $order->items()->delete();
                $order->delete();

                return true;
            });
        } catch (Exception $e) {
            throw new \Exception('Error deleting incoming order: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft deleted incoming order
     */
    public function restore($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $order = Sale::incomingOrders()->withTrashed()->findOrFail($id);

                // Restore the order and its items
                $order->restore();
                $order->items()->withTrashed()->restore();

                return $order->load(['customer', 'currency', 'items', 'employee', 'branch']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error restoring incoming order: ' . $e->getMessage());
        }
    }

    /**
     * Calculate totals for the order
     */
    private function calculateTotals(&$data, $items)
    {
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemSubtotal;

            // Calculate item discount
            $itemDiscount = 0;
            if (!empty($item['discount_percentage'])) {
                $itemDiscount = $itemSubtotal * ($item['discount_percentage'] / 100);
            } elseif (!empty($item['discount_amount'])) {
                $itemDiscount = $item['discount_amount'];
            }
            $totalDiscount += $itemDiscount;

            // Calculate item tax
            if (!empty($item['tax_rate'])) {
                $afterDiscount = $itemSubtotal - $itemDiscount;
                $itemTax = $afterDiscount * ($item['tax_rate'] / 100);
                $totalTax += $itemTax;
            }
        }

        // Apply order-level discount
        $orderDiscount = 0;
        if (!empty($data['discount_percentage'])) {
            $orderDiscount = $subtotal * ($data['discount_percentage'] / 100);
        } elseif (!empty($data['allowed_discount'])) {
            $orderDiscount = $data['allowed_discount'];
        }

        $totalAfterDiscount = $subtotal - $totalDiscount - $orderDiscount;

        // Apply order-level tax
        $orderTax = 0;
        if (!empty($data['tax_percentage'])) {
            $orderTax = $totalAfterDiscount * ($data['tax_percentage'] / 100);
        }

        $data['total_without_tax'] = $totalAfterDiscount;
        $data['tax_amount'] = $totalTax + $orderTax;
        $data['total_amount'] = $totalAfterDiscount + $totalTax + $orderTax;
        $data['total_local'] = $data['total_amount'] * $data['exchange_rate'];
        $data['total_foreign'] = $data['total_amount'];
        $data['remaining_balance'] = $data['total_amount'] - ($data['cash_paid'] ?? 0) - ($data['checks_paid'] ?? 0);
    }

    /**
     * Create sale items
     */
    private function createSaleItems($order, $items)
    {
        foreach ($items as $index => $itemData) {
            // Get item details if not provided
            if (!empty($itemData['item_id'])) {
                $item = Item::find($itemData['item_id']);
                if ($item) {
                    $itemData['item_number'] = $itemData['item_number'] ?? $item->item_number;
                    $itemData['item_name'] = $itemData['item_name'] ?? $item->name;
                    $itemData['unit_name'] = $itemData['unit_name'] ?? ($item->unit ? $item->unit->name : null);

                    // Use first sale price if unit price not provided
                    if (empty($itemData['unit_price'])) {
                        $itemData['unit_price'] = $item->first_sale_price ?? 0;
                    }
                }
            }

            // Calculate item totals
            $subtotal = $itemData['quantity'] * $itemData['unit_price'];

            $discount = 0;
            if (!empty($itemData['discount_percentage'])) {
                $discount = $subtotal * ($itemData['discount_percentage'] / 100);
                $itemData['discount_amount'] = $discount;
            } elseif (!empty($itemData['discount_amount'])) {
                $discount = $itemData['discount_amount'];
                $itemData['discount_percentage'] = ($discount / $subtotal) * 100;
            }

            $afterDiscount = $subtotal - $discount;
            $tax = 0;
            if (!empty($itemData['tax_rate'])) {
                $tax = $afterDiscount * ($itemData['tax_rate'] / 100);
            }

            $itemData['serial_number'] = $index + 1;
            $itemData['total'] = $afterDiscount + $tax;
            $itemData['total_local'] = $itemData['total'] * $order->exchange_rate;
            $itemData['total_foreign'] = $itemData['total'];

            $order->items()->create($itemData);
        }
    }

    /**
     * Get live exchange rate from external API
     */
    private function getLiveExchangeRate($currencyCode)
    {
        try {
            // Using a free exchange rate API (you can replace with your preferred service)
            $response = Http::timeout(10)->get("https://api.exchangerate-api.com/v4/latest/USD");

            if ($response->successful()) {
                $rates = $response->json()['rates'] ?? [];
                return $rates[$currencyCode] ?? null;
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::warning('Failed to fetch live exchange rate: ' . $e->getMessage());
        }

        return null;
    }
}
