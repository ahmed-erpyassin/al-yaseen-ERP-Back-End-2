<?php

namespace Modules\Purchases\Http\Controllers;

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
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('sales::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
