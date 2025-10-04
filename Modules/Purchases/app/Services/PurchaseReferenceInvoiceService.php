<?php

namespace Modules\Purchases\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Purchases\Http\Requests\PurchaseReferenceInvoiceRequest;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Suppliers\Models\Supplier;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;

class PurchaseReferenceInvoiceService
{
    /**
     * Get paginated list of purchase reference invoices with search and sorting
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort field
            if (!in_array($sortBy, $this->getAllowedSortFields())) {
                $sortBy = 'created_at';
            }

            $query = Purchase::with([
                'supplier:id,supplier_name_ar,supplier_name_en,supplier_number,email,mobile',
                'currency:id,code,name,symbol',
                'items:id,purchase_id,serial_number,item_number,item_name,unit_name,quantity,unit_price,first_selling_price,total',
                'creator:id,first_name,second_name,email',
                'company:id,title'
            ])
            ->where('type', 'purchase_reference_invoice');

            // Apply search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching purchase reference invoices: ' . $e->getMessage());
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

        // Search by purchase reference invoice number
        if ($request->filled('purchase_reference_invoice_number')) {
            $query->where('purchase_reference_invoice_number', 'like', '%' . $request->purchase_reference_invoice_number . '%');
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

        if ($request->filled('amount_from') && $request->filled('amount_to')) {
            $query->whereBetween('total_amount', [$request->amount_from, $request->amount_to]);
        }

        // Search by currency
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        // Search by licensed operator
        if ($request->filled('licensed_operator')) {
            $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
        }

        // Search by ledger code
        if ($request->filled('ledger_code')) {
            $query->where('ledger_code', 'like', '%' . $request->ledger_code . '%');
        }

        // Search by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by entry number (journal number) - exact or range
        if ($request->filled('journal_number')) {
            $query->where('journal_number', $request->journal_number);
        }

        if ($request->filled('journal_number_from') && $request->filled('journal_number_to')) {
            $fromNumber = (int) $request->journal_number_from;
            $toNumber = (int) $request->journal_number_to;
            $query->whereBetween('journal_number', [$fromNumber, $toNumber]);
        }

        // Search by entry number (journal number) using like for partial matches
        if ($request->filled('entry_number')) {
            $query->where('journal_number', 'like', '%' . $request->entry_number . '%');
        }

        if ($request->filled('entry_number_from') && $request->filled('entry_number_to')) {
            $fromNumber = (int) $request->entry_number_from;
            $toNumber = (int) $request->entry_number_to;
            $query->whereBetween('journal_number', [$fromNumber, $toNumber]);
        }
    }

    /**
     * Get allowed sort fields
     */
    private function getAllowedSortFields(): array
    {
        return [
            'id', 'purchase_reference_invoice_number', 'invoice_number', 'date', 'time', 'due_date',
            'supplier_name', 'licensed_operator', 'supplier_email', 'total_amount', 'grand_total',
            'currency_id', 'exchange_rate', 'currency_rate', 'status', 'created_at', 'updated_at',
            'ledger_code', 'ledger_number', 'journal_number', 'journal_code', 'tax_percentage',
            'tax_amount', 'total_without_tax', 'discount_percentage', 'discount_amount'
        ];
    }

    /**
     * Show a specific purchase reference invoice with all related data
     */
    public function show($id)
    {
        try {
            $invoice = Purchase::with([
                'supplier:id,supplier_name_ar,supplier_name_en,supplier_number,email,mobile,phone,address_one,tax_number',
                'currency:id,code,name,symbol',
                'taxRate:id,name,rate',
                'company:id,title',
                'branch:id,name',
                'journal:id,name,code',
                'items.item:id,item_number',
                'items.unit:id,unit_name_ar,unit_name_en',
                'creator:id,first_name,second_name,email',
                'updater:id,first_name,second_name,email',
                'deleter:id,first_name,second_name,email'
            ])
            ->where('type', 'purchase_reference_invoice')
            ->findOrFail($id);

            return $invoice;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e; // Re-throw the ModelNotFoundException
        } catch (\Exception $e) {
            throw new \Exception('Error fetching purchase reference invoice: ' . $e->getMessage());
        }
    }

    /**
     * Store a new purchase reference invoice
     */
    public function store(PurchaseReferenceInvoiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                Log::info('Starting purchase reference invoice creation');
                Log::info('Request data: ', $request->all());

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

                Log::info('User ID: ' . $userId);
                Log::info('Company ID: ' . $companyId);

                // Validate that referenced records exist
                $this->validateReferencedRecords($request);

                // Generate purchase reference invoice number
                try {
                    $invoiceNumber = Purchase::generatePurchaseReferenceInvoiceNumber();
                } catch (\Exception $e) {
                    throw new \Exception('Error generating invoice number: ' . $e->getMessage());
                }

                // Generate ledger information
                try {
                    $ledgerInfo = Purchase::generateLedgerCode($companyId);
                } catch (\Exception $e) {
                    throw new \Exception('Error generating ledger code: ' . $e->getMessage());
                }

                // Get live exchange rate
                try {
                    $exchangeRate = $this->getLiveExchangeRate($request->currency_id);
                } catch (\Exception $e) {
                    throw new \Exception('Error getting exchange rate: ' . $e->getMessage());
                }

                // Calculate currency rate with tax if applicable
                $currencyRateWithTax = $exchangeRate;
                if ($request->filled('is_tax_applied_to_currency_rate') && $request->is_tax_applied_to_currency_rate) {
                    $taxRate = 0;
                    if ($request->filled('tax_rate_id')) {
                        $taxRateModel = TaxRate::find($request->tax_rate_id);
                        $taxRate = $taxRateModel ? $taxRateModel->rate : 0;
                    } else {
                        $taxRate = $request->tax_percentage ?? 0;
                    }
                    $currencyRateWithTax = $exchangeRate * (1 + ($taxRate / 100));
                }

                // Prepare invoice data using only columns that exist in the base table
                $invoiceData = [
                    'type' => PurchaseTypeEnum::PURCHASE_REFERENCE_INVOICE,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'branch_id' => $request->branch_id ?? 1,
                    'currency_id' => $request->currency_id ?? 1,
                    'employee_id' => $request->employee_id ?? $userId,
                    'supplier_id' => $request->supplier_id ?? 1,
                    'journal_id' => $request->journal_id ?? 1,
                    'journal_number' => $request->journal_number ?? 1,
                    'exchange_rate' => $exchangeRate,
                    'cash_paid' => $request->cash_paid ?? 0,
                    'checks_paid' => $request->checks_paid ?? 0,
                    'allowed_discount' => $request->allowed_discount ?? 0,
                    'total_without_tax' => $request->total_without_tax ?? 0,
                    'tax_percentage' => $request->tax_percentage ?? 0,
                    'tax_amount' => $request->tax_amount ?? 0,
                    'total_amount' => $request->total_amount ?? 0,
                    'remaining_balance' => $request->remaining_balance ?? 0,
                    // These fields are required and cannot be null
                    'total_foreign' => $request->total_foreign ?? 0.0000,
                    'total_local' => $request->total_local ?? 0.0000,
                    'notes' => $request->notes ?? '',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'deleted_by' => $userId,
                ];

                // Add ledger info if columns exist
                if (isset($ledgerInfo['ledger_code'])) {
                    $invoiceData['ledger_code'] = $ledgerInfo['ledger_code'];
                }
                if (isset($ledgerInfo['ledger_number'])) {
                    $invoiceData['ledger_number'] = $ledgerInfo['ledger_number'];
                }

                // Add purchase reference invoice number if column exists
                $invoiceData['purchase_reference_invoice_number'] = $invoiceNumber;

                // Create the purchase reference invoice
                try {
                    $invoice = Purchase::create($invoiceData);

                    if (!$invoice) {
                        throw new \Exception('Purchase::create() returned null - check database constraints and fillable fields');
                    }

                    if (!$invoice->id) {
                        throw new \Exception('Purchase created but has no ID - possible database issue');
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Error creating purchase invoice: ' . $e->getMessage());
                }

                // Create invoice items
                if ($request->has('items') && is_array($request->items)) {
                    try {
                        $this->createInvoiceItems($invoice, $request->items);
                    } catch (\Exception $e) {
                        throw new \Exception('Error creating invoice items: ' . $e->getMessage());
                    }
                }

                // Calculate totals
                try {
                    $this->calculateInvoiceTotals($invoice);
                } catch (\Exception $e) {
                    throw new \Exception('Error calculating invoice totals: ' . $e->getMessage());
                }

                return $invoice->load([
                    'supplier',
                    'currency',
                    'items.item',
                    'items.unit',
                    'creator'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error creating purchase reference invoice: ' . $e->getMessage());
        }
    }

    /**
     * Validate that all referenced records exist in the database
     */
    private function validateReferencedRecords(PurchaseReferenceInvoiceRequest $request)
    {
        $errors = [];

        // Check company exists
        if ($request->company_id) {
            $company = \Modules\Companies\Models\Company::find($request->company_id);
            if (!$company) {
                $errors[] = "Company with ID {$request->company_id} does not exist";
            }
        }

        // Check supplier exists
        if ($request->supplier_id) {
            $supplier = Supplier::find($request->supplier_id);
            if (!$supplier) {
                $errors[] = "Supplier with ID {$request->supplier_id} does not exist";
            }
        }

        // Check currency exists
        if ($request->currency_id) {
            $currency = Currency::find($request->currency_id);
            if (!$currency) {
                $errors[] = "Currency with ID {$request->currency_id} does not exist";
            }
        }

        // Check branch exists (if provided)
        if ($request->branch_id) {
            $branch = \Modules\Companies\Models\Branch::find($request->branch_id);
            if (!$branch) {
                $errors[] = "Branch with ID {$request->branch_id} does not exist";
            }
        }

        // Check journal exists (if provided)
        if ($request->journal_id) {
            $journal = \Modules\Billing\Models\Journal::find($request->journal_id);
            if (!$journal) {
                $errors[] = "Journal with ID {$request->journal_id} does not exist";
            }
        }

        // Check items exist
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $index => $itemData) {
                if (isset($itemData['item_id'])) {
                    $item = Item::find($itemData['item_id']);
                    if (!$item) {
                        $errors[] = "Item with ID {$itemData['item_id']} at index {$index} does not exist";
                    }
                }
            }
        }

        if (!empty($errors)) {
            Log::error('Validation errors for purchase reference invoice:', $errors);
            throw new \Exception('Referenced records validation failed: ' . implode(', ', $errors));
        }

        Log::info('All referenced records validated successfully');
    }

    /**
     * Create invoice items
     */
    private function createInvoiceItems(Purchase $invoice, array $items)
    {
        $serialNumber = 1;

        foreach ($items as $itemData) {
            // Get item details
            $item = Item::find($itemData['item_id']);
            if (!$item) {
                throw new \Exception("Item with ID {$itemData['item_id']} not found");
            }

            // Get unit details - handle case where unit doesn't exist
            $unit = null;
            $unitId = null;
            $unitName = null;

            if ($item->unit_id) {
                $unit = Unit::find($item->unit_id);
                if ($unit) {
                    $unitId = $unit->id;
                    $unitName = $unit->name ?? $unit->unit_name_ar ?? $unit->unit_name_en;
                } else {
                    // If unit doesn't exist, use the first available unit
                    $firstUnit = Unit::first();
                    if ($firstUnit) {
                        $unitId = $firstUnit->id;
                        $unitName = $firstUnit->name ?? $firstUnit->unit_name_ar ?? $firstUnit->unit_name_en;
                    }
                }
            }

            // Calculate total
            $quantity = (float) $itemData['quantity'];
            $unitPrice = (float) ($itemData['unit_price'] ?? $item->first_selling_price ?? 0);
            $total = $quantity * $unitPrice;

            PurchaseItem::create([
                'purchase_id' => $invoice->id,
                'serial_number' => $serialNumber++,
                'item_id' => $itemData['item_id'],
                'item_number' => $item->item_number,
                'item_name' => $item->item_name_ar ?? $item->item_name_en ?? $item->name,
                'unit_id' => $unitId,
                'unit_name' => $unitName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'first_selling_price' => $item->first_selling_price ?? $unitPrice,
                'total' => $total,
                'notes' => $itemData['notes'] ?? null,
                'affects_inventory' => true,
            ]);
        }
    }

    /**
     * Calculate invoice totals
     */
    private function calculateInvoiceTotals(Purchase $invoice)
    {
        $items = $invoice->items;
        $subtotal = $items->sum('total');

        // Calculate discount
        $discountAmount = 0;
        if ($invoice->discount_percentage > 0) {
            $discountAmount = $subtotal * ($invoice->discount_percentage / 100);
        } elseif ($invoice->discount_amount > 0) {
            $discountAmount = $invoice->discount_amount;
        }

        $totalAfterDiscount = $subtotal - $discountAmount;

        // Calculate tax
        $taxAmount = 0;
        if ($invoice->tax_percentage > 0) {
            $taxAmount = $totalAfterDiscount * ($invoice->tax_percentage / 100);
        }

        $grandTotal = $totalAfterDiscount + $taxAmount;

        // Update invoice totals
        $invoice->update([
            'total_without_tax' => $totalAfterDiscount,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $grandTotal,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * Get live exchange rate from external API
     */
    public function getLiveExchangeRate($currencyId): float
    {
        try {
            $currency = Currency::find($currencyId);

            if (!$currency || $currency->code === 'USD' || $currency->code === 'SAR') {
                return 1.0;
            }

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
     * Get suppliers for dropdown (with search)
     */
    public function getSuppliers(Request $request)
    {
        try {
//$companyId = $request->company_id;
            $search = $request->get('search', '');

            $query = Supplier::
                select(['id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en', 'email', 'mobile']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('supplier_name_ar', 'like', '%' . $search . '%')
                      ->orWhere('supplier_name_en', 'like', '%' . $search . '%')
                      ->orWhere('supplier_number', 'like', '%' . $search . '%');
                });
            }

            return $query->limit(50)->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Get items for dropdown (with search)
     */
    public function getItems(Request $request)
    {
        try {
         //   $companyId = $request->company_id;
            $search = $request->get('search', '');

            $query = Item::
                with('unit:id')
                ->select(['id', 'item_number', 'unit_id']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->
                      Where('item_number', 'like', '%' . $search . '%');
                });
            }

            return $query->limit(100)->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching items: ' . $e->getMessage());
        }
    }

    /**
     * Get currencies for dropdown
     */
    public function getCurrencies(Request $request)
    {
        try {
            $companyId = $request->company_id;

            return Currency::where('company_id', $companyId)
                ->select(['id', 'code', 'name', 'symbol'])
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
          //  $companyId = $request->company_id;

            return TaxRate::
                // where('is_active', true)
                select(['id', 'name', 'rate'])
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching tax rates: ' . $e->getMessage());
        }
    }

    /**
     * Get form data for creating new purchase reference invoice
     */
    public function getFormData(Request $request)
    {
        try {
            return [
                'suppliers' => $this->getSuppliers($request),
                'items' => $this->getItems($request),
                'currencies' => $this->getCurrencies($request),
                'tax_rates' => $this->getTaxRates($request),
                'next_invoice_number' => Purchase::generatePurchaseReferenceInvoiceNumber(),
                'current_date' => now()->toDateString(),
                'current_time' => now()->toTimeString(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error fetching form data: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase reference invoice
     */
    public function update($id, PurchaseReferenceInvoiceRequest $request)
    {
        return DB::transaction(function () use ($id, $request) {
            $invoice = Purchase::where('type', 'purchase_reference_invoice')
                ->findOrFail($id);

            // Check if invoice is already invoiced (prevent updates)
            if ($invoice->status === 'invoiced') {
                throw new \Exception('Cannot update invoiced purchase reference invoice.');
            }

            // Get live exchange rate if currency changed
            $exchangeRate = $this->getLiveExchangeRate($request->currency_id);

            // Calculate currency rate with tax if applicable
            $currencyRateWithTax = $exchangeRate;
            if ($request->is_tax_applied_to_currency_rate) {
                $taxRate = TaxRate::find($request->tax_rate_id)?->rate ?? $request->tax_percentage ?? 0;
                $currencyRateWithTax = $exchangeRate * (1 + ($taxRate / 100));
            }

            // Update invoice data
            $updateData = $request->validated();
            $updateData['exchange_rate'] = $exchangeRate;
            $updateData['currency_rate'] = $exchangeRate;
            $updateData['currency_rate_with_tax'] = $currencyRateWithTax;
            $updateData['updated_by'] = Auth::id();

            $invoice->update($updateData);

            // Update items if provided
            if ($request->has('items')) {
                // Delete existing items
                $invoice->items()->delete();

                // Create new items
                $this->createInvoiceItems($invoice, $request->items);
            }

            // Recalculate totals
            $this->calculateInvoiceTotals($invoice);

            return $invoice->load([
                'supplier', 'currency', 'taxRate', 'company', 'branch', 'journal',
                'items.item', 'items.unit', 'creator', 'updater'
            ]);
        });
    }

    /**
     * Soft delete purchase reference invoice
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $invoice = Purchase::where('type', 'purchase_reference_invoice')
                ->findOrFail($id);

            // Check if invoice is already invoiced (prevent deletion)
            if ($invoice->status === 'invoiced') {
                throw new \Exception('Cannot delete invoiced purchase reference invoice.');
            }

            // Track who deleted the invoice
            $invoice->update(['deleted_by' => Auth::id()]);

            // Soft delete the invoice
            $invoice->delete();

            return ['message' => 'Purchase reference invoice deleted successfully.'];
        });
    }

    /**
     * Get deleted purchase reference invoices
     */
    public function getDeleted(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort field
            if (!in_array($sortBy, $this->getAllowedSortFields())) {
                $sortBy = 'deleted_at';
            }

            $query = Purchase::onlyTrashed()
                ->with([
                    'supplier:id,supplier_name_ar,supplier_name_en,supplier_number',
                    'currency:id,code,name,symbol',
                    'deleter:id,first_name,second_name,email'
                ])
                ->where('type', 'purchase_reference_invoice');

            // Apply search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching deleted purchase reference invoices: ' . $e->getMessage());
        }
    }

    /**
     * Restore deleted purchase reference invoice
     */
    public function restore($id)
    {
        return DB::transaction(function () use ($id) {
            $invoice = Purchase::onlyTrashed()
                ->where('type', 'purchase_reference_invoice')
                ->findOrFail($id);

            // Clear deleted_by field and restore
            $invoice->update(['deleted_by' => null]);
            $invoice->restore();

            return $invoice->load([
                'supplier', 'currency', 'taxRate', 'company', 'branch', 'journal',
                'items.item', 'items.unit', 'creator', 'updater'
            ]);
        });
    }

    /**
     * Get search form data for purchase reference invoices
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $companyId = $request->company_id;

            return [
                'suppliers' => Supplier::where('company_id', $companyId)
                    ->select(['id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en'])
                    ->limit(100)->get(),
                'currencies' => Currency::where('company_id', $companyId)
                    ->select(['id', 'code', 'name', 'symbol'])
                    ->get(),
                'statuses' => Purchase::STATUS_OPTIONS,
                'sortable_fields' => $this->getSortableFields(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error fetching search form data: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields for purchase reference invoices
     */
    public function getSortableFields(): array
    {
        return [
            'id' => 'ID',
            'purchase_reference_invoice_number' => 'Purchase Reference Invoice Number',
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
            'deleted_at' => 'Deleted At',
            'ledger_code' => 'Ledger Code',
            'ledger_number' => 'Ledger Number',
            'journal_number' => 'Journal Number',
            'journal_code' => 'Journal Code',
            'tax_percentage' => 'Tax Percentage',
            'tax_amount' => 'Tax Amount',
            'total_without_tax' => 'Total Without Tax',
            'discount_percentage' => 'Discount Percentage',
            'discount_amount' => 'Discount Amount',
            'cash_paid' => 'Cash Paid',
            'checks_paid' => 'Checks Paid',
            'remaining_balance' => 'Remaining Balance',
            'allowed_discount' => 'Allowed Discount',
            'total_foreign' => 'Total Foreign',
            'total_local' => 'Total Local',
            'employee_id' => 'Employee',
            'customer_id' => 'Customer',
            'branch_id' => 'Branch',
            'company_id' => 'Company',
            'user_id' => 'User',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
        ];
    }
}
