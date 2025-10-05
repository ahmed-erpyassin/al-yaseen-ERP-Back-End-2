<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\OutgoingShipmentService;
use Modules\Sales\Http\Requests\OutgoingShipmentRequest;
use Modules\Sales\Transformers\OutgoingShipmentResource;

/**
 * @group Sales Management / Outgoing Shipments
 *
 * APIs for managing outgoing shipments, including shipment creation, tracking, and delivery management.
 */
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
            $shipments = $this->outgoingShipmentService->index($request);
            return response()->json([
                'success' => true,
                'data' => OutgoingShipmentResource::collection($shipments->items()),
                'pagination' => [
                    'current_page' => $shipments->currentPage(),
                    'last_page' => $shipments->lastPage(),
                    'per_page' => $shipments->perPage(),
                    'total' => $shipments->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching outgoing shipments.',
                'message' => $e->getMessage()
            ], 500);
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
                'data' => new OutgoingShipmentResource($shipment),
                'message' => 'Outgoing shipment created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating outgoing shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $shipment = $this->outgoingShipmentService->show($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingShipmentResource($shipment)
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
                'message' => 'Outgoing shipment updated successfully.'
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
     * Remove the specified resource from storage (soft delete)
     */
    public function destroy($id)
    {
        try {
            $result = $this->outgoingShipmentService->destroy($id);
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Outgoing shipment deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting outgoing shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted outgoing shipment
     */
    public function restore($id)
    {
        try {
            $shipment = $this->outgoingShipmentService->restore($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingShipmentResource($shipment),
                'message' => 'Outgoing shipment restored successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring outgoing shipment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for customers
     */
    public function searchCustomers(Request $request)
    {
        try {
            $customers = $this->outgoingShipmentService->searchCustomers($request);
            return response()->json([
                'success' => true,
                'data' => $customers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching customers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for items
     */
    public function searchItems(Request $request)
    {
        try {
            $items = $this->outgoingShipmentService->searchItems($request);
            return response()->json([
                'success' => true,
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching items.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview/Display complete outgoing shipment data with all relationships
     */
    public function preview($id)
    {
        try {
            $shipment = $this->outgoingShipmentService->show($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingShipmentResource($shipment),
                'message' => 'Outgoing shipment preview retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching outgoing shipment preview.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for creating/editing outgoing shipments
     */
    public function getFormData()
    {
        try {
            $formData = $this->outgoingShipmentService->getFormData();
            return response()->json([
                'success' => true,
                'data' => $formData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
