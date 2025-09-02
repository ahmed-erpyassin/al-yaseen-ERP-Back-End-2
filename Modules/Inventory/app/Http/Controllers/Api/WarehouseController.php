<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Http\Requests\StoreWarehouseRequest;
use Modules\Inventory\Http\Requests\UpdateWarehouseRequest;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        
        $query = Warehouse::with(['company', 'branch', 'user', 'departmentWarehouse'])
            ->forCompany($companyId);

        // Apply filters
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        if ($request->has('department_warehouse_id')) {
            $query->where('department_warehouse_id', $request->get('department_warehouse_id'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $warehouses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $warehouses,
            'message' => 'Warehouses retrieved successfully'
        ]);
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;

        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;

        $warehouse = Warehouse::create($data);
        $warehouse->load(['company', 'branch', 'user', 'departmentWarehouse']);

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'Warehouse created successfully'
        ], 201);
    }

    /**
     * Display the specified warehouse.
     */
    public function show($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $warehouse = Warehouse::with(['company', 'stock.inventoryItem'])
            ->forCompany($companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'Warehouse retrieved successfully'
        ]);
    }

    /**
     * Update the specified warehouse.
     */
    public function update(UpdateWarehouseRequest $request, $id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;

        $warehouse = Warehouse::forCompany($companyId)->findOrFail($id);

        $data = $request->validated();
        $data['user_id'] = $userId;

        $warehouse->update($data);
        $warehouse->load(['company', 'branch', 'user', 'departmentWarehouse']);

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'Warehouse updated successfully'
        ]);
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $warehouse = Warehouse::forCompany($companyId)->findOrFail($id);
        
        // Check if warehouse has stock
        if ($warehouse->stock()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete warehouse with existing stock'
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully'
        ]);
    }

    /**
     * Get the first warehouse.
     */
    public function first(): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $warehouse = Warehouse::with(['company', 'branch', 'user', 'departmentWarehouse'])
            ->forCompany($companyId)
            ->orderBy('name')
            ->first();

        if (!$warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouses found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'First warehouse retrieved successfully'
        ]);
    }

    /**
     * Get the last warehouse.
     */
    public function last(): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        
        $warehouse = Warehouse::with(['company', 'branch', 'user', 'departmentWarehouse'])
            ->forCompany($companyId)
            ->orderBy('name', 'desc')
            ->first();

        if (!$warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouses found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'Last warehouse retrieved successfully'
        ]);
    }
}
