<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Http\Requests\StoreStockMovementRequest;

/**
 * @group Inventory Management / Stock Movements
 *
 * APIs for managing stock movements, including transfers, adjustments, and movement tracking.
 */
class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = StockMovement::with(['item', 'warehouse', 'user', 'unit', 'branch']);
            // ->forCompany($companyId);

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->get('movement_type'));
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->get('warehouse_id'));
        }

        if ($request->has('item_id')) {
            $query->where('item_id', $request->get('item_id'));
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->get('date_to'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $movements = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movements,
            'message' => 'Stock movements retrieved successfully'
        ]);
    }

    /**
     * Store a newly created stock movement.
     */
    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;
        $data['created_by'] = $userId;

        // Create the movement
        $movement = StockMovement::create($data);

        // Update inventory stock
        $this->updateInventoryStock($movement);

        $movement->load(['item', 'warehouse', 'user', 'unit', 'branch']);

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => 'Stock movement created successfully'
        ], 201);
    }

    /**
     * Display the specified stock movement.
     */
    public function show($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $movement = StockMovement::with(['item', 'warehouse', 'user', 'unit', 'branch'])
            // ->forCompany($companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => 'Stock movement retrieved successfully'
        ]);
    }

    /**
     * Get stock movements for a specific item.
     */
    public function byItem($itemId): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $movements = StockMovement::with(['warehouse', 'user', 'unit', 'branch'])
            // ->forCompany($companyId)
            ->where('item_id', $itemId)
            ->orderBy('transaction_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movements,
            'message' => 'Stock movements for item retrieved successfully'
        ]);
    }

    /**
     * Get stock movements for a specific warehouse.
     */
    public function byWarehouse($warehouseId): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $movements = StockMovement::with(['item', 'user', 'unit', 'branch'])
            // ->forCompany($companyId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('transaction_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movements,
            'message' => 'Stock movements for warehouse retrieved successfully'
        ]);
    }

    /**
     * Get stock summary for all items.
     */
    public function stockSummary(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $summary = InventoryStock::with(['inventoryItem', 'warehouse'])
            // ->forCompany($companyId)
            ->get()
            ->groupBy('inventory_item_id')
            ->map(function ($stocks) {
                return [
                    'item' => $stocks->first()->inventoryItem,
                    'total_quantity' => $stocks->sum('quantity'),
                    'total_reserved' => $stocks->sum('reserved_quantity'),
                    'total_available' => $stocks->sum('available_quantity'),
                    'warehouses' => $stocks->map(function ($stock) {
                        return [
                            'warehouse' => $stock->warehouse,
                            'quantity' => $stock->quantity,
                            'reserved' => $stock->reserved_quantity,
                            'available' => $stock->available_quantity,
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $summary->values(),
            'message' => 'Stock summary retrieved successfully'
        ]);
    }

    /**
     * Update inventory stock based on movement.
     */
    private function updateInventoryStock(StockMovement $movement)
    {
        $stock = InventoryStock::firstOrCreate([
            'company_id' => $movement->company_id,
            'inventory_item_id' => $movement->item_id,
            'warehouse_id' => $movement->warehouse_id,
        ], [
            'quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
        ]);

        if ($movement->movement_type === 'in') {
            $stock->quantity += $movement->quantity;
            $stock->available_quantity += $movement->quantity;
        } else {
            $stock->quantity -= $movement->quantity;
            $stock->available_quantity -= $movement->quantity;
        }

        $stock->save();
    }

    /**
     * ? Update the specified stock movement.
     *
     * Update an existing stock movement with new data.
     */
    public function update(StoreStockMovementRequest $request, $id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        $movement = StockMovement::findOrFail($id);
        // StockMovement::forCompany($companyId)->findOrFail($id);

        $data = $request->validated();
        $data['updated_by'] = $userId;

        $movement->update($data);

        return response()->json([
            'success' => true,
            'data' => $movement->load(['item', 'warehouse', 'user', 'unit', 'branch']),
            'message' => 'Stock movement updated successfully',
            'message_ar' => 'تم تحديث حركة المخزون بنجاح'
        ]);
    }

    /**
     * ? Remove the specified stock movement (soft delete).
     *
     * Soft delete a stock movement, marking it as deleted while preserving the record.
     */
    public function destroy($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;
        $userId = Auth::id() ?? request()->user_id;

        $movement = StockMovement::findOrFail($id);
        // StockMovement::forCompany($companyId)->findOrFail($id);

        // Set deleted_by before soft delete
        $movement->update(['deleted_by' => $userId]);
        $movement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stock movement deleted successfully',
            'message_ar' => 'تم حذف حركة المخزون بنجاح'
        ]);
    }

    /**
     * ? Get trashed (soft deleted) stock movements.
     *
     * Retrieve all soft deleted stock movements with search and pagination support.
     */
    public function trashed(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = StockMovement::onlyTrashed()
            ->with(['item', 'warehouse', 'user', 'unit', 'branch', 'deleter']);
            // ->forCompany($companyId);

        // Apply search to trashed items
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 15);
        $movements = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movements,
            'message' => 'Trashed stock movements retrieved successfully',
            'message_ar' => 'تم استرداد حركات المخزون المحذوفة بنجاح'
        ]);
    }

    /**
     * ? Restore a soft deleted stock movement.
     *
     * Restore a previously soft deleted stock movement back to active status.
     */
    public function restore($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;
        $userId = Auth::id() ?? request()->user_id;

        $movement = StockMovement::onlyTrashed()
            // ->forCompany($companyId)
            ->findOrFail($id);

        if (!$movement->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Stock movement is not deleted',
                'message_ar' => 'حركة المخزون غير محذوفة'
            ], 422);
        }

        // Clear deleted_by and restore
        $movement->update([
            'deleted_by' => null,
            'updated_by' => $userId
        ]);
        $movement->restore();

        return response()->json([
            'success' => true,
            'message' => 'Stock movement restored successfully',
            'message_ar' => 'تم استعادة حركة المخزون بنجاح',
            'data' => $movement->load(['item', 'warehouse', 'user', 'unit', 'branch'])
        ]);
    }

    /**
     * ? Permanently delete a stock movement (force delete).
     *
     * Permanently remove a stock movement from the database. This action cannot be undone.
     */
    public function forceDelete($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $movement = StockMovement::onlyTrashed()
            // ->forCompany($companyId)
            ->findOrFail($id);

        $movement->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Stock movement permanently deleted',
            'message_ar' => 'تم حذف حركة المخزون نهائياً'
        ]);
    }
}
