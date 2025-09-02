<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\BomItem;
use Modules\Inventory\Http\Requests\StoreBomItemRequest;
use Modules\Inventory\Http\Requests\UpdateBomItemRequest;

class BomItemController extends Controller
{
    /**
     * Display a listing of BOM items.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        
        $query = BomItem::with(['company', 'branch', 'user', 'item', 'component', 'unit'])
            ->forCompany($companyId);

        // Apply filters
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        if ($request->has('item_id')) {
            $query->where('item_id', $request->get('item_id'));
        }

        if ($request->has('component_id')) {
            $query->where('component_id', $request->get('component_id'));
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->get('unit_id'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'quantity');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $bomItems = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bomItems,
            'message' => 'BOM items retrieved successfully'
        ]);
    }

    /**
     * Store a newly created BOM item.
     */
    public function store(StoreBomItemRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;
        
        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;
        $data['created_by'] = $userId;

        $bomItem = BomItem::create($data);
        $bomItem->load(['company', 'branch', 'user', 'item', 'component', 'unit']);

        return response()->json([
            'success' => true,
            'data' => $bomItem,
            'message' => 'BOM item created successfully'
        ], 201);
    }

    /**
     * Display the specified BOM item.
     */
    public function show($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $bomItem = BomItem::with(['company', 'branch', 'user', 'item', 'component', 'unit'])
            ->forCompany($companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $bomItem,
            'message' => 'BOM item retrieved successfully'
        ]);
    }

    /**
     * Update the specified BOM item.
     */
    public function update(UpdateBomItemRequest $request, $id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;
        
        $bomItem = BomItem::forCompany($companyId)->findOrFail($id);
        
        $data = $request->validated();
        $data['updated_by'] = $userId;
        
        $bomItem->update($data);
        $bomItem->load(['company', 'branch', 'user', 'item', 'component', 'unit']);

        return response()->json([
            'success' => true,
            'data' => $bomItem,
            'message' => 'BOM item updated successfully'
        ]);
    }

    /**
     * Remove the specified BOM item.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $bomItem = BomItem::forCompany($companyId)->findOrFail($id);
        $bomItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'BOM item deleted successfully'
        ]);
    }

    /**
     * Get BOM items for a specific item (Bill of Materials).
     */
    public function byItem($itemId): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $bomItems = BomItem::with(['component', 'unit'])
            ->forCompany($companyId)
            ->forItem($itemId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bomItems,
            'message' => 'BOM items for item retrieved successfully'
        ]);
    }

    /**
     * Get items that use a specific component.
     */
    public function byComponent($componentId): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $bomItems = BomItem::with(['item', 'unit'])
            ->forCompany($companyId)
            ->forComponent($componentId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bomItems,
            'message' => 'Items using this component retrieved successfully'
        ]);
    }

    /**
     * Calculate material requirements for production.
     */
    public function calculateRequirements(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'production_quantity' => 'required|numeric|min:0.01'
        ]);

        $companyId = auth()->user()->company_id ?? $request->company_id;
        $itemId = $request->get('item_id');
        $productionQuantity = $request->get('production_quantity');
        
        $bomItems = BomItem::with(['component', 'unit'])
            ->forCompany($companyId)
            ->forItem($itemId)
            ->get();

        $requirements = $bomItems->map(function ($bomItem) use ($productionQuantity) {
            return [
                'component_id' => $bomItem->component_id,
                'component_name' => $bomItem->component->name,
                'component_code' => $bomItem->component->code,
                'unit_name' => $bomItem->unit->name,
                'unit_quantity' => $bomItem->quantity,
                'total_quantity' => $bomItem->calculateTotalQuantity($productionQuantity),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'item_id' => $itemId,
                'production_quantity' => $productionQuantity,
                'requirements' => $requirements
            ],
            'message' => 'Material requirements calculated successfully'
        ]);
    }
}
