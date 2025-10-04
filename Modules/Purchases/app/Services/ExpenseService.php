<?php

namespace Modules\Purchases\app\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Purchases\Http\Requests\ExpenseRequest;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Suppliers\Models\Supplier;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\FinancialAccounts\Models\Account;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;

class ExpenseService
{
    /**
     * Get paginated list of expenses with search and sorting
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $query = Purchase::with([
                'supplier:id,supplier_name_ar,supplier_name_en,supplier_number,email,mobile',
                'currency:id,code,name,symbol',
                'items:id,purchase_id,quantity,unit_price,total',
                'creator:id',
                'company:id'
            ])
            ->where('type', 'expense');

            // Apply search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting with validation
            $allowedSortFields = $this->getAllowedSortFields();
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching expenses: ' . $e->getMessage());
        }
    }

    /**
     * Apply search filters to the query
     */
    private function applySearchFilters($query, Request $request)
    {
        // Search by invoice number (exact or range)
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('invoice_number_from') && $request->filled('invoice_number_to')) {
            $fromNumber = (int) $request->invoice_number_from;
            $toNumber = (int) $request->invoice_number_to;
            $query->whereBetween('invoice_number', [$fromNumber, $toNumber]);
        }

        // Search by expense number (exact or range)
        if ($request->filled('expense_number')) {
            $query->where('expense_number', 'like', '%' . $request->expense_number . '%');
        }

        if ($request->filled('expense_number_from') && $request->filled('expense_number_to')) {
            $query->whereBetween('expense_number', [$request->expense_number_from, $request->expense_number_to]);
        }

        // Search by supplier name
        if ($request->filled('supplier_name')) {
            $query->where(function ($q) use ($request) {
                $q->where('supplier_name', 'like', '%' . $request->supplier_name . '%')
                  ->orWhereHas('supplier', function ($sq) use ($request) {
                      $sq->where('supplier_name_ar', 'like', '%' . $request->supplier_name . '%')
                        ->orWhere('supplier_name_en', 'like', '%' . $request->supplier_name . '%')
                        ->orWhere('supplier_number', 'like', '%' . $request->supplier_name . '%');
                  });
            });
        }

        // Search by supplier (legacy support)
        if ($request->filled('supplier_search')) {
            $query->where(function ($q) use ($request) {
                $q->where('supplier_name', 'like', '%' . $request->supplier_search . '%')
                  ->orWhereHas('supplier', function ($sq) use ($request) {
                      $sq->where('supplier_name_ar', 'like', '%' . $request->supplier_search . '%')
                        ->orWhere('supplier_name_en', 'like', '%' . $request->supplier_search . '%')
                        ->orWhere('supplier_number', 'like', '%' . $request->supplier_search . '%');
                  });
            });
        }

        // Search by date (exact or range)
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        // Search by amount (exact or range)
        if ($request->filled('amount')) {
            $query->where('total_amount', $request->amount);
        }

        if ($request->filled('total_amount')) {
            $query->where('total_amount', $request->total_amount);
        }

        if ($request->filled('amount_from') && $request->filled('amount_to')) {
            $query->whereBetween('total_amount', [$request->amount_from, $request->amount_to]);
        }

        // Search by currency
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        if ($request->filled('currency')) {
            $query->where('currency_id', $request->currency);
        }

        // Search by licensed operator
        if ($request->filled('licensed_operator')) {
            $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
        }

        // Search by journal entry number (exact or range)
        if ($request->filled('journal_number')) {
            $query->where('journal_number', $request->journal_number);
        }

        if ($request->filled('journal_number_from') && $request->filled('journal_number_to')) {
            $fromJournal = (int) $request->journal_number_from;
            $toJournal = (int) $request->journal_number_to;
            $query->whereBetween('journal_number', [$fromJournal, $toJournal]);
        }

        // Search by journal code
        if ($request->filled('journal_code')) {
            $query->where('journal_code', 'like', '%' . $request->journal_code . '%');
        }

        // Search by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }

    /**
     * Get allowed sort fields for expenses
     */
    private function getAllowedSortFields(): array
    {
        return [
            'id',
            'expense_number',
            'date',
            'time',
            'due_date',
            'supplier_name',
            'licensed_operator',
            'supplier_email',
            'total_amount',
            'grand_total',
            'currency_id',
            'exchange_rate',
            'currency_rate',
            'status',
            'created_at',
            'updated_at',
            'ledger_code',
            'ledger_number',
            'invoice_number'
        ];
    }

    /**
     * Show a specific expense with all related data
     */
    public function show($id)
    {
        try {
            $expense = Purchase::with([
                'supplier:id,supplier_name_ar,supplier_name_en,supplier_number,email,mobile,phone,address_one,tax_number',
                'currency:id,code,name,symbol',
                'taxRate:id,name,rate',
                'company:id',
                'branch:id,name',
                'journal:id,name,code',
                'items.account:id,code,name,type',
                'creator:id,email',
                'updater:id,email',
                'deleter:id,email'
            ])
            ->where('type', 'expense')
            ->findOrFail($id);

            return $expense;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching expense: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing expense
     */
    public function update(ExpenseRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $expense = Purchase::where('type', 'expense')->findOrFail($id);

                // Prevent updating invoiced expenses
                if ($expense->status === 'invoiced') {
                    throw new \Exception('Cannot update invoiced expenses.');
                }

                $userId = Auth::id();

                if (!$userId) {
                    // Fallback to first user if no authenticated user (for testing/seeding)
                    $firstUser = \Modules\Users\Models\User::first();
                    if (!$firstUser) {
                        throw new \Exception('No users found in the system');
                    }
                    $userId = $firstUser->id;
                }

                // Get live exchange rate if currency changed
                $exchangeRate = $expense->exchange_rate;
                if ($request->filled('currency_id') && $request->currency_id != $expense->currency_id) {
                    $exchangeRate = $this->getLiveExchangeRate($request->currency_id);
                }

                // Update expense data
                $updateData = array_merge($request->all(), [
                    'updated_by' => $userId,
                    'currency_rate' => $exchangeRate,
                    'exchange_rate' => $exchangeRate,
                ]);

                $expense->update($updateData);

                // Update items if provided
                if ($request->has('items') && is_array($request->items)) {
                    // Delete existing items
                    $expense->items()->delete();

                    // Create new items
                    $this->createExpenseItems($expense, $request->items);
                }

                // Recalculate totals
                $this->calculateExpenseTotals($expense);

                return $expense->load([
                    'supplier',
                    'currency',
                    'items.account',
                    'creator',
                    'updater'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating expense: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete an expense
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $expense = Purchase::where('type', 'expense')->findOrFail($id);

                // Prevent deleting invoiced expenses
                if ($expense->status === 'invoiced') {
                    throw new \Exception('Cannot delete invoiced expenses.');
                }

                $userId = Auth::id();
                if (!$userId) {
                    $firstUser = \Modules\Users\Models\User::first();
                    $userId = $firstUser?->id;
                }
                $expense->update(['deleted_by' => $userId]);
                $expense->delete();

                return true;
            });
        } catch (\Exception $e) {
            throw new \Exception('Error deleting expense: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft deleted expense
     */
    public function restore($id)
    {
        try {
            $expense = Purchase::where('type', 'expense')->withTrashed()->findOrFail($id);
            $expense->restore();
            $expense->update(['deleted_by' => null]);

            return $expense;
        } catch (\Exception $e) {
            throw new \Exception('Error restoring expense: ' . $e->getMessage());
        }
    }

    /**
     * Get soft deleted expenses
     */
    public function getDeleted(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);

            return Purchase::with([
                'supplier:id,supplier_name_ar,supplier_name_en,supplier_number',
                'currency:id,code,symbol',
                'deleter:id'
            ])
            ->where('type', 'expense')
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching deleted expenses: ' . $e->getMessage());
        }
    }

    /**
     * Store a new expense
     */
    public function store(ExpenseRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->company_id;
                $userId = Auth::id();

                if (!$userId) {
                    // Fallback to first user if no authenticated user (for testing/seeding)
                    $firstUser = \Modules\Users\Models\User::first();
                    if (!$firstUser) {
                        throw new \Exception('No users found in the system');
                    }
                    $userId = $firstUser->id;
                }

                if (!$companyId) {
                    throw new \Exception('Company ID is required');
                }

                // Generate expense number and ledger information
                try {
                    $expenseNumber = Purchase::generateExpenseNumber();
                } catch (\Exception $e) {
                    throw new \Exception('Error generating expense number: ' . $e->getMessage());
                }

                try {
                    $ledgerInfo = Purchase::generateLedgerCode($companyId);
                } catch (\Exception $e) {
                    throw new \Exception('Error generating ledger code: ' . $e->getMessage());
                }

                // Get live exchange rate if currency is provided
                $exchangeRate = 1.0;
                if ($request->filled('currency_id')) {
                    $exchangeRate = $this->getLiveExchangeRate($request->currency_id);
                }

                // Create the expense record
                $expenseData = [
                    'type' => 'expense',
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                 //   'expense_number' => $expenseNumber,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                    'status' => 'draft',
                    'currency_rate' => $exchangeRate,
                    'exchange_rate' => $exchangeRate,
                ] + $ledgerInfo + $request->all();

                try {
                    $expense = Purchase::create($expenseData);
                } catch (\Exception $e) {
                    throw new \Exception('Error creating expense record: ' . $e->getMessage());
                }

                // Create expense items
                if ($request->has('items') && is_array($request->items)) {
                    try {
                        $this->createExpenseItems($expense, $request->items);
                    } catch (\Exception $e) {
                        throw new \Exception('Error creating expense items: ' . $e->getMessage());
                    }
                }

                // Calculate totals
                $this->calculateExpenseTotals($expense);

                return $expense->load([
                    'supplier',
                    'currency',
                    'items.account',
                    'creator'
                ]);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating expense: ' . $e->getMessage());
        }
    }

    /**
     * Create expense items
     */
    private function createExpenseItems(Purchase $expense, array $items)
    {
        foreach ($items as $index => $itemData) {
            $item = [
                'purchase_id' => $expense->id,
                'serial_number' => $index + 1,
                'item_id' => $itemData['item_id'] ?? null,
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $itemData['unit_price'] ?? 0,
                'discount_rate' => $itemData['discount_rate'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'total_foreign' => $itemData['total_foreign'] ?? 0,
                'total_local' => $itemData['total_local'] ?? 0,
                'total' => $itemData['total'] ?? 0,
                'notes' => $itemData['notes'] ?? null,
                'description' => $itemData['description'] ?? null,
            ];

            // Handle account information
            if (isset($itemData['account_id'])) {
                $account = Account::find($itemData['account_id']);
                if ($account) {
                    $item['account_id'] = $account->id;
                    $item['account_number'] = $account->code;
                    $item['account_name'] = $account->name;
                } else {
                    throw new \Exception('Account with ID ' . $itemData['account_id'] . ' not found');
                }
            }

            try {
                PurchaseItem::create($item);
            } catch (\Exception $e) {
                throw new \Exception('Error creating purchase item: ' . $e->getMessage() . '. Item data: ' . json_encode($item));
            }
        }
    }

    /**
     * Calculate expense totals
     */
    private function calculateExpenseTotals(Purchase $expense)
    {
        $items = $expense->items;

        $subtotal = $items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $taxAmount = 0;
        if ($expense->tax_percentage > 0) {
            $taxAmount = ($subtotal * $expense->tax_percentage) / 100;
        }

        $total = $subtotal + $taxAmount;

        $expense->update([
            'total_without_tax' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
            'grand_total' => $total,
            'total_local' => $total * $expense->exchange_rate,
            'total_foreign' => $total,
        ]);
    }

    /**
     * Get live exchange rate for currency
     */
    public function getLiveExchangeRate($currencyId): float
    {
        try {
            $currency = Currency::find($currencyId);
            if (!$currency) {
                return 1.0;
            }

            // If it's the base currency, return 1.0
            if ($currency->code === 'USD' || $currency->code === 'SAR') {
                return 1.0;
            }

            // Try to get live rate from external API
            $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');

            if ($response->successful()) {
                $rates = $response->json()['rates'] ?? [];
                return $rates[$currency->code] ?? 1.0;
            }

            return 1.0;
        } catch (\Exception $e) {
            return 1.0;
        }
    }

    /**
     * Get suppliers for dropdown
     */
    public function getSuppliers(Request $request)
    {
        try {
            $search = $request->get('search', '');

            return Supplier::when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('supplier_name_ar', 'like', '%' . $search . '%')
                          ->orWhere('supplier_name_en', 'like', '%' . $search . '%')
                          ->orWhere('supplier_number', 'like', '%' . $search . '%');
                    });
                })
                ->select(['id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en', 'email', 'mobile'])
                ->limit(50)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Get accounts for dropdown
     */
    public function getAccounts(Request $request)
    {
        try {
            $search = $request->get('search', '');

            return Account::where('type', 'expense') // Only expense accounts
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                          ->orWhere('code', 'like', '%' . $search . '%');
                    });
                })
                ->select(['id', 'code', 'name'])
                ->limit(50)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching accounts: ' . $e->getMessage());
        }
    }

    /**
     * Get currencies for dropdown
     */
    public function getCurrencies(Request $request)
    {
        try {
            return Currency::select(['id', 'code', 'name', 'symbol'])
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching currencies: ' . $e->getMessage());
        }
    }

    /**
     * Get tax rates for dropdown
     */
    public function getTaxRates(Request $request)
    {
        try {
            return TaxRate::select(['id', 'name', 'rate'])

                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching tax rates: ' . $e->getMessage());
        }
    }

    /**
     * Get search form data for expenses
     */
    public function getSearchFormData(Request $request)
    {
        try {
            return [
                'suppliers' => Supplier::select(['id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en'])
                    ->limit(100)
                    ->get(),
                'currencies' => Currency::select(['id', 'code', 'name', 'symbol'])
                    ->get(),
                'status_options' => Purchase::STATUS_OPTIONS,
                'sortable_fields' => $this->getSortableFields(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error fetching search form data: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields for expenses
     */
    public function getSortableFields(): array
    {
        return [
            'id' => 'ID',
            'expense_number' => 'Expense Number',
            'invoice_number' => 'Invoice Number',
            'date' => 'Date',
            'time' => 'Time',
            'due_date' => 'Due Date',
            'supplier_name' => 'Supplier Name',
            'licensed_operator' => 'Licensed Operator',
            'supplier_email' => 'Supplier Email',
            'total_amount' => 'Total Amount',
            'grand_total' => 'Grand Total',
            'currency_id' => 'Currency',
            'exchange_rate' => 'Exchange Rate',
            'currency_rate' => 'Currency Rate',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'ledger_code' => 'Ledger Code',
            'ledger_number' => 'Ledger Number',
            'journal_number' => 'Journal Number',
            'journal_code' => 'Journal Code',
            'tax_percentage' => 'Tax Percentage',
            'tax_amount' => 'Tax Amount',
            'total_without_tax' => 'Total Without Tax',
            'discount_percentage' => 'Discount Percentage',
            'discount_amount' => 'Discount Amount',
        ];
    }
}
