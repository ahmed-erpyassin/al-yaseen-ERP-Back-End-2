<?php

namespace Modules\Purchases\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

                $validatedData = $request->validated();
                $items = $validatedData['items'] ?? [];
                unset($validatedData['items']); // Remove items from main data

                $data = [
                    'type'       => PurchaseTypeEnum::INVOICE,
                    'company_id' => $companyId,
                    'user_id'    => $userId,
                    'status'     => 'draft',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ] + $validatedData;

                $invoice = Purchase::create($data);

                // Create invoice items if provided
                if (!empty($items)) {
                    $this->createInvoiceItems($invoice, $items);
                }

                return $invoice->load(['items', 'supplier', 'customer', 'currency', 'creator']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice with all related data
     */
    public function show($id)
    {
        try {
            $invoice = Purchase::with([
                'items.item',
                'items.unit',
                'supplier',
                'customer',
                'currency',
                'branch',
                'creator',
                'updater'
            ])
            ->where('type', PurchaseTypeEnum::INVOICE)
            ->findOrFail($id);

            return $invoice;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified invoice
     */
    public function update($request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $invoice = Purchase::where('type', PurchaseTypeEnum::INVOICE)
                    ->findOrFail($id);

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

                $validatedData = $request->validated();
                $items = $validatedData['items'] ?? [];
                unset($validatedData['items']); // Remove items from main data

                // Add updated_by field and company_id
                $validatedData['updated_by'] = $userId;
                $validatedData['company_id'] = $companyId;

                // Update the invoice
                $invoice->update($validatedData);

                // Update invoice items if provided
                if (!empty($items)) {
                    // Delete existing items
                    $invoice->items()->delete();

                    // Create new items
                    $this->createInvoiceItems($invoice, $items);
                }

                return $invoice->load(['items', 'supplier', 'customer', 'currency', 'creator', 'updater']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified invoice (soft delete)
     */
    public function destroy($id)
    {
        try {
            $invoice = Purchase::where('type', PurchaseTypeEnum::INVOICE)
                ->findOrFail($id);

            $userId = Auth::id();

            if (!$userId) {
                // Fallback to first user if no authenticated user (for testing/seeding)
                $firstUser = \Modules\Users\Models\User::first();
                if (!$firstUser) {
                    throw new \Exception('No users found in the system');
                }
                $userId = $firstUser->id;
            }

            // Update the updated_by field before soft deleting
            $invoice->update(['updated_by' => $userId]);

            // Soft delete the invoice
            $invoice->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting invoice: ' . $e->getMessage());
        }
    }

    /**
     * Create invoice items
     */
    private function createInvoiceItems($invoice, $items)
    {
        foreach ($items as $index => $itemData) {
            $item = [
                'purchase_id' => $invoice->id,
                'serial_number' => $index + 1,
                'item_id' => $itemData['item_id'] ?? null,
                'account_id' => $itemData['account_id'] ?? null,
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

            \Modules\Purchases\Models\PurchaseItem::create($item);
        }
    }
}
