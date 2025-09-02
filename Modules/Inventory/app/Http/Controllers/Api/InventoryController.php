<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\InventoryItem;
use Modules\Inventory\Http\Requests\StoreInventoryItemRequest;
use Modules\Inventory\Http\Requests\UpdateInventoryItemRequest;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $query = InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($companyId);

        // Apply filters
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_name_ar', 'like', "%{$search}%")
                  ->orWhere('item_name_en', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'item_name_ar');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $items = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'Inventory items retrieved successfully'
        ]);
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $data = $request->validated();
        $data['company_id'] = $companyId;

        $item = InventoryItem::create($data);
        $item->load(['company']);

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Inventory item created successfully'
        ], 201);
    }

    /**
     * Display the specified inventory item.
     */
    public function show($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $item = InventoryItem::with(['company', 'stock.warehouse', 'stockMovements.user'])
            ->forCompany($companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Inventory item retrieved successfully'
        ]);
    }

    /**
     * Update the specified inventory item.
     */
    public function update(UpdateInventoryItemRequest $request, $id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $item = InventoryItem::forCompany($companyId)->findOrFail($id);
        $item->update($request->validated());
        $item->load(['company']);

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Inventory item updated successfully'
        ]);
    }

    /**
     * Remove the specified inventory item.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $item = InventoryItem::forCompany($companyId)->findOrFail($id);

        // Check if item has stock or movements
        if ($item->stock()->exists() || $item->stockMovements()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete item with existing stock or movements'
            ], 422);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory item deleted successfully'
        ]);
    }

    /**
     * Get low stock items.
     */
    public function lowStock(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $items = InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($companyId)
            ->whereColumn('quantity', '<=', 'minimum_limit')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'Low stock items retrieved successfully'
        ]);
    }

    /**
     * Get items that need reordering.
     */
    public function reorderItems(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $items = InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($companyId)
            ->whereColumn('quantity', '<=', 'reorder_limit')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'Items needing reorder retrieved successfully'
        ]);
    }
}
