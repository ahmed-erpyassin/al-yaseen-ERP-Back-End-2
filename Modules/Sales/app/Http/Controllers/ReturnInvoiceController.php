<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\ReturnInvoiceService;
use Modules\Sales\Http\Requests\ReturnInvoiceRequest;
use Modules\Sales\Transformers\ReturnInvoiceResource;

class ReturnInvoiceController extends Controller
{
    protected ReturnInvoiceService $returnInvoiceService;

    public function __construct(ReturnInvoiceService $returnInvoiceService)
    {
        $this->returnInvoiceService = $returnInvoiceService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->returnInvoiceService->index($request);
            return response()->json([
                'success' => true,
                'data' => ReturnInvoiceResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReturnInvoiceRequest $request)
    {
        try {
            $invoice = $this->returnInvoiceService->store($request);
            return response()->json([
                'success' => true,
                'data' => new ReturnInvoiceResource($invoice)
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
            $returnInvoice = $this->returnInvoiceService->show($id);
            return response()->json([
                'success' => true,
                'data' => new ReturnInvoiceResource($returnInvoice),
                'message' => 'Return invoice retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReturnInvoiceRequest $request, $id)
    {
        try {
            $returnInvoice = $this->returnInvoiceService->update($request, $id);
            return response()->json([
                'success' => true,
                'data' => new ReturnInvoiceResource($returnInvoice),
                'message' => 'Return invoice updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating return invoice.',
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
            $this->returnInvoiceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Return invoice deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
