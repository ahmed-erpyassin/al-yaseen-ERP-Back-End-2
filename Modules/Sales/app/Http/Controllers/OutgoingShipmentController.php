<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\OutgoingShipmentService;
use Modules\Sales\Http\Requests\OutgoingShipmentRequest;
use Modules\Sales\Transformers\OutgoingOfferResource;
use Modules\Sales\Transformers\OutgoingShipmentResource;

class OutgoingShipmentController extends Controller
{

    protected OutgoingShipmentService $outgoingShipmentService;

    public function __construct(OutgoingShipmentService $outgoingShipmentService)
    {
        $this->outgoingShipmentService = $outgoingShipmentService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->outgoingShipmentService->index($request);
            return response()->json([
                'success' => true,
                'data' => OutgoingShipmentResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OutgoingShipmentRequest $request)
    {
        try {
            $shipment = $this->outgoingShipmentService->store($request);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOfferResource($shipment)
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
