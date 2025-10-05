<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\InvoiceService;
use Modules\Purchases\Http\Requests\InvoiceRequest;
use Modules\Purchases\Transformers\InvoiceResource;

/**
 * @group Purchase Management / Invoices
 *
 * APIs for managing purchase invoices, including invoice processing, payment tracking, and vendor management.
 */
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
                'error' => 'An error occurred while fetching invoices.',
                'message' => $e->getMessage()
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
                'error' => 'An error occurred while creating invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice with all related data.
     * Returns invoice details with relationships loaded for comprehensive view.
     *
     * @param int $id Invoice ID
     * @return JsonResponse Invoice resource or error response
     */
    public function show($id, Request $request)
    {
        try {
            $invoice = $this->invoiceService->show($id);
            return response()->json([
                'success' => true,
                'data' => new InvoiceResource($invoice)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching invoice details.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified invoice in storage.
     * Updates invoice data with comprehensive validation and relationship handling.
     *
     * @param Request $request Request data
     * @param int $id Invoice ID
     * @return JsonResponse Updated invoice resource or error response
     */
    public function update(InvoiceRequest $request, $id)
    {
        try {
            $invoice = $this->invoiceService->update($request, $id);
            return response()->json([
                'success' => true,
                'data' => new InvoiceResource($invoice),
                'message' => 'Invoice updated successfully.'
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
     * Remove the specified invoice from storage (soft delete).
     * Performs soft delete with audit trail tracking who deleted the invoice.
     *
     * @param int $id Invoice ID
     * @return JsonResponse Success message or error response
     */
    public function destroy($id)
    {
        try {
            $this->invoiceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
