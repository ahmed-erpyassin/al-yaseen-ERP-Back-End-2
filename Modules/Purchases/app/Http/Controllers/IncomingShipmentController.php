<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\IncomingShipmentService;
use Modules\Purchases\Http\Requests\IncomingShipmentRequest;
use Modules\Purchases\Transformers\IncomingShipmentResource;

class IncomingShipmentController extends Controller
{

    protected IncomingShipmentService $incomingShipmentService;

    public function __construct(IncomingShipmentService $incomingShipmentService)
    {
        $this->incomingShipmentService = $incomingShipmentService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->incomingShipmentService->index($request);
            return response()->json([
                'success' => true,
                'data' => IncomingShipmentResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomingShipmentRequest $request)
    {
        try {
            $shipment = $this->incomingShipmentService->store($request);
            return response()->json([
                'success' => true,
                'data' => new IncomingShipmentResource($shipment)
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
