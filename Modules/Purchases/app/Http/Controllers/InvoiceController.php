<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\InvoiceService;
use Modules\Sales\Http\Requests\InvoiceRequest;
use Modules\Sales\Transformers\InvoiceResource;

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
    public function store(InvoiceRequest $request)
    {
        try {
            $offer = $this->invoiceService->store($request);
            return response()->json([
                'success' => true,
                'data' => new InvoiceResource($offer)
            ], 200);
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
    public function show($id)
    {
        try {
            // For now, return a placeholder response until service method is implemented
            return response()->json([
                'success' => false,
                'error' => 'Show method not yet implemented in service.',
                'message' => 'This endpoint will be available once the service method is implemented.'
            ], 501);
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
    public function update(Request $request, $id)
    {
        try {
            // For now, return a placeholder response until service method is implemented
            return response()->json([
                'success' => false,
                'error' => 'Update method not yet implemented in service.',
                'message' => 'This endpoint will be available once the service method is implemented.'
            ], 501);
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
            // For now, return a placeholder response until service method is implemented
            return response()->json([
                'success' => false,
                'error' => 'Delete method not yet implemented in service.',
                'message' => 'This endpoint will be available once the service method is implemented.'
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
