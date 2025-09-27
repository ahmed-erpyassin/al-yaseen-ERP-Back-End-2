<?php

namespace Modules\Sales\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\InvoiceRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\app\Services\BookNumberingService;
use Modules\Customers\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\FinancialAccounts\Models\Currency;

class InvoiceService
{
    public function index(Request $request)
    {
        try {
            $query = Sale::query()
                ->where('type', SalesTypeEnum::INVOICE)
                ->with(['customer', 'currency', 'employee', 'items']);

            // Apply search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Handle special sorting cases
            if ($sortBy === 'customer_name') {
                $query->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                      ->orderBy('customers.name', $sortOrder)
                      ->select('sales.*');
            } elseif ($sortBy === 'currency_name') {
                $query->leftJoin('currencies', 'sales.currency_id', '=', 'currencies.id')
                      ->orderBy('currencies.name', $sortOrder)
                      ->select('sales.*');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching sales invoices: ' . $e->getMessage());
        }
    }

    /**
     * Apply search filters to the query
     */
    private function applySearchFilters($query, Request $request)
    {
        // Invoice number search (exact or range)
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('invoice_number_from') && $request->filled('invoice_number_to')) {
            $query->whereBetween('invoice_number', [$request->invoice_number_from, $request->invoice_number_to]);
        }

        // Customer name search
        if ($request->filled('customer_name')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        // Date search (exact date)
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Date range search
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        // Amount search
        if ($request->filled('amount')) {
            $query->where('total_amount', $request->amount);
        }

        if ($request->filled('amount_from') && $request->filled('amount_to')) {
            $query->whereBetween('total_amount', [$request->amount_from, $request->amount_to]);
        }

        // Currency search
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        // Licensed operator search
        if ($request->filled('licensed_operator')) {
            $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Company filter (always apply for multi-tenant)
        if ($request->user() && $request->user()->company_id) {
            $query->where('company_id', $request->user()->company_id);
        }
    }

    public function store(InvoiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;
                $branchId = $request->user()->branch_id ?? 1; // Default branch if not set

                // Initialize BookNumberingService
                $bookNumberingService = new BookNumberingService();

                // Generate book code and invoice number
                $bookingData = $bookNumberingService->generateInvoiceBookAndNumber($companyId, $request->journal_id);

                // Get customer details for auto-population
                $customer = Customer::find($request->customer_id);
                $customerEmail = $request->customer_email ?? $customer->email ?? null;

                // Get currency details and exchange rate
                $currency = Currency::find($request->currency_id);
                $exchangeRate = $request->exchange_rate ?? 1.0;

                // Prepare invoice data
                $invoiceData = [
                    'type' => SalesTypeEnum::INVOICE,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'status' => 'draft',

                    // Auto-generated fields
                    'book_code' => $bookingData['book_code'],
                    'invoice_number' => $bookingData['invoice_number'],
                    'journal_number' => $bookingData['journal_number'],
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),

                    // Customer information
                    'customer_id' => $request->customer_id,
                    'customer_email' => $customerEmail,
                    'licensed_operator' => $request->licensed_operator,

                    // Currency and financial
                    'currency_id' => $request->currency_id,
                    'exchange_rate' => $exchangeRate,
                    'due_date' => $request->due_date,

                    // Financial fields
                    'cash_paid' => $request->cash_paid ?? 0,
                    'checks_paid' => $request->checks_paid ?? 0,
                    'allowed_discount' => $request->allowed_discount ?? 0,
                    'discount_percentage' => $request->discount_percentage ?? 0,
                    'tax_percentage' => $request->tax_percentage ?? 0,
                    'is_tax_inclusive' => $request->is_tax_inclusive ?? false,
                    'notes' => $request->notes,

                    // Audit fields
                    'created_by' => $userId,
                ];

                // Create the invoice
                $invoice = Sale::create($invoiceData);

                // Process invoice items
                if ($request->has('items') && is_array($request->items)) {
                    $this->processInvoiceItems($invoice, $request->items, $companyId, $userId);
                }

                // Calculate totals
                $this->calculateInvoiceTotals($invoice);

                return $invoice->load(['items', 'customer', 'currency']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating sales invoice: ' . $e->getMessage());
        }
    }

    /**
     * Process invoice items with auto-population
     */
    private function processInvoiceItems(Sale $invoice, array $items, $companyId, $userId)
    {
        $serialNumber = 1;

        foreach ($items as $itemData) {
            // Get item details for auto-population
            $item = Item::find($itemData['item_id']);

            if (!$item) {
                continue; // Skip invalid items
            }

            // Auto-populate item details
            $itemNumber = $item->item_number ?? 'ITM-' . str_pad($item->id, 6, '0', STR_PAD_LEFT);
            $itemName = $item->item_name_en ?? $item->item_name_ar ?? 'Unknown Item';

            // Get unit price (first sale price from warehouse)
            $unitPrice = $itemData['unit_price'] ?? $item->first_sale_price ?? 0;

            // Calculate totals
            $quantity = $itemData['quantity'];
            $subtotal = $quantity * $unitPrice;
            $discountAmount = 0;
            $discountPercentage = $itemData['discount_rate'] ?? 0;

            if ($discountPercentage > 0) {
                $discountAmount = $subtotal * ($discountPercentage / 100);
            }

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = 0;
            $taxRate = $itemData['tax_rate'] ?? 0;

            if ($taxRate > 0) {
                $taxAmount = $afterDiscount * ($taxRate / 100);
            }

            $total = $afterDiscount + $taxAmount;

            // Create sale item
            SaleItem::create([
                'sale_id' => $invoice->id,
                'item_id' => $itemData['item_id'],
                'unit_id' => $itemData['unit_id'],
                'serial_number' => $serialNumber,
                'item_number' => $itemNumber,
                'item_name' => $itemName,
                'description' => $itemData['description'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_rate' => $discountPercentage,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'total_foreign' => $total,
                'total_local' => $total * $invoice->exchange_rate,
                'total' => $total,
            ]);

            $serialNumber++;
        }
    }

    /**
     * Calculate invoice totals
     */
    private function calculateInvoiceTotals(Sale $invoice)
    {
        $items = $invoice->items;

        $totalWithoutTax = $items->sum(function ($item) {
            $subtotal = $item->quantity * $item->unit_price;
            $discount = $subtotal * ($item->discount_rate / 100);
            return $subtotal - $discount;
        });

        $totalTax = $items->sum(function ($item) {
            $subtotal = $item->quantity * $item->unit_price;
            $discount = $subtotal * ($item->discount_rate / 100);
            $afterDiscount = $subtotal - $discount;
            return $afterDiscount * ($item->tax_rate / 100);
        });

        $totalAmount = $totalWithoutTax + $totalTax;
        $remainingBalance = $totalAmount - $invoice->cash_paid - $invoice->checks_paid;

        // Update invoice totals
        $invoice->update([
            'total_without_tax' => $totalWithoutTax,
            'tax_amount' => $totalTax,
            'total_foreign' => $totalAmount,
            'total_local' => $totalAmount * $invoice->exchange_rate,
            'total_amount' => $totalAmount,
            'remaining_balance' => $remainingBalance,
        ]);
    }

    /**
     * Show comprehensive invoice details with all related data
     */
    public function show($id)
    {
        try {
            $invoice = Sale::with([
                'items' => function ($query) {
                    $query->with(['item', 'unit']);
                },
                'customer' => function ($query) {
                    $query->select(['id', 'customer_number', 'name', 'email', 'phone', 'licensed_operator', 'company_name']);
                },
                'currency' => function ($query) {
                    $query->select(['id', 'code', 'name', 'symbol']);
                },
                'employee' => function ($query) {
                    $query->select(['id', 'employee_number', 'first_name', 'second_name', 'email']);
                },
                'user' => function ($query) {
                    $query->select(['id', 'name', 'email']);
                },
                'company' => function ($query) {
                    $query->select(['id', 'name', 'email', 'phone']);
                },
                'branch' => function ($query) {
                    $query->select(['id', 'name', 'address']);
                }
            ])
            ->where('type', SalesTypeEnum::INVOICE)
            ->findOrFail($id);

            // Add computed fields
            $invoice->items_count = $invoice->items->count();
            $invoice->total_quantity = $invoice->items->sum('quantity');

            return $invoice;
        } catch (Exception $e) {
            throw new \Exception('Error fetching invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update invoice with comprehensive validation and data handling
     */
    public function update(InvoiceRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $invoice = Sale::where('type', SalesTypeEnum::INVOICE)->findOrFail($id);

                // Check if invoice can be updated
                if ($invoice->status === 'invoiced') {
                    throw new \Exception('Cannot update an invoiced invoice');
                }

                $userId = $request->user()->id;
                $companyId = $request->user()->company_id;

                // Get customer details for auto-population if customer changed
                $customer = null;
                if ($request->customer_id && $request->customer_id != $invoice->customer_id) {
                    $customer = Customer::find($request->customer_id);
                }

                // Get currency details if currency changed
                $currency = null;
                if ($request->currency_id && $request->currency_id != $invoice->currency_id) {
                    $currency = Currency::find($request->currency_id);
                }

                // Prepare update data
                $updateData = [
                    // Customer information
                    'customer_id' => $request->customer_id ?? $invoice->customer_id,
                    'customer_email' => $request->customer_email ?? ($customer ? $customer->email : $invoice->customer_email),
                    'licensed_operator' => $request->licensed_operator ?? $invoice->licensed_operator,

                    // Currency and financial
                    'currency_id' => $request->currency_id ?? $invoice->currency_id,
                    'exchange_rate' => $request->exchange_rate ?? $invoice->exchange_rate,
                    'due_date' => $request->due_date ?? $invoice->due_date,

                    // Financial fields
                    'cash_paid' => $request->cash_paid ?? $invoice->cash_paid,
                    'checks_paid' => $request->checks_paid ?? $invoice->checks_paid,
                    'allowed_discount' => $request->allowed_discount ?? $invoice->allowed_discount,
                    'discount_percentage' => $request->discount_percentage ?? $invoice->discount_percentage,
                    'tax_percentage' => $request->tax_percentage ?? $invoice->tax_percentage,
                    'is_tax_inclusive' => $request->is_tax_inclusive ?? $invoice->is_tax_inclusive,
                    'notes' => $request->notes ?? $invoice->notes,

                    // Status can be updated
                    'status' => $request->status ?? $invoice->status,

                    // Audit fields
                    'updated_by' => $userId,
                ];

                // Update the invoice
                $invoice->update($updateData);

                // Update items if provided
                if ($request->has('items') && is_array($request->items)) {
                    // Soft delete existing items (preserve history)
                    $invoice->items()->delete();

                    // Create new items
                    $this->processInvoiceItems($invoice, $request->items, $companyId, $userId);
                }

                // Recalculate totals
                $this->calculateInvoiceTotals($invoice);

                return $invoice->load(['items.item', 'items.unit', 'customer', 'currency', 'employee']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error updating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete invoice
     */
    public function destroy($id)
    {
        try {
            $invoice = Sale::where('type', SalesTypeEnum::INVOICE)->findOrFail($id);

            // Check if invoice can be deleted
            if ($invoice->status === 'invoiced') {
                throw new \Exception('Cannot delete an invoiced invoice');
            }

            // Soft delete the invoice (preserves data for audit trail)
            $invoice->delete();

            // Also soft delete related items
            $invoice->items()->delete();

            return true;
        } catch (Exception $e) {
            throw new \Exception('Error deleting invoice: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft deleted invoice
     */
    public function restore($id)
    {
        try {
            $invoice = Sale::withTrashed()
                ->where('type', SalesTypeEnum::INVOICE)
                ->findOrFail($id);

            // Restore the invoice
            $invoice->restore();

            // Restore related items
            $invoice->items()->withTrashed()->restore();

            return $invoice->load(['items', 'customer', 'currency']);
        } catch (Exception $e) {
            throw new \Exception('Error restoring invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get deleted invoices
     */
    public function getDeleted(Request $request)
    {
        try {
            $query = Sale::onlyTrashed()
                ->where('type', SalesTypeEnum::INVOICE)
                ->with(['customer', 'currency', 'employee']);

            // Apply search filters if provided
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($sortBy === 'customer_name') {
                $query->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                      ->orderBy('customers.name', $sortOrder)
                      ->select('sales.*');
            } elseif ($sortBy === 'currency_name') {
                $query->leftJoin('currencies', 'sales.currency_id', '=', 'currencies.id')
                      ->orderBy('currencies.name', $sortOrder)
                      ->select('sales.*');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching deleted invoices: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete invoice (force delete)
     */
    public function forceDelete($id)
    {
        try {
            $invoice = Sale::withTrashed()
                ->where('type', SalesTypeEnum::INVOICE)
                ->findOrFail($id);

            // Force delete related items first
            $invoice->items()->withTrashed()->forceDelete();

            // Force delete the invoice
            $invoice->forceDelete();

            return true;
        } catch (Exception $e) {
            throw new \Exception('Error permanently deleting invoice: ' . $e->getMessage());
        }
    }
}
