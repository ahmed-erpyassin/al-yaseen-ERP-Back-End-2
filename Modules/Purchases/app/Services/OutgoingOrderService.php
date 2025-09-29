<?php

namespace Modules\Purchases\app\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\Http\Requests\OutgoingOrderRequest;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Carbon\Carbon;

class OutgoingOrderService
{
    public function index(Request $request)
    {
        try {
            $query = Purchase::query()
                ->where('type', PurchaseTypeEnum::OUTGOING_ORDER)
                ->with(['customer', 'currency', 'items.item', 'creator', 'updater']);

            // Advanced search filters
            $this->applySearchFilters($query, $request);

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort fields
            $allowedSortFields = $this->getAllowedSortFields();
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100); // Between 1 and 100

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing orders: ' . $e->getMessage());
        }
    }

    /**
     * Apply search filters to the query
     */
    private function applySearchFilters($query, Request $request)
    {
        // Outgoing Order Number search (from/to)
        if ($request->filled('order_number_from')) {
            $query->where('outgoing_order_number', '>=', $request->order_number_from);
        }
        if ($request->filled('order_number_to')) {
            $query->where('outgoing_order_number', '<=', $request->order_number_to);
        }
        if ($request->filled('order_number')) {
            $query->where('outgoing_order_number', 'like', '%' . $request->order_number . '%');
        }

        // Customer Name search
        if ($request->filled('customer_name')) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->customer_name . '%')
                  ->orWhere('customer_number', 'like', '%' . $request->customer_name . '%')
                  ->orWhereHas('customer', function ($customerQuery) use ($request) {
                      $customerQuery->where('first_name', 'like', '%' . $request->customer_name . '%')
                                   ->orWhere('second_name', 'like', '%' . $request->customer_name . '%')
                                   ->orWhere('company_name', 'like', '%' . $request->customer_name . '%');
                  });
            });
        }

        // Date search (specific date or range)
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Amount search (specific amount or range)
        if ($request->filled('amount')) {
            $query->where('total_amount', $request->amount);
        }
        if ($request->filled('amount_from')) {
            $query->where('total_amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('total_amount', '<=', $request->amount_to);
        }

        // Currency search
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        // Licensed Operator search
        if ($request->filled('licensed_operator')) {
            $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
        }

        // Status search
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // General search (searches across multiple fields)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('outgoing_order_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('customer_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('customer_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('licensed_operator', 'like', '%' . $searchTerm . '%')
                  ->orWhere('notes', 'like', '%' . $searchTerm . '%');
            });
        }
    }

    /**
     * Get allowed sort fields
     */
    private function getAllowedSortFields(): array
    {
        return [
            'id',
            'outgoing_order_number',
            'customer_name',
            'customer_number',
            'date',
            'time',
            'due_date',
            'licensed_operator',
            'total_amount',
            'total_without_tax',
            'tax_amount',
            'exchange_rate',
            'status',
            'journal_code',
            'journal_number',
            'created_at',
            'updated_at'
        ];
    }

    public function store(OutgoingOrderRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;

                // Generate automatic fields
                $journalData = Purchase::generateJournalAndInvoiceNumber($companyId);
                $outgoingOrderNumber = Purchase::generateOutgoingOrderNumber();

                // Get customer information if customer_id is provided
                $customerData = [];
                if ($request->customer_id) {
                    $customer = Customer::find($request->customer_id);
                    if ($customer) {
                        $customerData = [
                            'customer_number' => $customer->customer_number,
                            'customer_name' => $customer->first_name . ' ' . $customer->second_name,
                            'customer_email' => $customer->email,
                            'customer_mobile' => $customer->mobile,
                        ];
                    }
                }

                // Get live exchange rate if currency is provided
                $exchangeRate = 1;
                if ($request->currency_id) {
                    $exchangeRate = $this->getLiveExchangeRate($request->currency_id);
                }

                // Prepare order data
                $orderData = [
                    'type' => PurchaseTypeEnum::OUTGOING_ORDER,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'status' => 'draft',
                    'outgoing_order_number' => $outgoingOrderNumber,
                    'journal_code' => $journalData['journal_code'],
                    'journal_number' => $journalData['invoice_number'],
                    'journal_invoice_count' => 1,
                    'date' => Carbon::now()->toDateString(),
                    'time' => Carbon::now()->toTimeString(),
                    'exchange_rate' => $exchangeRate,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ] + $customerData + $request->validated();

                // Create the order
                $order = Purchase::create($orderData);

                // Create order items
                if ($request->has('items') && is_array($request->items)) {
                    $this->createOrderItems($order, $request->items);
                }

                // Calculate totals
                $this->calculateOrderTotals($order);

                return $order->load(['customer', 'currency', 'items.item']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating outgoing order: ' . $e->getMessage());
        }
    }

    /**
     * Create order items
     */
    private function createOrderItems(Purchase $order, array $items)
    {
        foreach ($items as $index => $itemData) {
            // Get item information
            $item = Item::find($itemData['item_id']);
            if (!$item) {
                continue;
            }

            // Get unit information
            $unit = Unit::find($item->unit_id);

            $orderItem = PurchaseItem::create([
                'purchase_id' => $order->id,
                'serial_number' => $index + 1,
                'item_id' => $itemData['item_id'],
                'item_number' => $item->item_number,
                'item_name' => $item->name,
                'unit' => $unit ? $unit->name : 'Unit',
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'] ?? $item->first_selling_price,
                'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                'discount_amount' => $itemData['discount_amount'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'description' => $itemData['description'] ?? null,
            ]);
        }
    }

    /**
     * Calculate order totals
     */
    private function calculateOrderTotals(Purchase $order)
    {
        $items = $order->items;

        $subtotal = $items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $totalDiscount = $items->sum('discount_amount');
        $totalWithoutTax = $subtotal - $totalDiscount;
        $totalTax = $items->sum(function ($item) {
            return $item->total_without_tax * $item->tax_rate / 100;
        });

        $order->update([
            'total_without_tax' => $totalWithoutTax,
            'tax_amount' => $totalTax,
            'total_amount' => $totalWithoutTax + $totalTax,
            'allowed_discount' => $totalDiscount,
        ]);
    }

    /**
     * Get live exchange rate from external API
     */
    private function getLiveExchangeRate($currencyId)
    {
        try {
            $currency = Currency::find($currencyId);
            if (!$currency) {
                return 1;
            }

            // Get live rate from external API
            $response = Http::timeout(10)
                ->get("https://api.exchangerate-api.com/v4/latest/USD");

            if ($response->successful()) {
                $rates = $response->json()['rates'] ?? [];
                return $rates[$currency->code] ?? 1;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch live exchange rate: ' . $e->getMessage());
        }

        return 1;
    }

    /**
     * Get customers for dropdown
     */
    public function getCustomers(Request $request)
    {
        $search = $request->get('search', '');

        return Customer::where('status', 'active')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('customer_number', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('second_name', 'like', '%' . $search . '%')
                      ->orWhere('company_name', 'like', '%' . $search . '%');
                });
            })
            ->select('id', 'customer_number', 'first_name', 'second_name', 'company_name', 'email')
            ->limit(50)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'customer_number' => $customer->customer_number,
                    'customer_name' => $customer->first_name . ' ' . $customer->second_name,
                    'company_name' => $customer->company_name,
                    'email' => $customer->email,
                ];
            });
    }

    /**
     * Get items for dropdown
     */
    public function getItems(Request $request)
    {
        $search = $request->get('search', '');

        return Item::where('status', 'active')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('item_number', 'like', '%' . $search . '%')
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhere('name_ar', 'like', '%' . $search . '%');
                });
            })
            ->with('unit')
            ->select('id', 'item_number', 'name', 'name_ar', 'first_selling_price', 'unit_id')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->name,
                    'item_name_ar' => $item->name_ar,
                    'unit' => $item->unit ? $item->unit->name : 'Unit',
                    'unit_price' => $item->first_selling_price,
                ];
            });
    }

    /**
     * Get currencies for dropdown
     */
    public function getCurrencies()
    {
        return Currency::select('id', 'code', 'name', 'symbol')
            ->get();
    }

    /**
     * Get tax rates for dropdown
     */
    public function getTaxRates()
    {
        return TaxRate::where('type', 'vat')
            ->select('id', 'name', 'code', 'rate')
            ->get();
    }

    /**
     * Show specific outgoing order
     */
    public function show($id)
    {
        try {
            $order = Purchase::where('type', PurchaseTypeEnum::OUTGOING_ORDER)
                ->with([
                    'customer',
                    'currency',
                    'items.item.unit',
                    'creator',
                    'updater',
                    'journal'
                ])
                ->findOrFail($id);

            return $order;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing order: ' . $e->getMessage());
        }
    }

    /**
     * Update outgoing order
     */
    public function update(OutgoingOrderRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $order = Purchase::where('type', PurchaseTypeEnum::OUTGOING_ORDER)
                    ->findOrFail($id);

                // Check if order can be updated
                if ($order->status === 'invoiced') {
                    throw new \Exception('Cannot update an invoiced order');
                }

                $userId = $request->user()->id;

                // Get customer information if customer_id is provided
                $customerData = [];
                if ($request->customer_id) {
                    $customer = Customer::find($request->customer_id);
                    if ($customer) {
                        $customerData = [
                            'customer_number' => $customer->customer_number,
                            'customer_name' => $customer->first_name . ' ' . $customer->second_name,
                            'customer_email' => $customer->email,
                            'customer_mobile' => $customer->mobile,
                        ];
                    }
                }

                // Get live exchange rate if currency is provided
                $exchangeRate = $order->exchange_rate; // Keep existing rate
                if ($request->currency_id && $request->currency_id != $order->currency_id) {
                    $exchangeRate = $this->getLiveExchangeRate($request->currency_id);
                }

                // Prepare update data
                $updateData = array_merge($request->validated(), $customerData, [
                    'exchange_rate' => $exchangeRate,
                    'updated_by' => $userId,
                ]);

                // Update the order
                $order->update($updateData);

                // Update order items if provided
                if ($request->has('items') && is_array($request->items)) {
                    // Delete existing items
                    $order->items()->delete();

                    // Create new items
                    $this->createOrderItems($order, $request->items);
                }

                // Recalculate totals
                $this->calculateOrderTotals($order);

                return $order->load(['customer', 'currency', 'items.item', 'creator', 'updater']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating outgoing order: ' . $e->getMessage());
        }
    }

    /**
     * Delete outgoing order (soft delete)
     */
    public function destroy($id, $userId)
    {
        try {
            return DB::transaction(function () use ($id, $userId) {
                $order = Purchase::where('type', PurchaseTypeEnum::OUTGOING_ORDER)
                    ->findOrFail($id);

                // Check if order can be deleted
                if ($order->status === 'invoiced') {
                    throw new \Exception('Cannot delete an invoiced order');
                }

                // Set deleted_by before soft delete
                $order->update(['updated_by' => $userId]);

                // Soft delete the order (items will be cascade deleted)
                $order->delete();

                return true;
            });
        } catch (\Exception $e) {
            throw new \Exception('Error deleting outgoing order: ' . $e->getMessage());
        }
    }

    /**
     * Restore deleted outgoing order
     */
    public function restore($id)
    {
        try {
            $order = Purchase::where('type', PurchaseTypeEnum::OUTGOING_ORDER)
                ->onlyTrashed()
                ->findOrFail($id);

            $order->restore();

            return $order->load(['customer', 'currency', 'items.item']);
        } catch (\Exception $e) {
            throw new \Exception('Error restoring outgoing order: ' . $e->getMessage());
        }
    }

    /**
     * Get deleted outgoing orders
     */
    public function getDeleted(Request $request)
    {
        try {
            $query = Purchase::where('type', PurchaseTypeEnum::OUTGOING_ORDER)
                ->onlyTrashed()
                ->with(['customer', 'currency', 'creator']);

            // Apply search filters to deleted orders
            $this->applySearchFilters($query, $request);

            // Sorting
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSortFields = array_merge($this->getAllowedSortFields(), ['deleted_at']);
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'deleted_at';
            }

            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching deleted outgoing orders: ' . $e->getMessage());
        }
    }

    /**
     * Get search form data
     */
    public function getSearchFormData()
    {
        try {
            return [
                'customers' => Customer::where('status', 'active')
                    ->select('id', 'customer_number', 'first_name', 'second_name', 'company_name')
                    ->limit(100)
                    ->get()
                    ->map(function ($customer) {
                        return [
                            'id' => $customer->id,
                            'customer_number' => $customer->customer_number,
                            'customer_name' => $customer->first_name . ' ' . $customer->second_name,
                            'company_name' => $customer->company_name,
                        ];
                    }),
                'currencies' => $this->getCurrencies(),
                'status_options' => Purchase::STATUS_OPTIONS,
                'sort_fields' => $this->getSortableFields(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error fetching search form data: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields for frontend
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'outgoing_order_number' => 'Order Number',
            'customer_name' => 'Customer Name',
            'customer_number' => 'Customer Number',
            'date' => 'Date',
            'time' => 'Time',
            'due_date' => 'Due Date',
            'licensed_operator' => 'Licensed Operator',
            'total_amount' => 'Total Amount',
            'total_without_tax' => 'Total Without Tax',
            'tax_amount' => 'Tax Amount',
            'exchange_rate' => 'Exchange Rate',
            'status' => 'Status',
            'journal_code' => 'Journal Code',
            'journal_number' => 'Journal Number',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At'
        ];
    }
}
