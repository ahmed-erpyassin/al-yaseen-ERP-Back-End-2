<?php

namespace Modules\Purchases\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\Http\Requests\InvoiceRequest;
use Modules\Purchases\Http\Requests\PurchaseInvoiceRequest;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Suppliers\Models\Supplier;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;

class InvoiceService
{
    public function index(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $query = Purchase::query()
                ->where('company_id', $companyId)
                ->where('type', PurchaseTypeEnum::INVOICE)
                ->with([
                    'supplier:id,first_name,second_name,supplier_number,email,mobile',
                    'currency:id,name,code,symbol',
                    'employee:id,first_name,last_name,employee_number',
                    'user:id,first_name,second_name,email',
                    'items:id,purchase_id,item_id,item_name,quantity,unit_price,total',
                    'items.item:id,name,item_number',
                    'items.warehouse:id,name,warehouse_number'
                ]);

            // Apply search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $sortBy, $sortOrder);

            return $query->paginate($perPage);
        } catch (Exception $e) {
            throw new Exception('Error fetching purchase invoices: ' . $e->getMessage());
        }
    }

    public function store(PurchaseInvoiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;
                $branchId = $request->user()->branch_id ?? $request->branch_id;

                // Generate automatic fields
                $autoFields = $this->generateAutoFields($companyId, PurchaseTypeEnum::INVOICE);

                // Get supplier details if not provided
                $supplierData = $this->getSupplierData($request->supplier_id);

                // Get currency rate (with external API integration)
                $currencyRate = $this->getCurrencyRate($request->currency_id, $request->tax_rate_id);

                // Prepare purchase data
                $purchaseData = [
                    'type' => PurchaseTypeEnum::INVOICE,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'supplier_id' => $request->supplier_id,
                    'currency_id' => $request->currency_id,
                    'employee_id' => $request->employee_id,
                    'status' => 'draft',

                    // Auto-generated fields
                    'ledger_code' => $autoFields['ledger_code'],
                    'ledger_number' => $autoFields['ledger_number'],
                    'ledger_invoice_count' => $autoFields['ledger_invoice_count'],
                    'invoice_number' => $autoFields['invoice_number'],
                    'purchase_invoice_number' => $autoFields['purchase_invoice_number'],
                    'entry_number' => $autoFields['entry_number'],
                    'date' => Carbon::now()->toDateString(),
                    'time' => Carbon::now()->toTimeString(),

                    // Supplier data
                    'supplier_number' => $supplierData['supplier_number'] ?? null,
                    'supplier_name' => $supplierData['supplier_name'] ?? null,
                    'supplier_email' => $request->supplier_email ?? $supplierData['email'],
                    'supplier_mobile' => $request->supplier_mobile ?? $supplierData['mobile'],

                    // Currency and tax data
                    'exchange_rate' => $currencyRate['exchange_rate'],
                    'currency_rate' => $currencyRate['currency_rate'],
                    'currency_rate_with_tax' => $currencyRate['currency_rate_with_tax'],
                    'tax_rate_id' => $request->tax_rate_id,
                    'is_tax_applied_to_currency' => $currencyRate['is_tax_applied'],
                ] + $request->validated();

                // Create the purchase
                $purchase = Purchase::create($purchaseData);

                // Create purchase items with auto-generated serial numbers
                if ($request->has('items') && is_array($request->items)) {
                    $this->createPurchaseItems($purchase, $request->items);
                }

                return $purchase->load(['items', 'supplier', 'currency', 'employee']);
            });
        } catch (Exception $e) {
            throw new Exception('Error creating purchase invoice: ' . $e->getMessage());
        }
    }

    /**
     * Generate automatic fields for purchase invoice
     */
    private function generateAutoFields($companyId, $type)
    {
        // Generate ledger information
        $ledgerInfo = Purchase::generateLedgerInfo($companyId, $type);

        // Generate sequential invoice number
        $invoiceNumber = $this->generateInvoiceNumber($companyId, $type, $ledgerInfo['ledger_invoice_count']);

        // Generate purchase invoice number
        $purchaseInvoiceNumber = $this->generatePurchaseInvoiceNumber($companyId);

        // Generate entry number
        $entryNumber = $this->generateEntryNumber($companyId);

        return [
            'ledger_code' => $ledgerInfo['ledger_code'],
            'ledger_number' => $ledgerInfo['ledger_number'],
            'ledger_invoice_count' => $ledgerInfo['ledger_invoice_count'],
            'invoice_number' => $invoiceNumber,
            'purchase_invoice_number' => $purchaseInvoiceNumber,
            'entry_number' => $entryNumber,
            'date' => Carbon::now()->toDateString(),
            'time' => Carbon::now()->toTimeString(),
        ];
    }

    /**
     * Generate sequential invoice number
     */
    private function generateInvoiceNumber($companyId, $type, $ledgerInvoiceCount)
    {
        // Get the total count of invoices across all ledgers for this type
        $totalInvoiceCount = Purchase::where('company_id', $companyId)
            ->where('type', $type)
            ->whereNotNull('invoice_number')
            ->count() + 1;

        return 'INV-' . str_pad($totalInvoiceCount, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate purchase invoice number
     */
    private function generatePurchaseInvoiceNumber($companyId)
    {
        $count = Purchase::where('company_id', $companyId)
            ->where('type', PurchaseTypeEnum::INVOICE)
            ->whereNotNull('purchase_invoice_number')
            ->count() + 1;

        return 'PINV-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate entry number
     */
    private function generateEntryNumber($companyId)
    {
        $count = Purchase::where('company_id', $companyId)
            ->whereNotNull('entry_number')
            ->count() + 1;

        return 'ENT-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get supplier data
     */
    private function getSupplierData($supplierId)
    {
        if (!$supplierId) {
            return [];
        }

        $supplier = Supplier::find($supplierId);
        if (!$supplier) {
            return [];
        }

        return [
            'supplier_number' => $supplier->supplier_number ?? 'SUP-' . str_pad($supplier->id, 4, '0', STR_PAD_LEFT),
            'supplier_name' => trim(($supplier->first_name ?? '') . ' ' . ($supplier->second_name ?? '')) ?: $supplier->supplier_name_en ?: $supplier->supplier_name_ar,
            'email' => $supplier->email,
            'mobile' => $supplier->mobile,
        ];
    }

    /**
     * Get currency rate with external API integration
     */
    private function getCurrencyRate($currencyId, $taxRateId = null)
    {
        $currency = Currency::find($currencyId);
        if (!$currency) {
            throw new Exception('Currency not found');
        }

        // Default exchange rate
        $exchangeRate = 1.0;

        // Try to get live exchange rate from external API
        try {
            if ($currency->code !== 'USD') { // Assuming USD is base currency
                $response = Http::timeout(5)->get("https://api.exchangerate-api.com/v4/latest/USD");
                if ($response->successful()) {
                    $rates = $response->json()['rates'] ?? [];
                    $exchangeRate = $rates[$currency->code] ?? 1.0;
                }
            }
        } catch (Exception $e) {
            // Fallback to default rate if API fails
            $exchangeRate = 1.0;
        }

        $currencyRate = $exchangeRate;
        $currencyRateWithTax = $exchangeRate;
        $isTaxApplied = false;

        // Apply tax to currency rate if tax rate is provided
        if ($taxRateId) {
            $taxRate = TaxRate::find($taxRateId);
            if ($taxRate && $taxRate->rate > 0) {
                $currencyRateWithTax = $exchangeRate * (1 + ($taxRate->rate / 100));
                $isTaxApplied = true;
            }
        }

        return [
            'exchange_rate' => $exchangeRate,
            'currency_rate' => $currencyRate,
            'currency_rate_with_tax' => $currencyRateWithTax,
            'is_tax_applied' => $isTaxApplied,
        ];
    }

    /**
     * Create purchase items with auto-generated serial numbers
     */
    private function createPurchaseItems($purchase, $items)
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
            $quantity = $itemData['quantity'];
            $unitPrice = $itemData['unit_price'] ?? $item->selling_price ?? 0;
            $discountAmount = $itemData['discount_amount'] ?? 0;
            $discountPercentage = $itemData['discount_percentage'] ?? 0;

            // Apply discount
            if ($discountPercentage > 0) {
                $discountAmount = ($unitPrice * $quantity) * ($discountPercentage / 100);
            }

            $netUnitPrice = $unitPrice - ($discountAmount / $quantity);
            $lineTotalBeforeTax = $netUnitPrice * $quantity;

            // Apply tax
            $taxRate = $itemData['tax_rate'] ?? 0;
            $taxAmount = $lineTotalBeforeTax * ($taxRate / 100);
            $lineTotalAfterTax = $lineTotalBeforeTax + $taxAmount;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'serial_number' => $index + 1,
                'item_id' => $itemData['item_id'],
                'item_number' => $item->item_number ?? $item->code,
                'item_name' => $item->name,
                'unit_id' => $unit->id ?? null,
                'unit_name' => $unit->name ?? null,
                'warehouse_id' => $itemData['warehouse_id'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'net_unit_price' => $netUnitPrice,
                'line_total_before_tax' => $lineTotalBeforeTax,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total_after_tax' => $lineTotalAfterTax,
                'total' => $lineTotalAfterTax,
                'notes' => $itemData['notes'] ?? null,
            ]);
        }
    }

    /**
     * Apply search filters to the query
     */
    private function applySearchFilters($query, $request)
    {
        // Purchase Invoice Number range search (from/to)
        if ($request->filled('purchase_invoice_number_from')) {
            $query->where('purchase_invoice_number', '>=', $request->purchase_invoice_number_from);
        }

        if ($request->filled('purchase_invoice_number_to')) {
            $query->where('purchase_invoice_number', '<=', $request->purchase_invoice_number_to);
        }

        // Single Purchase Invoice Number search
        if ($request->filled('purchase_invoice_number')) {
            $query->where('purchase_invoice_number', 'like', '%' . $request->purchase_invoice_number . '%');
        }

        // Supplier name search (comprehensive)
        if ($request->filled('supplier_name')) {
            $supplierName = $request->supplier_name;
            $query->where(function ($q) use ($supplierName) {
                $q->where('supplier_name', 'like', '%' . $supplierName . '%')
                  ->orWhere('supplier_number', 'like', '%' . $supplierName . '%')
                  ->orWhereHas('supplier', function ($supplierQuery) use ($supplierName) {
                      $supplierQuery->where('first_name', 'like', '%' . $supplierName . '%')
                                   ->orWhere('second_name', 'like', '%' . $supplierName . '%')
                                   ->orWhere('supplier_number', 'like', '%' . $supplierName . '%')
                                   ->orWhere('supplier_name_en', 'like', '%' . $supplierName . '%')
                                   ->orWhere('supplier_name_ar', 'like', '%' . $supplierName . '%');
                  });
            });
        }

        // Exact date search
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Date range search
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Amount search (from/to)
        if ($request->filled('amount_from')) {
            $query->where(function ($q) use ($request) {
                $q->where('total_amount', '>=', $request->amount_from)
                  ->orWhere('grand_total', '>=', $request->amount_from);
            });
        }

        if ($request->filled('amount_to')) {
            $query->where(function ($q) use ($request) {
                $q->where('total_amount', '<=', $request->amount_to)
                  ->orWhere('grand_total', '<=', $request->amount_to);
            });
        }

        // Single amount search
        if ($request->filled('amount')) {
            $query->where(function ($q) use ($request) {
                $q->where('total_amount', $request->amount)
                  ->orWhere('grand_total', $request->amount);
            });
        }

        // Currency search
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        // Currency code search
        if ($request->filled('currency_code')) {
            $query->whereHas('currency', function ($currencyQuery) use ($request) {
                $currencyQuery->where('code', 'like', '%' . $request->currency_code . '%');
            });
        }

        // Licensed operator search
        if ($request->filled('licensed_operator')) {
            $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
        }

        // Invoice number search (general)
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        // Entry number search
        if ($request->filled('entry_number')) {
            $query->where('entry_number', 'like', '%' . $request->entry_number . '%');
        }

        // Ledger code search
        if ($request->filled('ledger_code')) {
            $query->where('ledger_code', 'like', '%' . $request->ledger_code . '%');
        }

        // Status search
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Employee search
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Branch search
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // User search
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Tax applied search
        if ($request->filled('is_tax_applied_to_currency')) {
            $query->where('is_tax_applied_to_currency', $request->boolean('is_tax_applied_to_currency'));
        }

        // Due date search
        if ($request->filled('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        // Due date range search
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting($query, $sortBy, $sortOrder)
    {
        // Comprehensive list of all sortable fields from purchases table for purchase invoices
        $allowedSortFields = [
            // Basic Information
            'id', 'user_id', 'company_id', 'branch_id', 'currency_id', 'employee_id', 'supplier_id', 'customer_id',

            // Invoice Information
            'quotation_number', 'invoice_number', 'purchase_invoice_number', 'entry_number', 'date', 'time', 'due_date',

            // Customer Information (if applicable)
            'customer_number', 'customer_name', 'customer_email', 'customer_mobile',

            // Supplier Information
            'supplier_name', 'supplier_number', 'supplier_email', 'supplier_mobile', 'licensed_operator',

            // Ledger System
            'journal_id', 'journal_number', 'ledger_code', 'ledger_number', 'ledger_invoice_count',

            // Type and Status
            'type', 'status',

            // Financial Information
            'cash_paid', 'checks_paid', 'allowed_discount', 'discount_percentage', 'discount_amount',
            'total_without_tax', 'tax_percentage', 'tax_amount', 'remaining_balance',
            'exchange_rate', 'currency_rate', 'currency_rate_with_tax', 'tax_rate_id', 'is_tax_applied_to_currency',
            'total_foreign', 'total_local', 'total_amount', 'grand_total',

            // Additional Information
            'notes',

            // Audit Fields
            'created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
        ];

        // Validate sort order
        $sortOrder = strtolower($sortOrder);
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Apply sorting with relationship-based sorting support
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Handle relationship-based sorting
            switch ($sortBy) {
                case 'supplier_full_name':
                    $query->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                          ->orderByRaw("CONCAT(COALESCE(suppliers.first_name, ''), ' ', COALESCE(suppliers.second_name, '')) {$sortOrder}");
                    break;

                case 'currency_name':
                    $query->leftJoin('currencies', 'purchases.currency_id', '=', 'currencies.id')
                          ->orderBy('currencies.name', $sortOrder);
                    break;

                case 'currency_code':
                    $query->leftJoin('currencies', 'purchases.currency_id', '=', 'currencies.id')
                          ->orderBy('currencies.code', $sortOrder);
                    break;

                case 'employee_name':
                    $query->leftJoin('users as employees', 'purchases.employee_id', '=', 'employees.id')
                          ->orderByRaw("CONCAT(COALESCE(employees.first_name, ''), ' ', COALESCE(employees.last_name, '')) {$sortOrder}");
                    break;

                case 'user_name':
                    $query->leftJoin('users', 'purchases.user_id', '=', 'users.id')
                          ->orderByRaw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.second_name, '')) {$sortOrder}");
                    break;

                case 'branch_name':
                    $query->leftJoin('branches', 'purchases.branch_id', '=', 'branches.id')
                          ->orderBy('branches.name', $sortOrder);
                    break;

                case 'tax_rate_name':
                    $query->leftJoin('tax_rates', 'purchases.tax_rate_id', '=', 'tax_rates.id')
                          ->orderBy('tax_rates.name', $sortOrder);
                    break;

                default:
                    // Default sorting by created_at if field is not recognized
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        // Add secondary sorting for consistent results
        if ($sortBy !== 'id' && $sortBy !== 'created_at') {
            $query->orderBy('id', 'desc');
        }
    }

    /**
     * Show a specific purchase invoice with complete data from purchases table
     */
    public function show($id, Request $request)
    {
        try {
            $companyId = $request->user()->company_id;

            // Get purchase invoice with all relationships and complete field selection
            $purchase = Purchase::where('company_id', $companyId)
                ->where('type', PurchaseTypeEnum::INVOICE)
                ->with([
                    // Supplier relationship with all fields
                    'supplier:id,supplier_number,first_name,second_name,third_name,fourth_name,supplier_name_en,supplier_name_ar,email,mobile,phone,address,city,country,postal_code,tax_number,commercial_register,website,notes,status,created_at,updated_at',

                    // Currency relationship with all fields
                    'currency:id,name,code,symbol,exchange_rate,is_default,status,created_at,updated_at',

                    // Employee relationship with all fields
                    'employee:id,employee_number,first_name,last_name,middle_name,email,mobile,phone,position,department,salary,hire_date,status,created_at,updated_at',

                    // User relationship with all fields
                    'user:id,first_name,second_name,third_name,fourth_name,email,mobile,phone,status,email_verified_at,created_at,updated_at',

                    // Branch relationship with all fields
                    'branch:id,name,code,address,city,country,phone,email,manager_name,status,created_at,updated_at',

                    // Tax Rate relationship with all fields
                    'taxRate:id,name,code,rate,type,description,is_default,status,created_at,updated_at',

                    // Customer relationship (if applicable) with all fields
                    'customer:id,customer_number,first_name,second_name,third_name,fourth_name,customer_name_en,customer_name_ar,email,mobile,phone,address,city,country,postal_code,tax_number,commercial_register,website,notes,status,created_at,updated_at',

                    // Purchase Items with complete details
                    'items:id,purchase_id,serial_number,item_id,item_name,item_number,unit_id,unit_name,warehouse_id,quantity,unit_price,discount_percentage,discount_amount,net_unit_price,line_total_before_tax,tax_rate,tax_amount,line_total_after_tax,total,notes,created_at,updated_at',
                    'items.item:id,name,item_number,code,description,barcode,category_id,unit_id,selling_price,purchase_price,minimum_stock,maximum_stock,reorder_level,status,created_at,updated_at',
                    'items.unit:id,name,symbol,code,description,conversion_factor,is_base_unit,status,created_at,updated_at',
                    'items.warehouse:id,name,warehouse_number,code,address,city,country,phone,email,manager_name,capacity,status,created_at,updated_at',

                    // Journal relationship (if applicable)
                    'journal:id,journal_number,name,description,type,status,created_at,updated_at',

                    // Created by, Updated by, Deleted by users
                    'createdBy:id,first_name,second_name,email',
                    'updatedBy:id,first_name,second_name,email',
                    'deletedBy:id,first_name,second_name,email'
                ])
                ->findOrFail($id);

            // Calculate comprehensive statistics
            $statistics = [
                // Item Statistics
                'total_items' => $purchase->items->count(),
                'total_quantity' => $purchase->items->sum('quantity'),
                'average_quantity' => $purchase->items->avg('quantity'),
                'highest_quantity' => $purchase->items->max('quantity'),
                'lowest_quantity' => $purchase->items->min('quantity'),

                // Price Statistics
                'average_unit_price' => $purchase->items->avg('unit_price'),
                'highest_unit_price' => $purchase->items->max('unit_price'),
                'lowest_unit_price' => $purchase->items->min('unit_price'),
                'total_unit_prices' => $purchase->items->sum('unit_price'),

                // Discount Statistics
                'total_discount_amount' => $purchase->items->sum('discount_amount'),
                'average_discount_amount' => $purchase->items->avg('discount_amount'),
                'highest_discount_amount' => $purchase->items->max('discount_amount'),
                'items_with_discount' => $purchase->items->where('discount_amount', '>', 0)->count(),
                'items_without_discount' => $purchase->items->where('discount_amount', '<=', 0)->count(),
                'total_discount_percentage' => $purchase->items->sum('discount_percentage'),
                'average_discount_percentage' => $purchase->items->avg('discount_percentage'),

                // Tax Statistics
                'total_tax_amount' => $purchase->items->sum('tax_amount'),
                'average_tax_amount' => $purchase->items->avg('tax_amount'),
                'highest_tax_amount' => $purchase->items->max('tax_amount'),
                'items_with_tax' => $purchase->items->where('tax_amount', '>', 0)->count(),
                'items_without_tax' => $purchase->items->where('tax_amount', '<=', 0)->count(),
                'total_tax_rate' => $purchase->items->sum('tax_rate'),
                'average_tax_rate' => $purchase->items->avg('tax_rate'),

                // Total Statistics
                'total_before_tax' => $purchase->items->sum('line_total_before_tax'),
                'total_after_tax' => $purchase->items->sum('line_total_after_tax'),
                'net_total' => $purchase->items->sum('total'),

                // Warehouse Statistics
                'unique_warehouses' => $purchase->items->pluck('warehouse_id')->filter()->unique()->count(),
                'items_without_warehouse' => $purchase->items->whereNull('warehouse_id')->count(),

                // Unit Statistics
                'unique_units' => $purchase->items->pluck('unit_id')->filter()->unique()->count(),
                'items_without_unit' => $purchase->items->whereNull('unit_id')->count(),
            ];

            // Format comprehensive data for display
            $formattedData = [
                // Invoice Display Information
                'invoice_display_number' => $purchase->purchase_invoice_number ?? $purchase->invoice_number ?? 'N/A',
                'entry_display_number' => $purchase->entry_number ?? 'N/A',
                'ledger_display' => $purchase->ledger_code ? $purchase->ledger_code . ' (' . $purchase->ledger_number . ')' : 'N/A',
                'journal_display' => $purchase->journal ? $purchase->journal->journal_number . ' - ' . $purchase->journal->name : 'N/A',

                // Date and Time Formatting
                'formatted_date' => $purchase->date ? Carbon::parse($purchase->date)->format('d/m/Y') : 'N/A',
                'formatted_time' => $purchase->time ? Carbon::parse($purchase->time)->format('H:i:s') : 'N/A',
                'formatted_due_date' => $purchase->due_date ? Carbon::parse($purchase->due_date)->format('d/m/Y') : 'N/A',
                'formatted_created_at' => $purchase->created_at ? $purchase->created_at->format('d/m/Y H:i:s') : 'N/A',
                'formatted_updated_at' => $purchase->updated_at ? $purchase->updated_at->format('d/m/Y H:i:s') : 'N/A',
                'formatted_deleted_at' => $purchase->deleted_at ? $purchase->deleted_at->format('d/m/Y H:i:s') : null,

                // Supplier Information
                'supplier_full_name' => $purchase->supplier ?
                    trim(($purchase->supplier->first_name ?? '') . ' ' . ($purchase->supplier->second_name ?? '') . ' ' . ($purchase->supplier->third_name ?? '') . ' ' . ($purchase->supplier->fourth_name ?? ''))
                    : ($purchase->supplier_name ?? 'N/A'),
                'supplier_display_name' => $purchase->supplier ?
                    ($purchase->supplier->supplier_name_en ?: $purchase->supplier->supplier_name_ar ?: trim($purchase->supplier->first_name . ' ' . $purchase->supplier->second_name))
                    : ($purchase->supplier_name ?? 'N/A'),
                'supplier_contact' => $purchase->supplier ?
                    ($purchase->supplier->email ? $purchase->supplier->email . ' | ' : '') . ($purchase->supplier->mobile ?: $purchase->supplier->phone ?: 'No contact')
                    : (($purchase->supplier_email ? $purchase->supplier_email . ' | ' : '') . ($purchase->supplier_mobile ?: 'No contact')),

                // Currency Information
                'currency_display' => $purchase->currency ?
                    $purchase->currency->name . ' (' . $purchase->currency->code . ' - ' . $purchase->currency->symbol . ')'
                    : 'N/A',
                'exchange_rate_display' => number_format($purchase->exchange_rate ?? 1, 4),
                'currency_rate_display' => number_format($purchase->currency_rate ?? 1, 4),
                'currency_rate_with_tax_display' => number_format($purchase->currency_rate_with_tax ?? 1, 4),

                // Employee Information
                'employee_full_name' => $purchase->employee ?
                    trim(($purchase->employee->first_name ?? '') . ' ' . ($purchase->employee->middle_name ?? '') . ' ' . ($purchase->employee->last_name ?? ''))
                    : 'N/A',
                'employee_details' => $purchase->employee ?
                    ($purchase->employee->employee_number ? 'ID: ' . $purchase->employee->employee_number . ' | ' : '') .
                    ($purchase->employee->position ? 'Position: ' . $purchase->employee->position . ' | ' : '') .
                    ($purchase->employee->department ? 'Dept: ' . $purchase->employee->department : '')
                    : 'N/A',

                // User Information
                'user_full_name' => $purchase->user ?
                    trim(($purchase->user->first_name ?? '') . ' ' . ($purchase->user->second_name ?? '') . ' ' . ($purchase->user->third_name ?? '') . ' ' . ($purchase->user->fourth_name ?? ''))
                    : 'N/A',
                'user_contact' => $purchase->user ?
                    ($purchase->user->email ? $purchase->user->email . ' | ' : '') . ($purchase->user->mobile ?: $purchase->user->phone ?: 'No contact')
                    : 'N/A',

                // Branch Information
                'branch_display' => $purchase->branch ?
                    $purchase->branch->name . ($purchase->branch->code ? ' (' . $purchase->branch->code . ')' : '')
                    : 'N/A',
                'branch_location' => $purchase->branch ?
                    ($purchase->branch->address ? $purchase->branch->address . ', ' : '') .
                    ($purchase->branch->city ? $purchase->branch->city . ', ' : '') .
                    ($purchase->branch->country ?: '')
                    : 'N/A',

                // Financial Information
                'formatted_total_without_tax' => number_format($purchase->total_without_tax ?? 0, 2),
                'formatted_discount_amount' => number_format($purchase->discount_amount ?? 0, 2),
                'formatted_tax_amount' => number_format($purchase->tax_amount ?? 0, 2),
                'formatted_total_amount' => number_format($purchase->total_amount ?? 0, 2),
                'formatted_grand_total' => number_format($purchase->grand_total ?? 0, 2),
                'formatted_total_foreign' => number_format($purchase->total_foreign ?? 0, 2),
                'formatted_total_local' => number_format($purchase->total_local ?? 0, 2),
                'formatted_cash_paid' => number_format($purchase->cash_paid ?? 0, 2),
                'formatted_checks_paid' => number_format($purchase->checks_paid ?? 0, 2),
                'formatted_remaining_balance' => number_format($purchase->remaining_balance ?? 0, 2),

                // Tax Information
                'tax_display' => $purchase->taxRate ?
                    $purchase->taxRate->name . ' (' . $purchase->taxRate->rate . '% - ' . $purchase->taxRate->type . ')'
                    : ($purchase->tax_percentage ? $purchase->tax_percentage . '%' : 'No Tax'),
                'is_tax_applied_display' => $purchase->is_tax_applied_to_currency ? 'Yes' : 'No',

                // Status Information
                'status_display' => ucfirst($purchase->status ?? 'draft'),
                'type_display' => ucfirst(str_replace('_', ' ', $purchase->type ?? 'invoice')),

                // Audit Information
                'created_by_name' => $purchase->createdBy ?
                    trim($purchase->createdBy->first_name . ' ' . $purchase->createdBy->second_name)
                    : 'System',
                'updated_by_name' => $purchase->updatedBy ?
                    trim($purchase->updatedBy->first_name . ' ' . $purchase->updatedBy->second_name)
                    : null,
                'deleted_by_name' => $purchase->deletedBy ?
                    trim($purchase->deletedBy->first_name . ' ' . $purchase->deletedBy->second_name)
                    : null,
            ];

            return [
                'purchase' => $purchase,
                'statistics' => $statistics,
                'formatted_data' => $formattedData,
            ];
        } catch (Exception $e) {
            throw new Exception('Error retrieving purchase invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update a purchase invoice with complete field modification support
     */
    public function update($id, PurchaseInvoiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($id, $request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;
                $branchId = $request->user()->branch_id ?? $request->branch_id;

                // Find the purchase invoice
                $purchase = Purchase::where('company_id', $companyId)
                    ->where('type', PurchaseTypeEnum::INVOICE)
                    ->findOrFail($id);

                // Store original values for comparison
                $originalSupplierId = $purchase->supplier_id;
                $originalCurrencyId = $purchase->currency_id;
                $originalTaxRateId = $purchase->tax_rate_id;

                // Get supplier details if supplier changed
                $supplierData = [];
                if ($request->supplier_id && $request->supplier_id !== $originalSupplierId) {
                    $supplierData = $this->getSupplierData($request->supplier_id);
                }

                // Get currency rate if currency or tax rate changed
                $currencyData = [];
                if (($request->currency_id && $request->currency_id !== $originalCurrencyId) ||
                    ($request->tax_rate_id !== $originalTaxRateId)) {
                    $currencyData = $this->getCurrencyRate(
                        $request->currency_id ?? $originalCurrencyId,
                        $request->tax_rate_id
                    );
                }

                // Prepare comprehensive update data with all possible fields
                $updateData = [
                    // Update timestamp and user
                    'updated_by' => $userId,

                    // Basic Information (only if provided)
                    'branch_id' => $branchId,
                    'supplier_id' => $request->supplier_id ?? $purchase->supplier_id,
                    'currency_id' => $request->currency_id ?? $purchase->currency_id,
                    'employee_id' => $request->employee_id ?? $purchase->employee_id,
                    'customer_id' => $request->customer_id ?? $purchase->customer_id,

                    // Invoice Information
                    'quotation_number' => $request->quotation_number ?? $purchase->quotation_number,
                    'due_date' => $request->due_date ?? $purchase->due_date,

                    // Customer Information (if applicable)
                    'customer_number' => $request->customer_number ?? $purchase->customer_number,
                    'customer_name' => $request->customer_name ?? $purchase->customer_name,
                    'customer_email' => $request->customer_email ?? $purchase->customer_email,
                    'customer_mobile' => $request->customer_mobile ?? $purchase->customer_mobile,

                    // Supplier Information
                    'licensed_operator' => $request->licensed_operator ?? $purchase->licensed_operator,

                    // Ledger System (preserve existing values unless specifically updated)
                    'journal_id' => $request->journal_id ?? $purchase->journal_id,
                    'journal_number' => $request->journal_number ?? $purchase->journal_number,

                    // Status
                    'status' => $request->status ?? $purchase->status,

                    // Financial Information
                    'cash_paid' => $request->cash_paid ?? $purchase->cash_paid,
                    'checks_paid' => $request->checks_paid ?? $purchase->checks_paid,
                    'allowed_discount' => $request->allowed_discount ?? $purchase->allowed_discount,
                    'discount_percentage' => $request->discount_percentage ?? $purchase->discount_percentage,
                    'discount_amount' => $request->discount_amount ?? $purchase->discount_amount,
                    'total_without_tax' => $request->total_without_tax ?? $purchase->total_without_tax,
                    'tax_percentage' => $request->tax_percentage ?? $purchase->tax_percentage,
                    'tax_amount' => $request->tax_amount ?? $purchase->tax_amount,
                    'remaining_balance' => $request->remaining_balance ?? $purchase->remaining_balance,
                    'tax_rate_id' => $request->tax_rate_id ?? $purchase->tax_rate_id,
                    'total_foreign' => $request->total_foreign ?? $purchase->total_foreign,
                    'total_local' => $request->total_local ?? $purchase->total_local,
                    'total_amount' => $request->total_amount ?? $purchase->total_amount,
                    'grand_total' => $request->grand_total ?? $purchase->grand_total,

                    // Additional Information
                    'notes' => $request->notes ?? $purchase->notes,
                ];

                // Add supplier data if supplier changed
                if (!empty($supplierData)) {
                    $updateData = array_merge($updateData, [
                        'supplier_number' => $supplierData['supplier_number'] ?? $purchase->supplier_number,
                        'supplier_name' => $supplierData['supplier_name'] ?? $purchase->supplier_name,
                        'supplier_email' => $request->supplier_email ?? $supplierData['email'] ?? $purchase->supplier_email,
                        'supplier_mobile' => $request->supplier_mobile ?? $supplierData['mobile'] ?? $purchase->supplier_mobile,
                    ]);
                } else {
                    // Update supplier contact info even if supplier didn't change
                    $updateData['supplier_email'] = $request->supplier_email ?? $purchase->supplier_email;
                    $updateData['supplier_mobile'] = $request->supplier_mobile ?? $purchase->supplier_mobile;
                }

                // Add currency data if currency or tax changed
                if (!empty($currencyData)) {
                    $updateData = array_merge($updateData, [
                        'exchange_rate' => $currencyData['exchange_rate'],
                        'currency_rate' => $currencyData['currency_rate'],
                        'currency_rate_with_tax' => $currencyData['currency_rate_with_tax'],
                        'is_tax_applied_to_currency' => $currencyData['is_tax_applied'],
                    ]);
                } else {
                    // Preserve existing currency rates if not changed
                    $updateData['exchange_rate'] = $request->exchange_rate ?? $purchase->exchange_rate;
                    $updateData['currency_rate'] = $request->currency_rate ?? $purchase->currency_rate;
                    $updateData['currency_rate_with_tax'] = $request->currency_rate_with_tax ?? $purchase->currency_rate_with_tax;
                    $updateData['is_tax_applied_to_currency'] = $request->is_tax_applied_to_currency ?? $purchase->is_tax_applied_to_currency;
                }

                // Remove null values to avoid overwriting existing data with nulls
                $updateData = array_filter($updateData, function ($value) {
                    return $value !== null;
                });

                // Update the purchase with comprehensive data
                $purchase->update($updateData);

                // Handle items update if provided
                if ($request->has('items') && is_array($request->items)) {
                    // Store existing items for comparison
                    $existingItems = $purchase->items()->get();

                    // Delete existing items (soft delete to maintain audit trail)
                    $purchase->items()->delete();

                    // Create new items with updated data
                    $this->createPurchaseItems($purchase, $request->items);

                    // Log the items change for audit purposes
                    \Log::info('Purchase Invoice Items Updated', [
                        'purchase_id' => $purchase->id,
                        'old_items_count' => $existingItems->count(),
                        'new_items_count' => count($request->items),
                        'updated_by' => $userId,
                    ]);
                }

                // Recalculate totals if items were updated
                if ($request->has('items')) {
                    $this->recalculatePurchaseTotals($purchase);
                }

                // Load all relationships for complete response
                return $purchase->load([
                    'items', 'supplier', 'currency', 'employee', 'user', 'branch',
                    'taxRate', 'customer', 'journal', 'createdBy', 'updatedBy'
                ]);
            });
        } catch (Exception $e) {
            throw new Exception('Error updating purchase invoice: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate purchase totals based on items
     */
    private function recalculatePurchaseTotals($purchase)
    {
        $items = $purchase->items()->get();

        $totalWithoutTax = $items->sum('line_total_before_tax');
        $totalTaxAmount = $items->sum('tax_amount');
        $totalDiscountAmount = $items->sum('discount_amount');
        $grandTotal = $items->sum('total');

        $purchase->update([
            'total_without_tax' => $totalWithoutTax,
            'tax_amount' => $totalTaxAmount,
            'discount_amount' => $totalDiscountAmount,
            'total_amount' => $totalWithoutTax + $totalTaxAmount,
            'grand_total' => $grandTotal,
            'remaining_balance' => $grandTotal - ($purchase->cash_paid ?? 0) - ($purchase->checks_paid ?? 0),
        ]);
    }

    /**
     * Delete a purchase invoice (soft delete)
     */
    public function destroy($id, Request $request)
    {
        try {
            return DB::transaction(function () use ($id, $request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;

                $purchase = Purchase::where('company_id', $companyId)
                    ->where('type', PurchaseTypeEnum::INVOICE)
                    ->findOrFail($id);

                // Mark as deleted by user
                $purchase->update([
                    'deleted_by' => $userId,
                ]);

                // Soft delete the purchase
                $purchase->delete();

                // Soft delete associated items
                $purchase->items()->delete();

                return [
                    'id' => $purchase->id,
                    'invoice_number' => $purchase->invoice_number,
                    'purchase_invoice_number' => $purchase->purchase_invoice_number,
                    'deleted_at' => $purchase->deleted_at,
                    'deleted_by' => $userId,
                ];
            });
        } catch (Exception $e) {
            throw new Exception('Error deleting purchase invoice: ' . $e->getMessage());
        }
    }
}
