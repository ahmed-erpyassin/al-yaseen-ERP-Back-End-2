<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Models\DepartmentWarehouse;
use Modules\Inventory\Http\Requests\StoreDepartmentWarehouseRequest;
use Modules\Inventory\Http\Requests\UpdateDepartmentWarehouseRequest;

/**
 * @group Inventory Management / Department Warehouses
 *
 * APIs for managing department-warehouse relationships and access control.
 */
class DepartmentWarehouseController extends Controller
{
    /**
     * Display a listing of department warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = DepartmentWarehouse::with(['company'])
            ->forCompany($companyId);

        // Apply filters
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('department_name_ar', 'like', "%{$search}%")
                  ->orWhere('department_name_en', 'like', "%{$search}%")
                  ->orWhere('department_number', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'department_name_ar');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $departments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $departments,
            'message' => 'Department warehouses retrieved successfully'
        ]);
    }

    /**
     * Store a newly created department warehouse.
     */
    public function store(StoreDepartmentWarehouseRequest $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['created_by'] = $userId;

        $department = DepartmentWarehouse::create($data);
        $department->load(['company']);

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department warehouse created successfully'
        ], 201);
    }

    /**
     * Display the specified department warehouse.
     */
    public function show($id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

        $department = DepartmentWarehouse::with(['company', 'warehouses'])
            ->forCompany($companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department warehouse retrieved successfully'
        ]);
    }

    /**
     * Update the specified department warehouse.
     */
    public function update(UpdateDepartmentWarehouseRequest $request, $id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        $department = DepartmentWarehouse::forCompany($companyId)->findOrFail($id);

        $data = $request->validated();
        $data['updated_by'] = $userId;

        $department->update($data);
        $department->load(['company']);

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department warehouse updated successfully'
        ]);
    }

    /**
     * Remove the specified department warehouse.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

        $department = DepartmentWarehouse::forCompany($companyId)->findOrFail($id);

        // Check if department has warehouses
        if ($department->warehouses()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with existing warehouses'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department warehouse deleted successfully'
        ]);
    }

    /**
     * Get the first department warehouse.
     */
    public function first(): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

        $department = DepartmentWarehouse::with(['company'])
            ->forCompany($companyId)
            ->orderBy('department_name_ar')
            ->first();

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'No department warehouses found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'First department warehouse retrieved successfully'
        ]);
    }

    /**
     * Get the last department warehouse.
     */
    public function last(): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

        $department = DepartmentWarehouse::with(['company'])
            ->forCompany($companyId)
            ->orderBy('department_name_ar', 'desc')
            ->first();

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'No department warehouses found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Last department warehouse retrieved successfully'
        ]);
    }
}
