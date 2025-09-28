<?php

namespace Modules\Purchases\app\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\Http\Requests\IncomingOfferRequest;

class IncomingOfferService
{

    public function index(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 15);

            // Validate sort fields to prevent SQL injection
            $allowedSortFields = [
                'id', 'quotation_number', 'invoice_number', 'date', 'time', 'due_date',
                'customer_number', 'customer_name', 'customer_email', 'customer_mobile',
                'supplier_name', 'licensed_operator', 'ledger_code', 'ledger_number',
                'status', 'cash_paid', 'checks_paid', 'allowed_discount', 'discount_percentage',
                'discount_amount', 'total_without_tax', 'tax_percentage', 'tax_amount',
                'grand_total', 'remaining_balance', 'exchange_rate', 'currency_rate',
                'total_amount', 'created_at', 'updated_at'
            ];

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            $query = Purchase::query()
                ->with([
                    'user',
                    'company',
                    'branch',
                    'currency',
                    'supplier',
                    'customer',
                    'taxRate',
                    'items.item',
                    'items.unit',
                    'creator',
                    'updater'
                ])
                ->where('company_id', $companyId)
                ->where('type', PurchaseTypeEnum::QUOTATION);

            // Apply basic search filters (for backward compatibility)
            $customerSearch = $request->get('customer_search', null);
            $supplierSearch = $request->get('supplier_search', null);

            if ($customerSearch) {
                $query->where(function ($q) use ($customerSearch) {
                    $q->where('customer_name', 'like', "%{$customerSearch}%")
                      ->orWhere('customer_number', 'like', "%{$customerSearch}%")
                      ->orWhere('customer_email', 'like', "%{$customerSearch}%");
                });
            }

            if ($supplierSearch) {
                $query->where(function ($q) use ($supplierSearch) {
                    $q->where('supplier_name', 'like', "%{$supplierSearch}%")
                      ->orWhereHas('supplier', function ($sq) use ($supplierSearch) {
                          $sq->where('supplier_name_ar', 'like', "%{$supplierSearch}%")
                            ->orWhere('supplier_name_en', 'like', "%{$supplierSearch}%");
                      });
                });
            }

            $query->orderBy($sortBy, $sortOrder);

            if ($request->get('paginate', true)) {
                return $query->paginate($perPage);
            } else {
                return $query->get();
            }
        } catch (\Exception $e) {
            throw new \Exception('Error fetching incoming quotations: ' . $e->getMessage());
        }
    }

    public function store(IncomingOfferRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id ?? 101;
                $userId = $request->user()->id;
                $validatedData = $request->validated();

                // Generate sequential numbers
                $quotationNumber = $validatedData['quotation_number'] ?? Purchase::generateQuotationNumber();
                $invoiceNumber = $validatedData['invoice_number'] ?? Purchase::generateInvoiceNumber();

                // Generate ledger information
                $ledgerInfo = Purchase::generateLedgerCode($companyId);

                // Get currency rate (with external API integration)
                $currencyRate = $this->getCurrencyRate($validatedData['currency_id'], $validatedData['tax_rate_id'] ?? null);

                // Prepare purchase data
                $purchaseData = [
                    'type' => PurchaseTypeEnum::QUOTATION,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'status' => 'draft',
                    'quotation_number' => $quotationNumber,
                    'invoice_number' => $invoiceNumber,
                    'date' => $validatedData['date'] ?? now()->toDateString(),
                    'time' => $validatedData['time'] ?? now()->toTimeString(),
                    'ledger_code' => $ledgerInfo['ledger_code'],
                    'ledger_number' => $ledgerInfo['ledger_number'],
                    'ledger_invoice_count' => $ledgerInfo['ledger_invoice_count'],
                    'currency_rate' => $currencyRate['rate'],
                    'currency_rate_with_tax' => $currencyRate['rate_with_tax'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ] + $validatedData;

                // Remove items from main data
                $items = $purchaseData['items'];
                unset($purchaseData['items']);

                // Create purchase
                $purchase = Purchase::create($purchaseData);

                // Create purchase items
                $this->createPurchaseItems($purchase, $items);

                // Calculate totals
                $this->calculateTotals($purchase);

                return $purchase->load([
                    'user',
                    'company',
                    'branch',
                    'currency',
                    'supplier',
                    'customer',
                    'taxRate',
                    'items.item',
                    'items.unit',
                    'creator',
                    'updater'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error creating incoming quotation: ' . $e->getMessage());
        }
    }

    /**
     * Get currency rate from external API with tax consideration
     */
    public function getCurrencyRate($currencyId, $taxRateId = null): array
    {
        try {
            // Get currency information
            $currency = \Modules\FinancialAccounts\Models\Currency::find($currencyId);

            if (!$currency) {
                throw new \Exception('Currency not found');
            }

            // For demonstration, using a mock API call
            // In production, integrate with real currency API like exchangerate-api.com
            $rate = $this->fetchCurrencyRateFromAPI($currency->code);

            $rateWithTax = $rate;

            // Apply tax if specified
            if ($taxRateId) {
                $taxRate = \Modules\FinancialAccounts\Models\TaxRate::find($taxRateId);
                if ($taxRate && $taxRate->rate > 0) {
                    $rateWithTax = $rate * (1 + ($taxRate->rate / 100));
                }
            }

            return [
                'rate' => $rate,
                'rate_with_tax' => $rateWithTax,
                'currency_code' => $currency->code
            ];
        } catch (\Exception $e) {
            // Fallback to default rate if API fails
            return [
                'rate' => 1.0,
                'rate_with_tax' => 1.0,
                'currency_code' => 'USD'
            ];
        }
    }

    /**
     * Fetch currency rate from external API
     */
    private function fetchCurrencyRateFromAPI($currencyCode): float
    {
        try {
            // Example using exchangerate-api.com (free tier)
            $response = Http::timeout(5)->get("https://api.exchangerate-api.com/v4/latest/{$currencyCode}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['rates']['USD'] ?? 1.0;
            }

            return 1.0;
        } catch (\Exception $e) {
            // Return default rate if API call fails
            return 1.0;
        }
    }

    /**
     * Create purchase items with calculations
     */
    private function createPurchaseItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $index => $itemData) {
            // Get item information
            $item = \Modules\Inventory\Models\Item::find($itemData['item_id']);

            if (!$item) {
                continue;
            }

            // Prepare item data
            $purchaseItemData = [
                'purchase_id' => $purchase->id,
                'serial_number' => $index + 1,
                'item_id' => $itemData['item_id'],
                'item_number' => $item->item_number ?? $itemData['item_number'] ?? null,
                'item_name' => $item->name ?? $itemData['item_name'] ?? null,
                'unit_id' => $item->unit_id ?? $itemData['unit_id'] ?? null,
                'unit_name' => $item->unit->name ?? $itemData['unit_name'] ?? null,
                'description' => $itemData['description'] ?? null,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'] ?? $item->first_sale_price ?? 0,
                'discount_rate' => $itemData['discount_rate'] ?? 0,
                'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                'discount_amount' => $itemData['discount_amount'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'notes' => $itemData['notes'] ?? null,
            ];

            PurchaseItem::create($purchaseItemData);
        }
    }

    /**
     * Calculate purchase totals
     */
    private function calculateTotals(Purchase $purchase): void
    {
        $items = $purchase->items;

        $subtotal = $items->sum('line_total_before_tax');
        $totalDiscount = $items->sum('discount_amount');
        $totalTax = $items->sum('tax_amount');
        $grandTotal = $items->sum('line_total_after_tax');

        // Apply purchase-level discount if any
        if ($purchase->discount_percentage > 0) {
            $purchaseDiscount = ($subtotal * $purchase->discount_percentage) / 100;
            $subtotal -= $purchaseDiscount;
            $totalDiscount += $purchaseDiscount;
        } elseif ($purchase->discount_amount > 0) {
            $subtotal -= $purchase->discount_amount;
            $totalDiscount += $purchase->discount_amount;
        }

        // Recalculate tax on discounted amount if needed
        if ($purchase->tax_percentage > 0) {
            $purchaseTax = ($subtotal * $purchase->tax_percentage) / 100;
            $totalTax = $purchaseTax;
            $grandTotal = $subtotal + $totalTax;
        }

        // Update purchase totals
        $purchase->update([
            'total_without_tax' => $subtotal,
            'tax_amount' => $totalTax,
            'total_amount' => $grandTotal,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * Get form data for creating incoming quotations
     */
    public function getFormData(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;

            return [
                // Sequential numbers
                'next_quotation_number' => Purchase::generateQuotationNumber(),
                'next_invoice_number' => Purchase::generateInvoiceNumber(),
                'next_ledger_info' => Purchase::generateLedgerCode($companyId),

                // Dropdown data
                'suppliers' => \Modules\Suppliers\Models\Supplier::forCompany($companyId)->active()
                    ->select('id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en')
                    ->get()
                    ->map(function ($supplier) {
                        return [
                            'id' => $supplier->id,
                            'supplier_number' => $supplier->supplier_number,
                            'supplier_name_ar' => $supplier->supplier_name_ar,
                            'supplier_name_en' => $supplier->supplier_name_en,
                            'display_name' => $supplier->supplier_number . ' - ' . ($supplier->supplier_name_ar ?? $supplier->supplier_name_en)
                        ];
                    }),

                'customers' => \Modules\Customers\Models\Customer::forCompany($companyId)->active()
                    ->select('id', 'customer_number', 'customer_name_ar', 'customer_name_en', 'email', 'mobile')
                    ->get()
                    ->map(function ($customer) {
                        return [
                            'id' => $customer->id,
                            'customer_number' => $customer->customer_number,
                            'customer_name_ar' => $customer->customer_name_ar,
                            'customer_name_en' => $customer->customer_name_en,
                            'email' => $customer->email,
                            'mobile' => $customer->mobile,
                            'display_name' => $customer->customer_number . ' - ' . ($customer->customer_name_ar ?? $customer->customer_name_en)
                        ];
                    }),

                'currencies' => \Modules\FinancialAccounts\Models\Currency::where('company_id', $companyId)
                    ->select('id', 'code', 'name', 'symbol')
                    ->get()
                    ->map(function ($currency) {
                        return [
                            'id' => $currency->id,
                            'code' => $currency->code,
                            'name' => $currency->name,
                            'symbol' => $currency->symbol,
                            'display_name' => $currency->code . ' - ' . $currency->name . ' (' . $currency->symbol . ')'
                        ];
                    }),

                'tax_rates' => \Modules\FinancialAccounts\Models\TaxRate::where('company_id', $companyId)
                    ->select('id', 'name', 'rate')
                    ->get(),

                'items' => \Modules\Inventory\Models\Item::forCompany($companyId)->active()
                    ->select('id', 'item_number', 'name', 'name_ar', 'unit_id', 'first_sale_price')
                    ->with('unit:id,name')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'item_number' => $item->item_number,
                            'name' => $item->name,
                            'name_ar' => $item->name_ar,
                            'unit_id' => $item->unit_id,
                            'unit_name' => $item->unit->name ?? null,
                            'first_sale_price' => $item->first_sale_price,
                            'display_name' => $item->item_number . ' - ' . ($item->name_ar ?? $item->name)
                        ];
                    }),

                'units' => \Modules\Inventory\Models\Unit::select('id', 'name', 'name_ar')->get(),

                'branches' => \Modules\Companies\Models\Branch::where('company_id', $companyId)
                    ->select('id', 'name', 'code')
                    ->get(),
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error fetching form data: ' . $e->getMessage());
        }
    }

    /**
     * Advanced search for incoming quotations with multiple criteria
     */
    public function search(Request $request)
    {
        try {
            $query = Purchase::query()
                ->with([
                    'user',
                    'company',
                    'branch',
                    'currency',
                    'supplier',
                    'customer',
                    'taxRate',
                    'items.item',
                    'items.unit',
                    'creator',
                    'updater'
                ]);

            $companyId = $request->user()->company_id ?? 101;
            $query->where('company_id', $companyId)
                  ->where('type', PurchaseTypeEnum::QUOTATION);

            // Quotation Number range search (from/to)
            if ($request->filled('quotation_number_from')) {
                $query->where('quotation_number', '>=', $request->quotation_number_from);
            }
            if ($request->filled('quotation_number_to')) {
                $query->where('quotation_number', '<=', $request->quotation_number_to);
            }

            // Specific quotation number search
            if ($request->filled('quotation_number')) {
                $query->where('quotation_number', 'like', '%' . $request->quotation_number . '%');
            }

            // Supplier Name search
            if ($request->filled('supplier_name')) {
                $query->where(function ($q) use ($request) {
                    $q->where('supplier_name', 'like', '%' . $request->supplier_name . '%')
                      ->orWhereHas('supplier', function ($sq) use ($request) {
                          $sq->where('supplier_name_ar', 'like', '%' . $request->supplier_name . '%')
                            ->orWhere('supplier_name_en', 'like', '%' . $request->supplier_name . '%');
                      });
                });
            }

            // Date search (exact date)
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

            // Amount search (exact amount)
            if ($request->filled('amount')) {
                $query->where('grand_total', $request->amount);
            }

            // Amount range search (from/to)
            if ($request->filled('amount_from')) {
                $query->where('grand_total', '>=', $request->amount_from);
            }
            if ($request->filled('amount_to')) {
                $query->where('grand_total', '<=', $request->amount_to);
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

            // Customer search
            if ($request->filled('customer_name')) {
                $query->where(function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%')
                      ->orWhere('customer_number', 'like', '%' . $request->customer_name . '%')
                      ->orWhere('customer_email', 'like', '%' . $request->customer_name . '%');
                });
            }

            // Ledger search
            if ($request->filled('ledger_code')) {
                $query->where('ledger_code', 'like', '%' . $request->ledger_code . '%');
            }

            // Invoice number search
            if ($request->filled('invoice_number')) {
                $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 15);

            // Validate sort fields
            $allowedSortFields = [
                'id', 'quotation_number', 'invoice_number', 'date', 'time', 'due_date',
                'customer_number', 'customer_name', 'customer_email', 'customer_mobile',
                'supplier_name', 'licensed_operator', 'ledger_code', 'ledger_number',
                'status', 'cash_paid', 'checks_paid', 'allowed_discount', 'discount_percentage',
                'discount_amount', 'total_without_tax', 'tax_percentage', 'tax_amount',
                'grand_total', 'remaining_balance', 'exchange_rate', 'currency_rate',
                'total_amount', 'created_at', 'updated_at'
            ];

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            $query->orderBy($sortBy, $sortOrder);

            if ($request->get('paginate', true)) {
                return $query->paginate($perPage);
            } else {
                return $query->get();
            }

        } catch (\Exception $e) {
            throw new \Exception('Error searching incoming quotations: ' . $e->getMessage());
        }
    }

    /**
     * Update incoming quotation with full data
     */
    public function update(IncomingOfferRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $purchase = Purchase::findOrFail($id);
                $userId = $request->user()->id;
                $validatedData = $request->validated();

                // Update currency rate if currency changed
                if (isset($validatedData['currency_id']) && $validatedData['currency_id'] != $purchase->currency_id) {
                    $currencyRate = $this->getCurrencyRate($validatedData['currency_id'], $validatedData['tax_rate_id'] ?? null);
                    $validatedData['currency_rate'] = $currencyRate['rate'];
                    $validatedData['currency_rate_with_tax'] = $currencyRate['rate_with_tax'];
                }

                // Prepare update data
                $updateData = $validatedData;
                $updateData['updated_by'] = $userId;

                // Remove items from main data
                $items = $updateData['items'] ?? [];
                unset($updateData['items']);

                // Update purchase
                $purchase->update($updateData);

                // Update purchase items if provided
                if (!empty($items)) {
                    // Delete existing items
                    $purchase->items()->delete();

                    // Create new items
                    $this->createPurchaseItems($purchase, $items);

                    // Recalculate totals
                    $this->calculateTotals($purchase);
                }

                return $purchase->load([
                    'user',
                    'company',
                    'branch',
                    'currency',
                    'supplier',
                    'customer',
                    'taxRate',
                    'items.item',
                    'items.unit',
                    'creator',
                    'updater'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating incoming quotation: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields for incoming quotations
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'quotation_number' => 'Quotation Number',
            'invoice_number' => 'Invoice Number',
            'date' => 'Date',
            'time' => 'Time',
            'due_date' => 'Due Date',
            'customer_number' => 'Customer Number',
            'customer_name' => 'Customer Name',
            'customer_email' => 'Customer Email',
            'customer_mobile' => 'Customer Mobile',
            'supplier_name' => 'Supplier Name',
            'licensed_operator' => 'Licensed Operator',
            'ledger_code' => 'Ledger Code',
            'ledger_number' => 'Ledger Number',
            'status' => 'Status',
            'cash_paid' => 'Cash Paid',
            'checks_paid' => 'Checks Paid',
            'allowed_discount' => 'Allowed Discount',
            'discount_percentage' => 'Discount Percentage',
            'discount_amount' => 'Discount Amount',
            'total_without_tax' => 'Total Without Tax',
            'tax_percentage' => 'Tax Percentage',
            'tax_amount' => 'Tax Amount',
            'grand_total' => 'Grand Total',
            'remaining_balance' => 'Remaining Balance',
            'exchange_rate' => 'Exchange Rate',
            'currency_rate' => 'Currency Rate',
            'total_amount' => 'Total Amount',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At'
        ];
    }
}
