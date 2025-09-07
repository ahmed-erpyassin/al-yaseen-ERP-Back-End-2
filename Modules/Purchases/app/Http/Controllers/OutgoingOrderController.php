<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\OutgoingOrderService;
use Modules\Purchases\Http\Requests\OutgoingOrderRequest;
use Modules\Purchases\Transformers\OutgoingOrderResource;

class OutgoingOrderController extends Controller
{

    protected OutgoingOrderService $outgoingOrderService;

    public function __construct(OutgoingOrderService $outgoingOrderService)
    {
        $this->outgoingOrderService = $outgoingOrderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try {
            $offers = $this->outgoingOrderService->index($request);
            return response()->json([
                'success' => true,
                'data' => OutgoingOrderResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OutgoingOrderRequest $request)
    {
        try {
            $order = $this->outgoingOrderService->store($request);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOrderResource($order)
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
