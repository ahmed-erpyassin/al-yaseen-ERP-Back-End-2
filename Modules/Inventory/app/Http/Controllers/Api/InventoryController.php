<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Http\Resources\InventoryResource;
use Modules\Inventory\Http\Requests\StoreInventoryItemRequest;
use Modules\Inventory\Http\Requests\UpdateInventoryItemRequest;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get filters from request
            $filters = $request->only([
                'active', 'category_id', 'supplier_id', 'search',
                'sort_by', 'sort_direction'
            ]);

            $perPage = $request->get('per_page', 15);

            // Get inventory items using service
            $items = $this->inventoryService->getInventoryItems($user, $filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => InventoryResource::collection($items),
                'message' => 'Inventory items retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving inventory items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            // Create inventory item using service
            $item = $this->inventoryService->createInventoryItem($data, $user);

            return response()->json([
                'success' => true,
                'data' => new InventoryResource($item),
                'message' => 'Inventory item created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified inventory item.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();

            // Get inventory item using service
            $item = $this->inventoryService->getInventoryItemById($id, $user);

            return response()->json([
                'success' => true,
                'data' => new InventoryResource($item),
                'message' => 'Inventory item retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving inventory item: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified inventory item.
     */
    public function update(UpdateInventoryItemRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            // Update inventory item using service
            $item = $this->inventoryService->updateInventoryItem($id, $data, $user);
            return response()->json([
                'success' => true,
                'data' => new InventoryResource($item),
                'message' => 'Inventory item updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified inventory item.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();

            // Delete inventory item using service
            $this->inventoryService->deleteInventoryItem($id, $user);

            return response()->json([
                'success' => true,
                'message' => 'Inventory item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock items.
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);

            // Get low stock items using service
            $items = $this->inventoryService->getLowStockItems($user, $perPage);

            return response()->json([
                'success' => true,
                'data' => InventoryResource::collection($items),
                'message' => 'Low stock items retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving low stock items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items that need reordering.
     */
    public function reorderItems(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);

            // Get reorder items using service
            $items = $this->inventoryService->getReorderItems($user, $perPage);

            return response()->json([
                'success' => true,
                'data' => InventoryResource::collection($items),
                'message' => 'Items needing reorder retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reorder items: ' . $e->getMessage()
            ], 500);
        }
    }
}
