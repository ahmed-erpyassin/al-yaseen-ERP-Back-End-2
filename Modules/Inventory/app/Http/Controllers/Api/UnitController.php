<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Http\Requests\StoreUnitRequest;
use Modules\Inventory\Http\Requests\UpdateUnitRequest;

/**
 * @group Inventory Management / Units
 *
 * APIs for managing units of measure, including creation, updates, and unit conversions.
 */
class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $companyId = $user->company?->id ?? $request->company_id;

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company ID is required. User must have a company or provide company_id in request.'
            ], 400);
        }

        $query = Unit::with([
            'company',
            'branch',
            'user',
            'defaultHandlingUnit:id,name,symbol',
            'defaultWarehouse:id,name,warehouse_number'
        ])
        ->forCompany($companyId);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $units = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $units,
            'message' => 'Units retrieved successfully'
        ]);
    }

    /**
     * Store a newly created unit.
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        $user = Auth::user();
        $companyId = $user->company?->id ?? $request->company_id;
        $userId = $user->id ?? $request->user_id;

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company ID is required. User must have a company or provide company_id in request.'
            ], 400);
        }

        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;
        $data['created_by'] = $userId;

        $unit = Unit::create($data);
        $unit->load(['company', 'branch', 'user']);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit created successfully'
        ], 201);
    }

    /**
     * Display the specified unit.
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $companyId = $user->company?->id ?? request()->company_id;

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Company ID is required. User must have a company or provide company_id in request.'
            ], 400);
        }

        $unit = Unit::with(['company', 'branch', 'user', 'items', 'itemUnits'])
            ->forCompany($companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit retrieved successfully'
        ]);
    }

    /**
     * Update the specified unit.
     */
    public function update(UpdateUnitRequest $request, $id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        $unit = Unit::
        // forCompany($companyId)->
        findOrFail($id);

        $data = $request->validated();
        $data['updated_by'] = $userId;

        $unit->update($data);
        $unit->load(['company', 'branch', 'user']);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit updated successfully'
        ]);
    }

    /**
     * Remove the specified unit.
     */
    public function destroy($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $unit = Unit::
        // forCompany($companyId)->
        findOrFail($id);

        // Check if unit has items
        if ($unit->items()->exists() || $unit->itemUnits()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete unit with existing items or item units'
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully'
        ]);
    }

    /**
     * Get the first unit.
     */
    public function first(): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $unit = Unit::with(['company', 'branch', 'user'])
            // ->forCompany($companyId)
            ->orderBy('name')
            ->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'No units found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'First unit retrieved successfully'
        ]);
    }

    /**
     * Get the last unit.
     */
    public function last(): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $unit = Unit::with(['company', 'branch', 'user'])
            // ->forCompany($companyId)
            ->orderBy('name', 'desc')
            ->first();

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'No units found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Last unit retrieved successfully'
        ]);
    }

    /**
     * Get predefined unit options.
     */
    public function getUnitOptions(): JsonResponse
    {
        $options = Unit::UNIT_OPTIONS;

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Unit options retrieved successfully',
            'message_ar' => 'تم استرداد خيارات الوحدات بنجاح'
        ]);
    }

    /**
     * Get all unit options including custom ones.
     */
    public function getAllUnitOptions(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $options = Unit::getAllUnitOptions($companyId);
        // $options = Unit::getAllUnitOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'All unit options retrieved successfully',
            'message_ar' => 'تم استرداد جميع خيارات الوحدات بنجاح'
        ]);
    }

    /**
     * Get contains options.
     */
    public function getContainsOptions(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $options = Unit::getAllContainsOptions($companyId);
        // $options = Unit::getAllContainsOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Contains options retrieved successfully',
            'message_ar' => 'تم استرداد خيارات المحتويات بنجاح'
        ]);
    }

    /**
     * Get units for dropdown selection.
     */
    public function getUnitsForDropdown(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $units = Unit::
        // forCompany($companyId)->
        where('status', 'active')
            ->select('id', 'name', 'symbol', 'code')
            ->orderBy('name')
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'display_name' => $unit->name . ($unit->symbol ? " ({$unit->symbol})" : ''),
                    'symbol' => $unit->symbol,
                    'code' => $unit->code,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $units,
            'message' => 'Units for dropdown retrieved successfully',
            'message_ar' => 'تم استرداد الوحدات للقائمة المنسدلة بنجاح'
        ]);
    }

    /**
     * Get warehouses for dropdown selection.
     */
    public function getWarehousesForDropdown(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $warehouses = \Modules\Inventory\Models\Warehouse::
            // forCompany($companyId)->
            select('id', 'name', 'warehouse_number', 'address', 'status')
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'display_name' => $warehouse->name . ($warehouse->warehouse_number ? " ({$warehouse->warehouse_number})" : ''),
                    'warehouse_number' => $warehouse->warehouse_number,
                    'address' => $warehouse->address,
                    'status' => $warehouse->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $warehouses,
            'message' => 'Warehouses for dropdown retrieved successfully',
            'message_ar' => 'تم استرداد المخازن للقائمة المنسدلة بنجاح'
        ]);
    }

    /**
     * Get comprehensive unit data for form.
     */
    public function getUnitFormData(Request $request): JsonResponse
    {
        $data = [
            'unit_options' => Unit::UNIT_OPTIONS,
            'contains_options' => Unit::CONTAINS_OPTIONS,
            'available_units' => Unit::where('status', 'active')
                ->select('id', 'name', 'symbol')
                ->get(),
            'available_warehouses' => \Modules\Inventory\Models\Warehouse::where('status', 'active')
                ->select('id', 'name', 'warehouse_number', 'address')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Unit form data retrieved successfully',
            'message_ar' => 'تم استرداد بيانات نموذج الوحدة بنجاح'
        ]);
    }
}
