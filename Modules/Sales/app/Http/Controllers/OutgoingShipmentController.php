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
        try {
            $shipment = $this->outgoingShipmentService->show($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingShipmentResource($shipment),
                'message' => 'Outgoing shipment retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching outgoing shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OutgoingShipmentRequest $request, $id)
    {
        try {
            $shipment = $this->outgoingShipmentService->update($request, $id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingShipmentResource($shipment),
                'message' => 'Outgoing shipment updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating outgoing shipment.',
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
            $this->outgoingShipmentService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Outgoing shipment deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting outgoing shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
