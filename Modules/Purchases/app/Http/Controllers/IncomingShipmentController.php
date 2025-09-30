<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\IncomingShipmentService;
use Modules\Purchases\Http\Requests\IncomingShipmentRequest;
use Modules\Purchases\Transformers\IncomingShipmentResource;

/**
 * @group Purchase Management / Incoming Shipments
 *
 * APIs for managing incoming shipments from suppliers, including receipt, inspection, and inventory updates.
 */
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
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching incoming shipments.',
                'message' => $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating incoming shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified incoming shipment with all related data.
     * Returns shipment details with relationships loaded for comprehensive view.
     *
     * @param int $id Shipment ID
     * @return JsonResponse Shipment resource or error response
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
                'error' => 'An error occurred while fetching shipment details.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified incoming shipment in storage.
     * Updates shipment data with comprehensive validation and relationship handling.
     *
     * @param Request $request Request data
     * @param int $id Shipment ID
     * @return JsonResponse Updated shipment resource or error response
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
                'error' => 'An error occurred while updating shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified incoming shipment from storage (soft delete).
     * Performs soft delete with audit trail tracking who deleted the shipment.
     *
     * @param int $id Shipment ID
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
                'error' => 'An error occurred while deleting shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
