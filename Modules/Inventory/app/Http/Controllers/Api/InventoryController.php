<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Http\Resources\InventoryResource;
use Modules\Inventory\Http\Requests\StoreInventoryItemRequest;
use Modules\Inventory\Http\Requests\UpdateInventoryItemRequest;

/**
 * @group Inventory Management / Inventory Items
 *
 * APIs for managing inventory items, stock levels, and warehouse operations.
 */

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * List Inventory Items
     *
     * Retrieve a paginated list of inventory items with filtering and search capabilities.
     *
     * @queryParam active boolean Filter by active status. Example: true
     * @queryParam category_id integer Filter by category ID. Example: 1
     * @queryParam supplier_id integer Filter by supplier ID. Example: 1
     * @queryParam search string Search across item names and descriptions. Example: laptop
     * @queryParam sort_by string Field to sort by. Example: name
     * @queryParam sort_direction string Sort direction (asc/desc). Example: asc
     * @queryParam per_page integer Number of items per page (default: 15). Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Laptop Dell XPS 13",
     *       "sku": "DELL-XPS-13",
     *       "category": "Electronics",
     *       "quantity": 50,
     *       "unit_price": 1200.00,
     *       "supplier": "Dell Inc.",
     *       "active": true,
     *       "created_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ],
     *   "message": "Inventory items retrieved successfully"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving inventory items: Database connection failed"
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

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
            $user = Auth::user();

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
            $user = Auth::user();

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
            $user = Auth::user();

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
            $user = Auth::user();

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
     * ! Get low stock items.
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
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
     * ! Get items that need reordering.
     */
    public function reorderItems(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
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

    /**
     * ! Get first inventory item.
     */
    public function first(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $sortBy = $request->get('sort_by', 'created_at');

            // Get first inventory item using service
            $item = $this->inventoryService->getFirstInventoryItem($user, $sortBy);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'No inventory items found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new InventoryResource($item),
                'message' => 'First inventory item retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving first inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ! Get last inventory item.
     */
    public function last(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $sortBy = $request->get('sort_by', 'created_at');

            // Get last inventory item using service
            $item = $this->inventoryService->getLastInventoryItem($user, $sortBy);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'No inventory items found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new InventoryResource($item),
                'message' => 'Last inventory item retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving last inventory item: ' . $e->getMessage()
            ], 500);
        }
    }
}
