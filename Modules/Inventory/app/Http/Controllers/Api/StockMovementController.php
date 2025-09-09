<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Http\Requests\StoreStockMovementRequest;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        
        $query = StockMovement::with(['item', 'warehouse', 'user', 'unit', 'branch'])
            ->forCompany($companyId);

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
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;
        
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
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $movement = StockMovement::with(['item', 'warehouse', 'user', 'unit', 'branch'])
            ->forCompany($companyId)
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
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $movements = StockMovement::with(['warehouse', 'user', 'unit', 'branch'])
            ->forCompany($companyId)
            ->forItem($itemId)
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
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $movements = StockMovement::with(['item', 'user', 'unit', 'branch'])
            ->forCompany($companyId)
            ->forWarehouse($warehouseId)
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
        $companyId = auth()->user()->company_id ?? $request->company_id;
        
        $summary = InventoryStock::with(['inventoryItem', 'warehouse'])
            ->forCompany($companyId)
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
}
