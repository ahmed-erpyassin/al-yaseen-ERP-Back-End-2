<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\InvoiceService;
use Modules\Sales\Http\Requests\InvoiceRequest;
use Modules\Sales\Transformers\InvoiceResource;
use Modules\Sales\Models\Sale;
use Modules\Sales\app\Enums\SalesTypeEnum;

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
                'data'    => InvoiceResource::collection($invoices)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoiceRequest $request)
    {
        try {
            $offer = $this->invoiceService->store($request);
            return response()->json([
                'success' => true,
                'data' => new InvoiceResource($offer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $invoice = $this->invoiceService->show($id);
            return response()->json([
                'success' => true,
                'data' => new InvoiceResource($invoice),
                'message' => 'Invoice retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoiceRequest $request, $id)
    {
        try {
            $invoice = $this->invoiceService->update($request, $id);
            return response()->json([
                'success' => true,
                'data' => new InvoiceResource($invoice),
                'message' => 'Invoice updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->invoiceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for invoices
     */
    public function search(Request $request)
    {
        try {
            $invoices = $this->invoiceService->index($request);
            return response()->json([
                'success' => true,
                'data' => InvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice search form data
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;

            // Get customers for dropdown
            $customers = \Modules\Customers\Models\Customer::where('company_id', $companyId)
                ->where('status', 'active')
                ->select(['id', 'customer_number', 'name'])
                ->orderBy('name')
                ->get();

            // Get currencies for dropdown
            $currencies = \Modules\FinancialAccounts\Models\Currency::where('company_id', $companyId)
                ->select(['id', 'code', 'name', 'symbol'])
                ->orderBy('name')
                ->get();

            // Get licensed operators (unique values)
            $licensedOperators = Sale::where('company_id', $companyId)
                ->where('type', SalesTypeEnum::INVOICE)
                ->whereNotNull('licensed_operator')
                ->where('licensed_operator', '!=', '')
                ->distinct()
                ->pluck('licensed_operator')
                ->filter()
                ->values();

            // Get status options
            $statusOptions = [
                'draft' => 'Draft',
                'approved' => 'Approved',
                'sent' => 'Sent',
                'invoiced' => 'Invoiced',
                'cancelled' => 'Cancelled'
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $customers,
                    'currencies' => $currencies,
                    'licensed_operators' => $licensedOperators,
                    'status_options' => $statusOptions,
                    'sortable_fields' => [
                        'invoice_number' => 'Invoice Number',
                        'date' => 'Date',
                        'customer_name' => 'Customer Name',
                        'total_amount' => 'Amount',
                        'currency_name' => 'Currency',
                        'licensed_operator' => 'Licensed Operator',
                        'status' => 'Status',
                        'created_at' => 'Created At'
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching search form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted invoice
     */
    public function restore($id)
    {
        try {
            $invoice = $this->invoiceService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Invoice restored successfully',
                'data' => new InvoiceResource($invoice)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deleted invoices
     */
    public function getDeleted(Request $request)
    {
        try {
            $invoices = $this->invoiceService->getDeleted($request);

            return response()->json([
                'success' => true,
                'data' => InvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete invoice
     */
    public function forceDelete($id)
    {
        try {
            $this->invoiceService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Invoice permanently deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while permanently deleting invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields configuration
     */
    public function getSortableFields()
    {
        try {
            $sortableFields = [
                'invoice_number' => [
                    'label' => 'Invoice Number',
                    'type' => 'string',
                    'sortable' => true
                ],
                'book_code' => [
                    'label' => 'Book Code',
                    'type' => 'string',
                    'sortable' => true
                ],
                'date' => [
                    'label' => 'Date',
                    'type' => 'date',
                    'sortable' => true
                ],
                'due_date' => [
                    'label' => 'Due Date',
                    'type' => 'date',
                    'sortable' => true
                ],
                'customer_name' => [
                    'label' => 'Customer Name',
                    'type' => 'string',
                    'sortable' => true,
                    'join_table' => 'customers'
                ],
                'total_amount' => [
                    'label' => 'Total Amount',
                    'type' => 'decimal',
                    'sortable' => true
                ],
                'remaining_balance' => [
                    'label' => 'Remaining Balance',
                    'type' => 'decimal',
                    'sortable' => true
                ],
                'currency_name' => [
                    'label' => 'Currency',
                    'type' => 'string',
                    'sortable' => true,
                    'join_table' => 'currencies'
                ],
                'exchange_rate' => [
                    'label' => 'Exchange Rate',
                    'type' => 'decimal',
                    'sortable' => true
                ],
                'licensed_operator' => [
                    'label' => 'Licensed Operator',
                    'type' => 'string',
                    'sortable' => true
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'string',
                    'sortable' => true
                ],
                'cash_paid' => [
                    'label' => 'Cash Paid',
                    'type' => 'decimal',
                    'sortable' => true
                ],
                'checks_paid' => [
                    'label' => 'Checks Paid',
                    'type' => 'decimal',
                    'sortable' => true
                ],
                'tax_amount' => [
                    'label' => 'Tax Amount',
                    'type' => 'decimal',
                    'sortable' => true
                ],
                'created_at' => [
                    'label' => 'Created At',
                    'type' => 'datetime',
                    'sortable' => true
                ],
                'updated_at' => [
                    'label' => 'Updated At',
                    'type' => 'datetime',
                    'sortable' => true
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $sortableFields
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching sortable fields.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
