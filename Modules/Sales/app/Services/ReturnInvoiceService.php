<?php

namespace Modules\Sales\app\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\ReturnInvoiceRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Customers\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Company;
use Modules\HumanResources\Models\Employee;

use Carbon\Carbon;

class ReturnInvoiceService
{
    public function index(Request $request)
    {
        try {
            $customerSearch = $request->get('customer_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Sale::query()
                ->with([
                    'customer',
                    'currency',
                    'employee',
                    'branch',
                    'user',
                    'items'
                ])
                ->where('type', SalesTypeEnum::RETURN_INVOICE)
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->whereHas('customer', function ($q) use ($customerSearch) {
                        $q->where('name', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching return invoices: ' . $e->getMessage());
        }
    }

    /**
     * Advanced search for return invoices with multiple criteria
     */
    public function search(Request $request)
    {
        try {
            $query = Sale::query()
                ->with([
                    'customer',
                    'currency',
                    'employee',
                    'branch',
                    'user',
                    'items'
                ])
                ->where('type', SalesTypeEnum::RETURN_INVOICE);

            // Sales Return Invoice Number range search (from/to)
            if ($request->filled('invoice_number_from')) {
                $query->where('invoice_number', '>=', $request->invoice_number_from);
            }
            if ($request->filled('invoice_number_to')) {
                $query->where('invoice_number', '<=', $request->invoice_number_to);
            }

            // Customer Name search
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->customer_name . '%');
                });
            }

            // Date search (specific date)
            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            }

            // Date range search (from/to)
            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            // Licensed Operator search
            if ($request->filled('licensed_operator')) {
                $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
            }

            // Amount search (exact or range)
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

            // Entry Number search (ledger_code)
            if ($request->filled('entry_number')) {
                $query->where('ledger_code', 'like', '%' . $request->entry_number . '%');
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Company filter (if user has access to multiple companies)
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort fields to prevent SQL injection
            $allowedSortFields = [
                'id', 'ledger_code', 'ledger_number', 'invoice_number', 'date', 'time', 'due_date',
                'total_amount', 'status', 'licensed_operator', 'customer_name', 'customer_number',
                'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100); // Limit max per page

            return $query->paginate($perPage);

        } catch (\Exception $e) {
            throw new \Exception('Error searching return invoices: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields for return invoices
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'ledger_code' => 'Entry Number',
            'ledger_number' => 'Ledger Number',
            'invoice_number' => 'Return Invoice Number',
            'date' => 'Date',
            'time' => 'Time',
            'due_date' => 'Due Date',
            'total_amount' => 'Amount',
            'status' => 'Status',
            'licensed_operator' => 'Licensed Operator',
            'customer_name' => 'Customer Name',
            'customer_number' => 'Customer Number',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date'
        ];
    }

    /**
     * Get search form data for return invoices
     */
    public function getSearchFormData(Request $request)
    {
        try {
            return [
                'customers' => Customer::select('id', 'customer_number', 'first_name', 'email')
                    ->orderBy('first_name')
                    ->get(),

                'currencies' => Currency::select('id', 'name', 'code', 'symbol')
                    ->orderBy('name')
                    ->get(),

                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'approved', 'label' => 'Approved'],
                    ['value' => 'sent', 'label' => 'Sent'],
                    ['value' => 'processed', 'label' => 'Processed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                    ['value' => 'completed', 'label' => 'Completed']
                ],

                'sortable_fields' => $this->getSortableFields(),

                'date_ranges' => [
                    ['value' => 'today', 'label' => 'Today'],
                    ['value' => 'yesterday', 'label' => 'Yesterday'],
                    ['value' => 'this_week', 'label' => 'This Week'],
                    ['value' => 'last_week', 'label' => 'Last Week'],
                    ['value' => 'this_month', 'label' => 'This Month'],
                    ['value' => 'last_month', 'label' => 'Last Month'],
                    ['value' => 'this_year', 'label' => 'This Year'],
                    ['value' => 'custom', 'label' => 'Custom Range']
                ],

                'licensed_operators' => Sale::where('type', SalesTypeEnum::RETURN_INVOICE)
                    ->whereNotNull('licensed_operator')
                    ->distinct()
                    ->pluck('licensed_operator')
                    ->filter()
                    ->values()
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error fetching search form data: ' . $e->getMessage());
        }
    }

    /**
     * Create a new sales return invoice with complete functionality
     */
    public function store(ReturnInvoiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = Auth::user()->company_id ?? Company::first()?->id ?? 1;
                $userId = Auth::id();
                $validatedData = $request->validated();

                // Generate ledger and invoice number for return invoices
                $numberingData = $this->generateLedgerAndInvoiceNumber($companyId);

                // Get customer data for auto-population
                $customer = Customer::find($validatedData['customer_id']);

                // Get live exchange rate if currency is provided
                $exchangeRate = $validatedData['exchange_rate'] ?? 1;
                if (isset($validatedData['currency_id'])) {
                    $exchangeRate = $this->getLiveExchangeRate($validatedData['currency_id']);
                }

                // Get required foreign key IDs with fallbacks
                $branchId = $validatedData['branch_id'] ?? Branch::first()?->id ?? 1;
                $currencyId = $validatedData['currency_id'] ?? Currency::first()?->id ?? 1;
                $employeeId = $validatedData['employee_id'] ?? Employee::first()?->id ?? 1;

                // Prepare return invoice data
                $returnInvoiceData = [
                    'type' => SalesTypeEnum::RETURN_INVOICE,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'status' => 'draft',

                    // Auto-generated fields
                    'ledger_code' => $numberingData['ledger_code'],
                    'ledger_number' => $numberingData['ledger_number'],
                    'ledger_invoice_count' => $numberingData['ledger_invoice_count'],
                    'invoice_number' => $numberingData['invoice_number'],
                    'journal_number' => $validatedData['journal_number'] ?? 1,
                    'date' => Carbon::now()->toDateString(),
                    'time' => Carbon::now()->toTimeString(),

                    // Customer information
                    'customer_id' => $validatedData['customer_id'],
                    'customer_number' => $customer ? $customer->customer_number : null,
                    'customer_name' => $customer ? $customer->name : null,
                    'customer_email' => $validatedData['customer_email'] ?? ($customer ? $customer->email : null),

                    // Currency and exchange rate
                    'exchange_rate' => $exchangeRate,

                    // Required foreign key fields with fallbacks
                    'branch_id' => $branchId,
                    'currency_id' => $currencyId,
                    'employee_id' => $employeeId,

                    // Other fields from request
                    'due_date' => $validatedData['due_date'] ?? Carbon::now()->addDays(30)->toDateString(),
                    'licensed_operator' => $validatedData['licensed_operator'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,

                    // Tax settings
                    'is_tax_inclusive' => $validatedData['is_tax_inclusive'] ?? false,
                    'tax_percentage' => $validatedData['tax_percentage'] ?? 0,

                    // Required financial fields with defaults
                    'total_foreign' => 0.0000,
                    'total_local' => 0.0000,
                    'total_amount' => 0.0000,
                    'cash_paid' => 0.00,
                    'checks_paid' => 0.00,
                    'allowed_discount' => 0.00,
                    'total_without_tax' => 0.00,
                    'tax_amount' => 0.00,
                    'remaining_balance' => 0.00,
                    'created_by' => $userId,
                ];

                // Create the return invoice
                $returnInvoice = Sale::create($returnInvoiceData);

                // Create return invoice items if provided
                if (isset($validatedData['items']) && is_array($validatedData['items'])) {
                    $this->createReturnInvoiceItems($returnInvoice, $validatedData['items']);

                    // Calculate totals
                    $this->calculateReturnInvoiceTotals($returnInvoice);

                    // Update inventory (increase stock for returned items)
                    $this->updateInventoryForReturn($returnInvoice);
                }

                // Reload with all relationships for complete response
                return $returnInvoice->load([
                    'customer',
                    'items.item',
                    'items.unit',
                    'user',
                    'employee',
                    'currency',
                    'branch'
                ]);
            });

        } catch (Exception $e) {
            Log::error('Error creating return invoice: ' . $e->getMessage());
            throw new \Exception('Error creating return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Generate ledger code and invoice number for return invoices
     * Ledger system: 50 invoices per ledger, then move to next ledger
     */
    private function generateLedgerAndInvoiceNumber($companyId)
    {
        // Get the latest return invoice for this company
        $latestInvoice = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::RETURN_INVOICE)
            ->orderBy('invoice_number', 'desc')
            ->first();

        $ledgerNumber = 1;
        $ledgerInvoiceCount = 1;
        $invoiceNumber = 1;

        if ($latestInvoice) {
            $currentLedgerNumber = $latestInvoice->ledger_number ?? 1;
            $currentLedgerCount = $latestInvoice->ledger_invoice_count ?? 0;
            $currentInvoiceNumber = (int) $latestInvoice->invoice_number;

            // Check if current ledger is full (50 invoices)
            if ($currentLedgerCount >= 50) {
                // Move to next ledger
                $ledgerNumber = $currentLedgerNumber + 1;
                $ledgerInvoiceCount = 1;
            } else {
                // Continue with current ledger
                $ledgerNumber = $currentLedgerNumber;
                $ledgerInvoiceCount = $currentLedgerCount + 1;
            }

            // Invoice number continues sequentially regardless of ledger
            $invoiceNumber = $currentInvoiceNumber + 1;
        }

        return [
            'ledger_code' => 'LDG-' . str_pad($ledgerNumber, 3, '0', STR_PAD_LEFT),
            'ledger_number' => $ledgerNumber,
            'ledger_invoice_count' => $ledgerInvoiceCount,
            'invoice_number' => str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT)
        ];
    }

    /**
     * Create return invoice items
     */
    private function createReturnInvoiceItems($returnInvoice, $items)
    {
        foreach ($items as $index => $itemData) {
            // Get item details
            $item = Item::find($itemData['item_id']);
            if (!$item) {
                continue;
            }

            // Get unit details
            $unit = Unit::find($itemData['unit_id'] ?? $item->unit_id);

            // Calculate totals
            $quantity = $itemData['quantity'] ?? 1;
            $unitPrice = $itemData['unit_price'] ?? $item->first_sale_price ?? 0;
            $total = $quantity * $unitPrice;

            // Calculate tax if applicable
            $taxAmount = 0;
            if (isset($itemData['tax_rate_id']) && $itemData['tax_rate_id']) {
                $taxRate = TaxRate::find($itemData['tax_rate_id']);
                if ($taxRate) {
                    $taxAmount = ($total * $taxRate->rate) / 100;
                }
            }

            SaleItem::create([
                'sale_id' => $returnInvoice->id,
                'serial_number' => $index + 1,
                'item_id' => $item->id,
                'item_number' => $item->item_number,
                'item_name' => $item->name,
                'unit_id' => $unit ? $unit->id : null,
                'unit_name' => $unit ? $unit->name : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
                'tax_rate_id' => $itemData['tax_rate_id'] ?? null,
                'tax_amount' => $taxAmount,
                'notes' => $itemData['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Calculate return invoice totals
     */
    private function calculateReturnInvoiceTotals($returnInvoice)
    {
        $items = $returnInvoice->items;

        $totalWithoutTax = $items->sum('total');
        $totalTaxAmount = $items->sum('tax_amount');
        $totalAmount = $totalWithoutTax + $totalTaxAmount;

        // Calculate foreign and local amounts based on exchange rate
        $exchangeRate = $returnInvoice->exchange_rate ?? 1;
        $totalForeign = $totalAmount;
        $totalLocal = $totalAmount * $exchangeRate;

        $returnInvoice->update([
            'total_without_tax' => $totalWithoutTax,
            'tax_amount' => $totalTaxAmount,
            'total_amount' => $totalAmount,
            'total_foreign' => $totalForeign,
            'total_local' => $totalLocal,
        ]);
    }

    /**
     * Update inventory for return (increase stock)
     */
    private function updateInventoryForReturn($returnInvoice)
    {
        foreach ($returnInvoice->items as $item) {
            $inventoryItem = Item::find($item->item_id);
            if ($inventoryItem && $inventoryItem->stock_tracking) {
                // Increase stock for returned items
                $inventoryItem->increment('quantity', $item->quantity);
                $inventoryItem->increment('balance', $item->quantity);

                Log::info("Inventory updated for return - Item: {$inventoryItem->name}, Quantity increased: {$item->quantity}");
            }
        }
    }

    /**
     * Get live exchange rate for currency
     */
    private function getLiveExchangeRate($currencyId)
    {
        try {
            $currency = Currency::find($currencyId);
            if (!$currency) {
                return 1;
            }

            // If it's the base currency, return 1
            if (in_array($currency->code, ['USD', 'ILS', 'JOD'])) {
                return 1;
            }

            // Get live exchange rate from external API
            $response = Http::timeout(10)->get("https://api.exchangerate-api.com/v4/latest/USD");

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
     * Show a specific return invoice
     */
    public function show($id)
    {
        try {
            return Sale::with([
                'customer',
                'items.item',
                'items.unit',
                'items.taxRate',
                'user',
                'employee',
                'currency',
                'branch'
            ])
            ->where('type', SalesTypeEnum::RETURN_INVOICE)
            ->findOrFail($id);

        } catch (\Exception $e) {
            throw new \Exception('Error fetching return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing return invoice
     */
    public function update(ReturnInvoiceRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $returnInvoice = Sale::with(['items'])
                    ->where('type', SalesTypeEnum::RETURN_INVOICE)
                    ->findOrFail($id);

                // Check if return invoice can be updated
                if ($returnInvoice->status === 'completed') {
                    throw new \Exception('Cannot update completed return invoice');
                }

                $validatedData = $request->validated();
                $userId = Auth::id();
                $companyId = Auth::user()->company_id ?? $returnInvoice->company_id;

                // Get customer data for auto-population if customer changed
                $customer = null;
                if (isset($validatedData['customer_id']) && $validatedData['customer_id'] != $returnInvoice->customer_id) {
                    $customer = Customer::find($validatedData['customer_id']);
                }

                // Get live exchange rate if currency changed
                $exchangeRate = $returnInvoice->exchange_rate;
                if (isset($validatedData['currency_id']) && $validatedData['currency_id'] != $returnInvoice->currency_id) {
                    $exchangeRate = $this->getLiveExchangeRate($validatedData['currency_id']);
                }

                // Prepare update data
                $updateData = [
                    'updated_by' => $userId,
                    'company_id' => $companyId,

                    // Customer information
                    'customer_id' => $validatedData['customer_id'] ?? $returnInvoice->customer_id,
                    'customer_number' => $customer ? $customer->customer_number : $returnInvoice->customer_number,
                    'customer_name' => $customer ? $customer->name : $returnInvoice->customer_name,
                    'customer_email' => $validatedData['customer_email'] ??
                                      ($customer ? $customer->email : $returnInvoice->customer_email),

                    // Return invoice details
                    'due_date' => $validatedData['due_date'] ?? $returnInvoice->due_date,
                    'licensed_operator' => $validatedData['licensed_operator'] ?? $returnInvoice->licensed_operator,
                    'notes' => $validatedData['notes'] ?? $returnInvoice->notes,

                    // Employee and branch information
                    'employee_id' => $validatedData['employee_id'] ?? $returnInvoice->employee_id,
                    'branch_id' => $validatedData['branch_id'] ?? $returnInvoice->branch_id,

                    // Currency and exchange rate
                    'currency_id' => $validatedData['currency_id'] ?? $returnInvoice->currency_id,
                    'exchange_rate' => $exchangeRate,

                    // Tax settings
                    'is_tax_inclusive' => $validatedData['is_tax_inclusive'] ?? $returnInvoice->is_tax_inclusive,
                    'tax_percentage' => $validatedData['tax_percentage'] ?? $returnInvoice->tax_percentage,
                ];

                // Update the return invoice
                $returnInvoice->update($updateData);

                // Update return invoice items if provided
                if (isset($validatedData['items']) && is_array($validatedData['items'])) {
                    // Revert inventory changes from old items
                    $this->revertInventoryForReturn($returnInvoice);

                    // Delete existing items (soft delete)
                    $returnInvoice->items()->delete();

                    // Create new items
                    $this->createReturnInvoiceItems($returnInvoice, $validatedData['items']);

                    // Recalculate totals
                    $this->calculateReturnInvoiceTotals($returnInvoice);

                    // Update inventory for new items
                    $this->updateInventoryForReturn($returnInvoice);
                }

                // Reload with all relationships for complete response
                return $returnInvoice->load([
                    'customer',
                    'items.item',
                    'items.unit',
                    'user',
                    'employee',
                    'currency',
                    'branch'
                ]);
            });

        } catch (Exception $e) {
            Log::error('Error updating return invoice: ' . $e->getMessage());
            throw new \Exception('Error updating return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete a return invoice (soft delete)
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $returnInvoice = Sale::with(['items'])
                    ->where('type', SalesTypeEnum::RETURN_INVOICE)
                    ->findOrFail($id);

                // Check if return invoice can be deleted
                if ($returnInvoice->status === 'completed') {
                    throw new \Exception('Cannot delete completed return invoice');
                }

                // Revert inventory changes
                $this->revertInventoryForReturn($returnInvoice);

                // Set deleted_by before soft delete
                $returnInvoice->update([
                    'deleted_by' => Auth::id(),
                    'status' => 'cancelled'
                ]);

                // Soft delete the return invoice items first
                foreach ($returnInvoice->items as $item) {
                    $item->update(['deleted_by' => Auth::id()]);
                    $item->delete();
                }

                // Soft delete the return invoice
                $returnInvoice->delete();

                return [
                    'success' => true,
                    'message' => 'Return invoice deleted successfully',
                    'return_invoice_id' => $id,
                    'invoice_number' => $returnInvoice->invoice_number
                ];
            });

        } catch (Exception $e) {
            Log::error('Error deleting return invoice: ' . $e->getMessage());
            throw new \Exception('Error deleting return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Revert inventory changes for return (decrease stock)
     */
    private function revertInventoryForReturn($returnInvoice)
    {
        foreach ($returnInvoice->items as $item) {
            $inventoryItem = Item::find($item->item_id);
            if ($inventoryItem && $inventoryItem->stock_tracking) {
                // Decrease stock (revert the return)
                $inventoryItem->decrement('quantity', $item->quantity);
                $inventoryItem->decrement('balance', $item->quantity);

                Log::info("Inventory reverted for return - Item: {$inventoryItem->name}, Quantity decreased: {$item->quantity}");
            }
        }
    }

    /**
     * Search customers for dropdown
     */
    public function searchCustomers(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $query = Customer::select('id', 'customer_number', 'first_name', 'email', 'phone');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                      ->orWhere('customer_number', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            return $query->orderBy('first_name')->limit(50)->get();

        } catch (\Exception $e) {
            throw new \Exception('Error searching customers: ' . $e->getMessage());
        }
    }

    /**
     * Get customer by number
     */
    public function getCustomerByNumber(Request $request)
    {
        try {
            $customerNumber = $request->get('customer_number');

            if (!$customerNumber) {
                throw new \Exception('Customer number is required');
            }

            $customer = Customer::where('customer_number', $customerNumber)
                ->select('id', 'customer_number', 'first_name', 'email', 'phone')
                ->first();

            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            return $customer;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching customer: ' . $e->getMessage());
        }
    }

    /**
     * Get customer by name
     */
    public function getCustomerByName(Request $request)
    {
        try {
            $customerName = $request->get('customer_name');

            if (!$customerName) {
                throw new \Exception('Customer name is required');
            }

            $customer = Customer::where('first_name', $customerName)
                ->select('id', 'customer_number', 'first_name', 'email', 'phone')
                ->first();

            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            return $customer;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching customer: ' . $e->getMessage());
        }
    }

    /**
     * Search items for dropdown
     */
    public function searchItems(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $query = Item::where('active', true)
                ->select('id', 'item_number', 'name', 'first_sale_price', 'unit_id');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('item_number', 'like', '%' . $search . '%');
                });
            }

            return $query->with('unit:id,name,symbol')
                ->orderBy('name')
                ->limit(50)
                ->get();

        } catch (\Exception $e) {
            throw new \Exception('Error searching items: ' . $e->getMessage());
        }
    }

    /**
     * Get item by number
     */
    public function getItemByNumber(Request $request)
    {
        try {
            $itemNumber = $request->get('item_number');

            if (!$itemNumber) {
                throw new \Exception('Item number is required');
            }

            $item = Item::where('item_number', $itemNumber)
                ->where('active', true)
                ->with('unit:id,name,symbol')
                ->select('id', 'item_number', 'name', 'first_sale_price', 'unit_id')
                ->first();

            if (!$item) {
                throw new \Exception('Item not found');
            }

            return $item;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching item: ' . $e->getMessage());
        }
    }

    /**
     * Get item by name
     */
    public function getItemByName(Request $request)
    {
        try {
            $itemName = $request->get('item_name');

            if (!$itemName) {
                throw new \Exception('Item name is required');
            }

            $item = Item::where('name', $itemName)
                ->where('active', true)
                ->with('unit:id,name,symbol')
                ->select('id', 'item_number', 'name', 'first_sale_price', 'unit_id')
                ->first();

            if (!$item) {
                throw new \Exception('Item not found');
            }

            return $item;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching item: ' . $e->getMessage());
        }
    }

    /**
     * Get form data for return invoice creation
     */
    public function getFormData(Request $request)
    {
        try {
            return [
                'customers' => Customer::select('id', 'customer_number', 'first_name', 'email')
                    ->orderBy('first_name')
                    ->get(),

                'currencies' => Currency::select('id', 'name', 'code', 'symbol')
                    ->orderBy('name')
                    ->get(),

                'items' => Item::where('active', true)
                    ->with('unit:id,name,symbol')
                    ->select('id', 'item_number', 'name', 'first_sale_price', 'unit_id')
                    ->orderBy('name')
                    ->get(),

                'units' => Unit::select('id', 'name', 'symbol')
                    ->orderBy('name')
                    ->get(),

                'tax_rates' => TaxRate::select('id', 'name', 'code', 'rate', 'type')
                    ->orderBy('name')
                    ->get(),

                'licensed_operators' => Sale::whereNotNull('licensed_operator')
                    ->distinct()
                    ->pluck('licensed_operator')
                    ->filter()
                    ->values()
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error fetching form data: ' . $e->getMessage());
        }
    }

    /**
     * Get deleted return invoices (soft deleted)
     */
    public function getDeleted(Request $request)
    {
        try {
            $query = Sale::onlyTrashed()
                ->with([
                    'customer',
                    'currency',
                    'employee',
                    'branch',
                    'user',
                    'items'
                ])
                ->where('type', SalesTypeEnum::RETURN_INVOICE);

            // Apply filters if provided
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSortFields = [
                'id', 'ledger_code', 'invoice_number', 'date', 'total_amount',
                'deleted_at', 'created_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('deleted_at', 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            return $query->paginate($perPage);

        } catch (\Exception $e) {
            throw new \Exception('Error fetching deleted return invoices: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft deleted return invoice
     */
    public function restore($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $returnInvoice = Sale::onlyTrashed()
                    ->where('type', SalesTypeEnum::RETURN_INVOICE)
                    ->findOrFail($id);

                // Restore the return invoice items first
                SaleItem::onlyTrashed()
                    ->where('sale_id', $id)
                    ->restore();

                // Restore the return invoice
                $returnInvoice->restore();

                // Update status back to draft
                $returnInvoice->update([
                    'status' => 'draft',
                    'deleted_by' => null
                ]);

                // Re-apply inventory changes for restored return
                $this->updateInventoryForReturn($returnInvoice);

                return [
                    'success' => true,
                    'message' => 'Return invoice restored successfully',
                    'return_invoice_id' => $id,
                    'invoice_number' => $returnInvoice->invoice_number
                ];
            });

        } catch (\Exception $e) {
            throw new \Exception('Error restoring return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a return invoice (permanent deletion)
     */
    public function forceDelete($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $returnInvoice = Sale::onlyTrashed()
                    ->where('type', SalesTypeEnum::RETURN_INVOICE)
                    ->findOrFail($id);

                // Force delete all return invoice items
                SaleItem::onlyTrashed()
                    ->where('sale_id', $id)
                    ->forceDelete();

                // Force delete the return invoice
                $returnInvoice->forceDelete();

                return [
                    'success' => true,
                    'message' => 'Return invoice permanently deleted',
                    'return_invoice_id' => $id
                ];
            });

        } catch (\Exception $e) {
            throw new \Exception('Error permanently deleting return invoice: ' . $e->getMessage());
        }
    }
}
