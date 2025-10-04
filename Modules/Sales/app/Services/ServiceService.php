<?php

namespace Modules\Sales\app\Services;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\ServiceRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Account;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Item;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\HumanResources\Models\Employee;
use Carbon\Carbon;

class ServiceService
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
                ->where('type', SalesTypeEnum::SERVICE)
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->whereHas('customer', function ($q) use ($customerSearch) {
                        $q->where('name', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching services: ' . $e->getMessage());
        }
    }

    /**
     * Advanced search for services with multiple criteria
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
                    'items.account',
                    'items.unit'
                ])
                ->where('type', SalesTypeEnum::SERVICE);

            // Service Number range search (from/to)
            if ($request->filled('service_number_from')) {
                $query->where('invoice_number', '>=', $request->service_number_from);
            }
            if ($request->filled('service_number_to')) {
                $query->where('invoice_number', '<=', $request->service_number_to);
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

            // Entry Number search (book_code)
            if ($request->filled('entry_number')) {
                $query->where('book_code', 'like', '%' . $request->entry_number . '%');
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
                'id', 'book_code', 'invoice_number', 'date', 'time', 'due_date',
                'total_amount', 'status', 'licensed_operator', 'created_at', 'updated_at'
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
            throw new \Exception('Error searching services: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields for services
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'book_code' => 'Entry Number',
            'invoice_number' => 'Service Number',
            'date' => 'Date',
            'time' => 'Time',
            'due_date' => 'Due Date',
            'total_amount' => 'Amount',
            'status' => 'Status',
            'licensed_operator' => 'Licensed Operator',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date'
        ];
    }

    /**
     * Get search form data for services
     */
    public function getSearchFormData(Request $request)
    {
        try {
            return [
                'customers' => Customer::select('id', 'first_name', 'email')
                    ->orderBy('first_name')
                    ->get(),

                'currencies' => Currency::select('id', 'name', 'code', 'symbol')
                   // ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'approved', 'label' => 'Approved'],
                    ['value' => 'sent', 'label' => 'Sent'],
                    ['value' => 'invoiced', 'label' => 'Invoiced'],
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
                ]
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error fetching search form data: ' . $e->getMessage());
        }
    }

    /**
     * Create a new service with complete functionality
     */
    public function store(ServiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $userId = Auth::id();
                $validatedData = $request->validated();

                // Get or create required entities
                $companyId = Auth::user()->company_id ?? Company::first()?->id ?? 1;
                $branchId = $validatedData['branch_id'] ?? Branch::first()?->id ?? 1;
                $currencyId = $validatedData['currency_id'] ?? Currency::first()?->id ?? 1;
                $employeeId = $validatedData['employee_id'] ?? Employee::first()?->id ?? 1;

                // Generate book code and invoice number for services
                $numberingData = $this->generateBookAndInvoiceNumber($companyId);

                // Get customer data for auto-population
                $customer = Customer::find($validatedData['customer_id']);

                // Get live exchange rate if currency is provided
                $exchangeRate = 1;
                if ($currencyId) {
                    $exchangeRate = $this->getLiveExchangeRate($currencyId);
                }

                // Prepare service data
                $serviceData = [
                    'type' => SalesTypeEnum::SERVICE,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'status' => 'draft',

                    // Auto-generated fields
                    'book_code' => $numberingData['book_code'],
                    'invoice_number' => $numberingData['invoice_number'],
                    'date' => Carbon::now()->toDateString(),
                    'time' => Carbon::now()->toTimeString(),

                    // Required fields from original migration
                    'journal_number' => $validatedData['journal_number'] ?? 1,
                    'branch_id' => $branchId,
                    'currency_id' => $currencyId,
                    'employee_id' => $employeeId,
                    'customer_id' => $validatedData['customer_id'],
                    'due_date' => $validatedData['due_date'] ?? Carbon::now()->addDays(30)->toDateString(),
                    'exchange_rate' => $exchangeRate,
                    'total_foreign' => 0.0000,
                    'total_local' => 0.0000,
                    'total_amount' => 0.0000,

                    // Customer information
                    'customer_email' => $validatedData['customer_email'] ?? ($customer ? $customer->email : null),

                    // Other fields from request
                    'licensed_operator' => $validatedData['licensed_operator'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,

                    // Tax settings
                    'is_tax_inclusive' => $validatedData['is_tax_inclusive'] ?? false,
                    'tax_percentage' => floatval($validatedData['tax_percentage'] ?? 0),

                    // Financial fields (will be calculated)
                    'cash_paid' => 0.00,
                    'checks_paid' => 0.00,
                    'allowed_discount' => 0.00,
                    'total_without_tax' => 0.00,
                    'tax_amount' => 0.00,
                    'remaining_balance' => 0.00,
                    'created_by' => $userId,
                ];

                // Create the service
                $service = Sale::create($serviceData);

                // Create service items if provided
                if (isset($validatedData['items']) && is_array($validatedData['items'])) {
                    $this->createServiceItems($service, $validatedData['items']);

                    // Recalculate totals after adding items
                    $this->calculateServiceTotals($service);
                }

                return $service->load(['customer', 'items.account', 'items.unit', 'user', 'currency']);
            });

        } catch (Exception $e) {
            Log::error('Error creating service: ' . $e->getMessage());
            throw new \Exception('Error creating service: ' . $e->getMessage());
        }
    }

    /**
     * Generate book code and invoice number for services
     */
    private function generateBookAndInvoiceNumber($companyId): array
    {


        // Generate book code
        $bookCode = $this->generateBookCode($companyId);

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber($companyId);

        return [
            'book_code' => $bookCode,
            'invoice_number' => $invoiceNumber
        ];
    }

    /**
     * Generate sequential book code for services (50 services per book)
     */
    private function generateBookCode($companyId): string
    {
        $lastService = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::SERVICE)
            ->whereNotNull('book_code')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastService || !$lastService->book_code) {
            return 'SRV-BOOK-001';
        }

        $lastNumber = (int) substr($lastService->book_code, -3);
        $currentBookServicesCount = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::SERVICE)
            ->where('book_code', $lastService->book_code)
            ->count();

        if ($currentBookServicesCount >= 50) {
            $newNumber = $lastNumber + 1;
            return 'SRV-BOOK-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        }

        return $lastService->book_code;
    }

    /**
     * Generate sequential invoice number for services
     */
    private function generateInvoiceNumber($companyId): string
    {
        $lastService = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::SERVICE)
            ->whereNotNull('invoice_number')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastService || !$lastService->invoice_number) {
            return 'SRV-000001';
        }

        // Extract number from invoice number (e.g., SRV-000001 -> 1)
        $lastNumber = (int) substr($lastService->invoice_number, -6);
        $newNumber = $lastNumber + 1;

        return 'SRV-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
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

            // If it's the base currency (USD or local), return 1
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
     * Create service items with account integration
     */
    private function createServiceItems($service, $items)
    {
        foreach ($items as $index => $itemData) {
            // Get account information
            $account = null;
            if (isset($itemData['account_id'])) {
                $account = Account::find($itemData['account_id']);
            }

            // Get unit information
            $unit = null;
            if (isset($itemData['unit_id'])) {
                $unit = Unit::find($itemData['unit_id']);
            }

            // Get or set default item_id and unit_id for required fields
            $itemId = $itemData['item_id'] ?? Item::first()?->id ?? 1;
            $unitId = $itemData['unit_id'] ?? Unit::first()?->id ?? 1;

            // Calculate totals
            $quantity = $itemData['quantity'] ?? 1;
            $unitPrice = $itemData['unit_price'] ?? 0;
            $total = $quantity * $unitPrice;

            // Apply tax if enabled
            $taxAmount = 0;
            if (isset($itemData['apply_tax']) && $itemData['apply_tax'] && isset($itemData['tax_rate_id'])) {
                $taxRate = TaxRate::find($itemData['tax_rate_id']);
                if ($taxRate) {
                    $taxAmount = ($total * $taxRate->rate) / 100;
                }
            }

            $saleItemData = [
                'sale_id' => $service->id,
                'item_id' => $itemId, // Required field - use existing item or default
                'serial_number' => $index + 1,
                'account_id' => $itemData['account_id'] ?? null,
                'account_number' => $account ? $account->code : ($itemData['account_number'] ?? null),
                'account_name' => $account ? $account->name : ($itemData['account_name'] ?? null),
                'unit_id' => $unitId, // Required field - use existing unit or default
                'unit_name' => $unit ? $unit->name : ($itemData['unit_name'] ?? null),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
                'tax_rate_id' => $itemData['tax_rate_id'] ?? null,
                'tax_amount' => $taxAmount,
                'notes' => $itemData['notes'] ?? null,
            ];

            SaleItem::create($saleItemData);
        }
    }

    /**
     * Calculate service totals
     */
    private function calculateServiceTotals($service)
    {
        $items = $service->items;

        $totalWithoutTax = $items->sum('total');
        $totalTaxAmount = $items->sum('tax_amount');
        $totalAmount = $totalWithoutTax + $totalTaxAmount;

        $service->update([
            'total_without_tax' => floatval($totalWithoutTax),
            'tax_amount' => floatval($totalTaxAmount),
            'total_amount' => floatval($totalAmount),
            'total_foreign' => floatval($totalAmount),
            'total_local' => floatval($totalAmount * $service->exchange_rate),
            'remaining_balance' => floatval($totalAmount), // Initially, full amount is remaining
        ]);
    }

    /**
     * Search for customers
     */
    public function searchCustomers(Request $request)
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 10);

        return Customer::where('first_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orWhere('customer_number', 'like', "%{$search}%")
            ->limit($limit)
            ->get(['id', 'customer_number', 'first_name', 'email', 'phone']);
    }

    /**
     * Search for accounts with advanced filtering
     * Supports search by first letter and bidirectional account number/name lookup
     */
    public function searchAccounts(Request $request)
    {
        $search = $request->get('search', '');
        $searchType = $request->get('search_type', 'name'); // 'name', 'code', or 'both'
        $limit = $request->get('limit', 50);
        $query = Account::query();

        if ($search) {
            if ($searchType === 'name') {
                // Search by account name - filter by first letter
                $query->where('name', 'like', $search . '%');
            } elseif ($searchType === 'code') {
                // Search by account code/number
                $query->where('code', 'like', $search . '%');
            } else {
                // Search both name and code
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search . '%')
                      ->orWhere('code', 'like', $search . '%');
                });
            }
        }

        return $query->select(['id', 'code', 'name', 'type'])
                    ->orderBy('code')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get all account numbers for dropdown (read-only)
     */
    public function getAllAccountNumbers(Request $request)
    {
        return Account::select(['id', 'code', 'name', 'type'])
            ->orderBy('code')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'account_number' => $account->code,
                    'account_name' => $account->name,
                    'type' => $account->type,
                    'display_text' => $account->code . ' - ' . $account->name
                ];
            });
    }

    /**
     * Get account details by account number (code)
     */
    public function getAccountByNumber(Request $request)
    {
        $accountNumber = $request->get('account_number');
        if (!$accountNumber) {
            throw new \Exception('Account number is required');
        }

        $account = Account::where('code', $accountNumber)
            ->first();

        if (!$account) {
            throw new \Exception('Account not found');
        }

        return [
            'id' => $account->id,
            'account_number' => $account->code,
            'account_name' => $account->name,
            'type' => $account->type
        ];
    }

    /**
     * Get account details by account name
     */
    public function getAccountByName(Request $request)
    {
        $accountName = $request->get('account_name');
        if (!$accountName) {
            throw new \Exception('Account name is required');
        }

        $account = Account::where('name', 'like', $accountName . '%')
            ->first();

        if (!$account) {
            throw new \Exception('Account not found');
        }

        return [
            'id' => $account->id,
            'account_number' => $account->code,
            'account_name' => $account->name,
            'type' => $account->type
        ];
    }

    /**
     * Get form data for creating/editing services
     */
    public function getFormData(?Request $request = null)
    {
        try {
            return [
                'customers' => Customer::select('id', 'customer_number', 'first_name', 'email', 'phone')
                    ->orderBy('customer_number')
                    ->get(),

                'accounts' => Account::select('id', 'code', 'name', 'type')
                    ->orderBy('code')
                    ->get()
                    ->map(function ($account) {
                        return [
                            'id' => $account->id,
                            'account_number' => $account->code,
                            'account_name' => $account->name,
                            'type' => $account->type,
                            'display_text' => $account->code . ' - ' . $account->name
                        ];
                    }),

                'currencies' => Currency::select('id', 'name', 'code', 'symbol')
                    ->get(),

                'units' => Unit::select('id', 'name', 'symbol')->get(),

                'tax_rates' => TaxRate::select('id', 'name', 'code', 'rate', 'type')
                    ->get(),

                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'approved', 'label' => 'Approved'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ],

                // Account integration helpers
                'account_types' => [
                    ['value' => 'asset', 'label' => 'Asset'],
                    ['value' => 'liability', 'label' => 'Liability'],
                    ['value' => 'equity', 'label' => 'Equity'],
                    ['value' => 'revenue', 'label' => 'Revenue'],
                    ['value' => 'expense', 'label' => 'Expense'],
                ],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error fetching form data: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific service
     */
    public function show($id)
    {
        try {
            $service = Sale::with([
                'customer',
                'items.account',
                'items.unit',
                'user',
                'employee',
                'currency',
                'branch'
            ])
            ->where('type', SalesTypeEnum::SERVICE)
            ->where('id', $id)
            ->first();

            if (!$service) {
                throw new \Exception('Service not found');
            }

            return $service;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching service: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing service
     */
    public function update(ServiceRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $service = Sale::with(['items'])
                    ->where('type', SalesTypeEnum::SERVICE)
                    ->where('id', $id)
                    ->first();

                if (!$service) {
                    throw new \Exception('Service not found');
                }

                // Check if service can be updated
                if ($service->status === 'completed') {
                    throw new \Exception('Cannot update completed service');
                }

                $validatedData = $request->validated();
                $userId = Auth::id();
                $companyId = Auth::user()->company_id ?? $service->company_id;

                // Get customer data for auto-population if customer changed
                $customer = null;
                if (isset($validatedData['customer_id']) && $validatedData['customer_id'] != $service->customer_id) {
                    $customer = Customer::find($validatedData['customer_id']);
                }

                // Get live exchange rate if currency changed
                $exchangeRate = $service->exchange_rate;
                if (isset($validatedData['currency_id']) && $validatedData['currency_id'] != $service->currency_id) {
                    $exchangeRate = $this->getLiveExchangeRate($validatedData['currency_id']);
                }

                // Prepare complete update data
                $updateData = [
                    'updated_by' => $userId,
                    'company_id' => $companyId,

                    // Customer information
                    'customer_id' => $validatedData['customer_id'] ?? $service->customer_id,
                    'customer_email' => $validatedData['customer_email'] ??
                                      ($customer ? $customer->email : $service->customer_email),

                    // Service details
                    'due_date' => $validatedData['due_date'] ?? $service->due_date,
                    'licensed_operator' => $validatedData['licensed_operator'] ?? $service->licensed_operator,
                    'notes' => $validatedData['notes'] ?? $service->notes,

                    // Employee and branch information
                    'employee_id' => $validatedData['employee_id'] ?? $service->employee_id,
                    'branch_id' => $validatedData['branch_id'] ?? $service->branch_id,

                    // Currency and exchange rate
                    'currency_id' => $validatedData['currency_id'] ?? $service->currency_id,
                    'exchange_rate' => $exchangeRate,

                    // Tax settings
                    'is_tax_inclusive' => $validatedData['is_tax_inclusive'] ?? $service->is_tax_inclusive,
                    'tax_percentage' => $validatedData['tax_percentage'] ?? $service->tax_percentage,
                ];

                // Update the service
                $service->update($updateData);

                // Update service items if provided
                if (isset($validatedData['items']) && is_array($validatedData['items'])) {
                    // Delete existing items (soft delete)
                    $service->items()->delete();

                    // Create new items
                    $this->createServiceItems($service, $validatedData['items']);

                    // Recalculate totals
                    $this->calculateServiceTotals($service);
                }

                // Reload with all relationships for complete response
                return $service->load([
                    'customer',
                    'items.account',
                    'items.unit',
                    'user',
                    'employee',
                    'currency',
                    'branch'
                ]);
            });

        } catch (Exception $e) {
            Log::error('Error updating service: ' . $e->getMessage());
            throw new \Exception('Error updating service: ' . $e->getMessage());
        }
    }

    /**
     * Delete a service (soft delete)
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $service = Sale::with(['items'])
                    ->where('type', SalesTypeEnum::SERVICE)
                    ->where('id', $id)
                    ->first();

                if (!$service) {
                    throw new \Exception('Service not found');
                }

                // Check if service can be deleted
                if ($service->status === 'completed') {
                    throw new \Exception('Cannot delete completed service');
                }

                // Set deleted_by before soft delete
                $service->update([
                    'deleted_by' => Auth::id(),
                    'status' => 'cancelled'
                ]);

                // Soft delete the service items first
                foreach ($service->items as $item) {
                    $item->update(['deleted_by' => Auth::id()]);
                    $item->delete();
                }

                // Soft delete the service
                $service->delete();

                return [
                    'success' => true,
                    'message' => 'Service deleted successfully',
                    'service_id' => $id,
                    'invoice_number' => $service->invoice_number
                ];
            });

        } catch (Exception $e) {
            Log::error('Error deleting service: ' . $e->getMessage());
            throw new \Exception('Error deleting service: ' . $e->getMessage());
        }
    }

    /**
     * Get deleted services (soft deleted)
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
                ->where('type', SalesTypeEnum::SERVICE);

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
                'id', 'book_code', 'invoice_number', 'date', 'total_amount',
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
            throw new \Exception('Error fetching deleted services: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft deleted service
     */
    public function restore($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $service = Sale::onlyTrashed()
                    ->where('type', SalesTypeEnum::SERVICE)
                    ->where('id', $id)
                    ->first();

                if (!$service) {
                    throw new \Exception('Deleted service not found');
                }

                // Restore the service items first
                SaleItem::onlyTrashed()
                    ->where('sale_id', $id)
                    ->restore();

                // Restore the service
                $service->restore();

                // Update status back to draft
                $service->update([
                    'status' => 'draft',
                    'deleted_by' => null
                ]);

                return [
                    'success' => true,
                    'message' => 'Service restored successfully',
                    'service_id' => $id,
                    'invoice_number' => $service->invoice_number
                ];
            });

        } catch (\Exception $e) {
            throw new \Exception('Error restoring service: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a service (permanent deletion)
     */
    public function forceDelete($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $service = Sale::onlyTrashed()
                    ->where('type', SalesTypeEnum::SERVICE)
                    ->where('id', $id)
                    ->first();

                if (!$service) {
                    throw new \Exception('Deleted service not found');
                }

                // Force delete all service items
                SaleItem::onlyTrashed()
                    ->where('sale_id', $id)
                    ->forceDelete();

                // Force delete the service
                $service->forceDelete();

                return [
                    'success' => true,
                    'message' => 'Service permanently deleted',
                    'service_id' => $id
                ];
            });

        } catch (\Exception $e) {
            throw new \Exception('Error permanently deleting service: ' . $e->getMessage());
        }
    }
}
