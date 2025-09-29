<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Purchases\app\Services\InvoiceService;
use Modules\Purchases\Http\Requests\InvoiceRequest;
use Modules\Purchases\Http\Requests\PurchaseInvoiceRequest;
use Modules\Purchases\Transformers\InvoiceResource;
use Modules\Purchases\Http\Resources\PurchaseInvoiceResource;
use Modules\Suppliers\Models\Supplier;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\HumanResources\Models\Employee;

class InvoiceController extends Controller
{

    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $invoices = $this->invoiceService->index($request);
            return response()->json([
                'success' => true,
                'data' => PurchaseInvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ],
                'message' => 'Purchase invoices retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error fetching purchase invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseInvoiceRequest $request)
    {
        try {
            $invoice = $this->invoiceService->store($request);
            return response()->json([
                'success' => true,
                'data' => new PurchaseInvoiceResource($invoice),
                'message' => 'Purchase invoice created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error creating purchase invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id, Request $request)
    {
        try {
            $result = $this->invoiceService->show($id, $request);

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => new PurchaseInvoiceResource($result['purchase']),
                    'statistics' => $result['statistics'],
                    'formatted_data' => $result['formatted_data']
                ],
                'message' => 'Purchase invoice details retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving purchase invoice: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PurchaseInvoiceRequest $request, $id)
    {
        try {
            $invoice = $this->invoiceService->update($id, $request);

            return response()->json([
                'success' => true,
                'data' => new PurchaseInvoiceResource($invoice),
                'message' => 'Purchase invoice updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error updating purchase invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        try {
            $result = $this->invoiceService->destroy($id, $request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Purchase invoice deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error deleting purchase invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for creating/editing purchase invoices
     */
    public function getFormData(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;

            return response()->json([
                'success' => true,
                'data' => [
                    'suppliers' => Supplier::where('company_id', $companyId)
                        ->select('id', 'supplier_number', 'first_name', 'second_name', 'email', 'mobile')
                        ->get()
                        ->map(function ($supplier) {
                            return [
                                'id' => $supplier->id,
                                'supplier_number' => $supplier->supplier_number,
                                'name' => trim($supplier->first_name . ' ' . $supplier->second_name),
                                'email' => $supplier->email,
                                'mobile' => $supplier->mobile,
                            ];
                        }),
                    'currencies' => Currency::select('id', 'name', 'code', 'symbol')->get(),
                    'tax_rates' => TaxRate::select('id', 'name', 'code', 'rate', 'type')->get(),
                    'employees' => Employee::where('company_id', $companyId)
                        ->select('id', 'employee_number', 'first_name', 'last_name', 'email')
                        ->get()
                        ->map(function ($employee) {
                            return [
                                'id' => $employee->id,
                                'employee_number' => $employee->employee_number,
                                'name' => trim($employee->first_name . ' ' . $employee->last_name),
                                'email' => $employee->email,
                            ];
                        }),
                    'warehouses' => Warehouse::where('company_id', $companyId)
                        ->select('id', 'warehouse_number', 'name', 'address')
                        ->get(),
                    'units' => Unit::select('id', 'name', 'symbol', 'code')->get(),
                ],
                'message' => 'Form data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving form data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search suppliers with bidirectional search (number ↔ name)
     */
    public function searchSuppliers(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);

            $query = Supplier::where('company_id', $companyId);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('supplier_number', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('second_name', 'like', '%' . $search . '%')
                      ->orWhere('supplier_name_en', 'like', '%' . $search . '%')
                      ->orWhere('supplier_name_ar', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('mobile', 'like', '%' . $search . '%');
                });
            }

            $suppliers = $query->select('id', 'supplier_number', 'first_name', 'second_name', 'email', 'mobile')
                ->limit($limit)
                ->get()
                ->map(function ($supplier) {
                    return [
                        'id' => $supplier->id,
                        'supplier_number' => $supplier->supplier_number,
                        'name' => trim($supplier->first_name . ' ' . $supplier->second_name),
                        'email' => $supplier->email,
                        'mobile' => $supplier->mobile,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'message' => 'Suppliers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error searching suppliers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search items with auto-population
     */
    public function searchItems(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);

            $query = Item::where('company_id', $companyId);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('item_number', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%')
                      ->orWhere('barcode', 'like', '%' . $search . '%');
                });
            }

            $items = $query->with(['unit:id,name,symbol', 'category:id,name'])
                ->select('id', 'item_number', 'name', 'description', 'unit_id', 'category_id', 'selling_price', 'purchase_price', 'barcode')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_number' => $item->item_number,
                        'name' => $item->name,
                        'description' => $item->description,
                        'barcode' => $item->barcode,
                        'unit_id' => $item->unit_id,
                        'unit_name' => $item->unit->name ?? null,
                        'unit_symbol' => $item->unit->symbol ?? null,
                        'category_id' => $item->category_id,
                        'category_name' => $item->category->name ?? null,
                        'selling_price' => $item->selling_price,
                        'purchase_price' => $item->purchase_price,
                        'suggested_price' => $item->purchase_price ?? $item->selling_price ?? 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Items retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error searching items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live currency rate
     */
    public function getCurrencyRate(Request $request)
    {
        try {
            $currencyId = $request->get('currency_id');
            $taxRateId = $request->get('tax_rate_id');

            if (!$currencyId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency ID is required'
                ], 400);
            }

            $currency = Currency::find($currencyId);
            if (!$currency) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency not found'
                ], 404);
            }

            // Get live exchange rate
            $exchangeRate = 1.0;
            try {
                if ($currency->code !== 'USD') {
                    $response = \Illuminate\Support\Facades\Http::timeout(5)
                        ->get("https://api.exchangerate-api.com/v4/latest/USD");
                    if ($response->successful()) {
                        $rates = $response->json()['rates'] ?? [];
                        $exchangeRate = $rates[$currency->code] ?? 1.0;
                    }
                }
            } catch (\Exception $e) {
                // Use default rate if API fails
                $exchangeRate = 1.0;
            }

            $currencyRate = $exchangeRate;
            $currencyRateWithTax = $exchangeRate;
            $isTaxApplied = false;

            // Apply tax if provided
            if ($taxRateId) {
                $taxRate = TaxRate::find($taxRateId);
                if ($taxRate && $taxRate->rate > 0) {
                    $currencyRateWithTax = $exchangeRate * (1 + ($taxRate->rate / 100));
                    $isTaxApplied = true;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'currency' => [
                        'id' => $currency->id,
                        'name' => $currency->name,
                        'code' => $currency->code,
                        'symbol' => $currency->symbol,
                    ],
                    'exchange_rate' => $exchangeRate,
                    'currency_rate' => $currencyRate,
                    'currency_rate_with_tax' => $currencyRateWithTax,
                    'is_tax_applied' => $isTaxApplied,
                    'last_updated' => now()->toISOString(),
                ],
                'message' => 'Currency rate retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving currency rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for purchase invoices
     */
    public function advancedSearch(Request $request)
    {
        try {
            $invoices = $this->invoiceService->index($request);

            return response()->json([
                'success' => true,
                'data' => PurchaseInvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ],
                'search_criteria' => [
                    'purchase_invoice_number' => $request->get('purchase_invoice_number'),
                    'purchase_invoice_number_from' => $request->get('purchase_invoice_number_from'),
                    'purchase_invoice_number_to' => $request->get('purchase_invoice_number_to'),
                    'supplier_name' => $request->get('supplier_name'),
                    'date' => $request->get('date'),
                    'date_from' => $request->get('date_from'),
                    'date_to' => $request->get('date_to'),
                    'amount' => $request->get('amount'),
                    'amount_from' => $request->get('amount_from'),
                    'amount_to' => $request->get('amount_to'),
                    'currency_id' => $request->get('currency_id'),
                    'currency_code' => $request->get('currency_code'),
                    'licensed_operator' => $request->get('licensed_operator'),
                    'status' => $request->get('status'),
                    'sort_by' => $request->get('sort_by', 'created_at'),
                    'sort_order' => $request->get('sort_order', 'desc'),
                ],
                'message' => 'Advanced search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error performing advanced search: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data for purchase invoices
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;

            return response()->json([
                'success' => true,
                'data' => [
                    'search_fields' => [
                        'purchase_invoice_number' => [
                            'label' => 'Purchase Invoice Number',
                            'type' => 'text',
                            'placeholder' => 'Enter purchase invoice number',
                        ],
                        'purchase_invoice_number_range' => [
                            'label' => 'Purchase Invoice Number Range',
                            'type' => 'range',
                            'from_placeholder' => 'From invoice number',
                            'to_placeholder' => 'To invoice number',
                        ],
                        'supplier_name' => [
                            'label' => 'Supplier Name',
                            'type' => 'text',
                            'placeholder' => 'Enter supplier name or number',
                        ],
                        'date' => [
                            'label' => 'Exact Date',
                            'type' => 'date',
                            'placeholder' => 'Select exact date',
                        ],
                        'date_range' => [
                            'label' => 'Date Range',
                            'type' => 'date_range',
                            'from_placeholder' => 'From date',
                            'to_placeholder' => 'To date',
                        ],
                        'amount' => [
                            'label' => 'Exact Amount',
                            'type' => 'number',
                            'placeholder' => 'Enter exact amount',
                        ],
                        'amount_range' => [
                            'label' => 'Amount Range',
                            'type' => 'number_range',
                            'from_placeholder' => 'From amount',
                            'to_placeholder' => 'To amount',
                        ],
                        'currency' => [
                            'label' => 'Currency',
                            'type' => 'select',
                            'options' => Currency::select('id', 'name', 'code', 'symbol')->get(),
                        ],
                        'licensed_operator' => [
                            'label' => 'Licensed Operator',
                            'type' => 'text',
                            'placeholder' => 'Enter licensed operator name',
                        ],
                        'status' => [
                            'label' => 'Status',
                            'type' => 'select',
                            'options' => [
                                ['value' => 'draft', 'label' => 'Draft'],
                                ['value' => 'pending', 'label' => 'Pending'],
                                ['value' => 'approved', 'label' => 'Approved'],
                                ['value' => 'completed', 'label' => 'Completed'],
                                ['value' => 'cancelled', 'label' => 'Cancelled'],
                            ],
                        ],
                    ],
                    'sort_options' => $this->getSortableFieldsArray(),
                ],
                'message' => 'Search form data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving search form data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for purchase invoices
     */
    public function getSortableFields()
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSortableFieldsArray(),
            'message' => 'Sortable fields retrieved successfully'
        ]);
    }

    /**
     * Get sortable fields array
     */
    private function getSortableFieldsArray()
    {
        return [
            // Basic Information
            'id' => 'ID',
            'user_id' => 'User ID',
            'company_id' => 'Company ID',
            'branch_id' => 'Branch ID',
            'currency_id' => 'Currency ID',
            'employee_id' => 'Employee ID',
            'supplier_id' => 'Supplier ID',
            'customer_id' => 'Customer ID',

            // Invoice Information
            'quotation_number' => 'Quotation Number',
            'invoice_number' => 'Invoice Number',
            'purchase_invoice_number' => 'Purchase Invoice Number',
            'entry_number' => 'Entry Number',
            'date' => 'Date',
            'time' => 'Time',
            'due_date' => 'Due Date',

            // Customer Information
            'customer_number' => 'Customer Number',
            'customer_name' => 'Customer Name',
            'customer_email' => 'Customer Email',
            'customer_mobile' => 'Customer Mobile',

            // Supplier Information
            'supplier_name' => 'Supplier Name',
            'supplier_number' => 'Supplier Number',
            'supplier_email' => 'Supplier Email',
            'supplier_mobile' => 'Supplier Mobile',
            'licensed_operator' => 'Licensed Operator',

            // Ledger System
            'journal_id' => 'Journal ID',
            'journal_number' => 'Journal Number',
            'ledger_code' => 'Ledger Code',
            'ledger_number' => 'Ledger Number',
            'ledger_invoice_count' => 'Ledger Invoice Count',

            // Type and Status
            'type' => 'Type',
            'status' => 'Status',

            // Financial Information
            'cash_paid' => 'Cash Paid',
            'checks_paid' => 'Checks Paid',
            'allowed_discount' => 'Allowed Discount',
            'discount_percentage' => 'Discount Percentage',
            'discount_amount' => 'Discount Amount',
            'total_without_tax' => 'Total Without Tax',
            'tax_percentage' => 'Tax Percentage',
            'tax_amount' => 'Tax Amount',
            'remaining_balance' => 'Remaining Balance',
            'exchange_rate' => 'Exchange Rate',
            'currency_rate' => 'Currency Rate',
            'currency_rate_with_tax' => 'Currency Rate With Tax',
            'tax_rate_id' => 'Tax Rate ID',
            'is_tax_applied_to_currency' => 'Tax Applied to Currency',
            'total_foreign' => 'Total Foreign',
            'total_local' => 'Total Local',
            'total_amount' => 'Total Amount',
            'grand_total' => 'Grand Total',

            // Additional Information
            'notes' => 'Notes',

            // Audit Fields
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',

            // Relationship-based sorting
            'supplier_full_name' => 'Supplier Full Name',
            'currency_name' => 'Currency Name',
            'currency_code' => 'Currency Code',
            'employee_name' => 'Employee Name',
            'user_name' => 'User Name',
            'branch_name' => 'Branch Name',
            'tax_rate_name' => 'Tax Rate Name',
        ];
    }
}
