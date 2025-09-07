<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\IncomingOrderService;
use Modules\Sales\Transformers\IncomingOrderResource;

class IncomingOrderController extends Controller
{

    protected IncomingOrderService $incomingOrderService;

    public function __construct(IncomingOrderService $incomingOrderService)
    {
        $this->incomingOrderService = $incomingOrderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try {
            $offers = $this->incomingOrderService->index($request);
            return response()->json([
                'success' => true,
                'data' => IncomingOrderResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $order = $this->incomingOrderService->store($request);
            return response()->json([
                'success' => true,
                'data' => new IncomingOrderResource($order)
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
