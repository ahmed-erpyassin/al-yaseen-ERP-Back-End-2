<?php

namespace Modules\Purchases\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Purchases\Http\Requests\PurchaseReferenceInvoiceRequest;
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
                'creator:id,name',
                'company:id,name'
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
                'company:id,name',
                'branch:id,name',
                'journal:id,name,code',
                'items.item:id,item_number,item_name_ar,item_name_en,first_selling_price',
                'items.unit:id,unit_name_ar,unit_name_en',
                'creator:id,name,email',
                'updater:id,name,email',
                'deleter:id,name,email'
            ])
            ->where('type', 'purchase_reference_invoice')
            ->findOrFail($id);

            return $invoice;
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
                $userId = $request->user()->id;
                $companyId = $request->user()->company_id;

                // Generate purchase reference invoice number
                $invoiceNumber = Purchase::generatePurchaseReferenceInvoiceNumber();
                
                // Generate ledger information
                $ledgerInfo = Purchase::generateLedgerCode($companyId);
                
                // Get live exchange rate
                $exchangeRate = $this->getLiveExchangeRate($request->currency_id);
                
                // Calculate currency rate with tax if applicable
                $currencyRateWithTax = $exchangeRate;
                if ($request->filled('is_tax_applied_to_currency_rate') && $request->is_tax_applied_to_currency_rate) {
                    $taxRate = $request->filled('tax_rate_id') ? 
                        TaxRate::find($request->tax_rate_id)?->rate ?? 0 : 
                        ($request->tax_percentage ?? 0);
                    $currencyRateWithTax = $exchangeRate * (1 + ($taxRate / 100));
                }

                // Prepare invoice data
                $invoiceData = array_merge($request->validated(), [
                    'type' => 'purchase_reference_invoice',
                    'purchase_reference_invoice_number' => $invoiceNumber,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                    'currency_rate' => $exchangeRate,
                    'exchange_rate' => $exchangeRate,
                    'currency_rate_with_tax' => $currencyRateWithTax,
                    'affects_inventory' => true, // Purchase reference invoices affect inventory
                    'created_by' => $userId,
                    'company_id' => $companyId,
                ] + $ledgerInfo);

                // Create the purchase reference invoice
                $invoice = Purchase::create($invoiceData);

                // Create invoice items
                if ($request->has('items') && is_array($request->items)) {
                    $this->createInvoiceItems($invoice, $request->items);
                }

                // Calculate totals
                $this->calculateInvoiceTotals($invoice);

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
     * Create invoice items
     */
    private function createInvoiceItems(Purchase $invoice, array $items)
    {
        $serialNumber = 1;
        
        foreach ($items as $itemData) {
            // Get item details
            $item = Item::find($itemData['item_id']);
            $unit = $item ? Unit::find($item->unit_id) : null;
            
            // Calculate total
            $quantity = (float) $itemData['quantity'];
            $unitPrice = (float) ($itemData['unit_price'] ?? $item?->first_selling_price ?? 0);
            $total = $quantity * $unitPrice;

            PurchaseItem::create([
                'purchase_id' => $invoice->id,
                'serial_number' => $serialNumber++,
                'item_id' => $itemData['item_id'],
                'item_number' => $item?->item_number,
                'item_name' => $item?->item_name_ar ?? $item?->item_name_en,
                'unit_id' => $item?->unit_id,
                'unit_name' => $unit?->unit_name_ar ?? $unit?->unit_name_en,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'first_selling_price' => $item?->first_selling_price ?? $unitPrice,
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
            $companyId = $request->user()->company_id;
            $search = $request->get('search', '');

            $query = Supplier::where('company_id', $companyId)
                ->select(['id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en', 'email', 'mobile']);

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
            $companyId = $request->user()->company_id;
            $search = $request->get('search', '');

            $query = Item::where('company_id', $companyId)
                ->with('unit:id,unit_name_ar,unit_name_en')
                ->select(['id', 'item_number', 'item_name_ar', 'item_name_en', 'first_selling_price', 'unit_id']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('item_name_ar', 'like', '%' . $search . '%')
                      ->orWhere('item_name_en', 'like', '%' . $search . '%')
                      ->orWhere('item_number', 'like', '%' . $search . '%');
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
            $companyId = $request->user()->company_id;

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
            $companyId = $request->user()->company_id;

            return TaxRate::where('company_id', $companyId)
                ->where('is_active', true)
                ->select(['id', 'name', 'rate'])
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
                    'deleter:id,name,email'
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
            $companyId = $request->user()->company_id;

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
