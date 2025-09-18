<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\ItemUnit;
use Modules\Inventory\Http\Requests\StoreItemUnitRequest;
use Modules\Inventory\Http\Requests\UpdateItemUnitRequest;

/**
 * @group Inventory Management / Item Units
 *
 * APIs for managing item unit relationships, conversions, and unit-specific operations.
 */
class ItemUnitController extends Controller
{
    /**
     * Display a listing of item units.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $query = ItemUnit::with(['company', 'branch', 'user', 'item', 'unit'])
            ->forCompany($companyId);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        if ($request->has('item_id')) {
            $query->where('item_id', $request->get('item_id'));
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->get('unit_id'));
        }

        if ($request->has('is_default')) {
            $query->where('is_default', $request->boolean('is_default'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'conversion_rate');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $itemUnits = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $itemUnits,
            'message' => 'Item units retrieved successfully'
        ]);
    }

    /**
     * Store a newly created item unit.
     */
    public function store(StoreItemUnitRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;

        $data = $request->validated();
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;
        $data['created_by'] = $userId;

        // If this is set as default, unset other defaults for the same item
        if ($data['is_default']) {
            ItemUnit::where('item_id', $data['item_id'])
                ->where('company_id', $companyId)
                ->update(['is_default' => false]);
        }

        $itemUnit = ItemUnit::create($data);
        $itemUnit->load(['company', 'branch', 'user', 'item', 'unit']);

        return response()->json([
            'success' => true,
            'data' => $itemUnit,
            'message' => 'Item unit created successfully'
        ], 201);
    }

    /**
     * Display the specified item unit.
     */
    public function show($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $itemUnit = ItemUnit::with(['company', 'branch', 'user', 'item', 'unit'])
            ->forCompany($companyId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $itemUnit,
            'message' => 'Item unit retrieved successfully'
        ]);
    }

    /**
     * Update the specified item unit.
     */
    public function update(UpdateItemUnitRequest $request, $id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;

        $itemUnit = ItemUnit::forCompany($companyId)->findOrFail($id);

        $data = $request->validated();
        $data['updated_by'] = $userId;

        // If this is set as default, unset other defaults for the same item
        if ($data['is_default']) {
            ItemUnit::where('item_id', $itemUnit->item_id)
                ->where('company_id', $companyId)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $itemUnit->update($data);
        $itemUnit->load(['company', 'branch', 'user', 'item', 'unit']);

        return response()->json([
            'success' => true,
            'data' => $itemUnit,
            'message' => 'Item unit updated successfully'
        ]);
    }

    /**
     * Remove the specified item unit.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $itemUnit = ItemUnit::forCompany($companyId)->findOrFail($id);

        // Don't allow deletion of default unit if it's the only one
        if ($itemUnit->is_default) {
            $otherUnits = ItemUnit::where('item_id', $itemUnit->item_id)
                ->where('company_id', $companyId)
                ->where('id', '!=', $id)
                ->count();

            if ($otherUnits == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the only unit for this item'
                ], 422);
            }
        }

        $itemUnit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item unit deleted successfully'
        ]);
    }

    /**
     * Get item units for a specific item.
     */
    public function byItem($itemId): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $itemUnits = ItemUnit::with(['unit'])
            ->forCompany($companyId)
            ->forItem($itemId)
            ->active()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $itemUnits,
            'message' => 'Item units for item retrieved successfully'
        ]);
    }

    /**
     * Set default unit for an item.
     */
    public function setDefault($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        $userId = auth()->id() ?? request()->user_id;

        $itemUnit = ItemUnit::forCompany($companyId)->findOrFail($id);

        // Unset other defaults for the same item
        ItemUnit::where('item_id', $itemUnit->item_id)
            ->where('company_id', $companyId)
            ->update(['is_default' => false]);

        // Set this as default
        $itemUnit->update([
            'is_default' => true,
            'updated_by' => $userId
        ]);

        return response()->json([
            'success' => true,
            'data' => $itemUnit,
            'message' => 'Default unit set successfully'
        ]);
    }

    /**
     * Get item units by type.
     */
    public function getByType(Request $request, $itemId, $type): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $itemUnits = ItemUnit::with(['unit:id,name,symbol', 'item:id,name'])
            ->forCompany($companyId)
            ->forItem($itemId)
            ->byType($type)
            ->active()
            ->get()
            ->map(function ($itemUnit) {
                return $itemUnit->full_details;
            });

        return response()->json([
            'success' => true,
            'data' => $itemUnits,
            'message' => 'Item units by type retrieved successfully',
            'message_ar' => 'تم استرداد وحدات الصنف حسب النوع بنجاح'
        ]);
    }

    /**
     * Get comprehensive item unit data.
     */
    public function getComprehensiveData(Request $request, $itemId): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $itemUnits = ItemUnit::with(['unit:id,name,symbol', 'item:id,name'])
            ->forCompany($companyId)
            ->forItem($itemId)
            ->active()
            ->get();

        $data = [
            'balance_units' => $itemUnits->where('unit_type', 'balance')->map(function ($unit) {
                return $unit->full_details;
            })->values(),
            'second_units' => $itemUnits->where('unit_type', 'second')->map(function ($unit) {
                return $unit->full_details;
            })->values(),
            'third_units' => $itemUnits->where('unit_type', 'third')->map(function ($unit) {
                return $unit->full_details;
            })->values(),
            'default_unit' => $itemUnits->where('is_default', true)->first()?->full_details,
            'total_units' => $itemUnits->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Comprehensive item unit data retrieved successfully',
            'message_ar' => 'تم استرداد البيانات الشاملة لوحدات الصنف بنجاح'
        ]);
    }

    /**
     * Get unit type options.
     */
    public function getUnitTypeOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ItemUnit::UNIT_TYPE_OPTIONS,
            'message' => 'Unit type options retrieved successfully',
            'message_ar' => 'تم استرداد خيارات أنواع الوحدات بنجاح'
        ]);
    }

    /**
     * Get contains options for item units.
     */
    public function getItemUnitContainsOptions(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $options = ItemUnit::getAllContainsOptions($companyId);

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Item unit contains options retrieved successfully',
            'message_ar' => 'تم استرداد خيارات محتويات وحدات الأصناف بنجاح'
        ]);
    }

    /**
     * Calculate conversion between units.
     */
    public function calculateConversion(Request $request): JsonResponse
    {
        $request->validate([
            'from_unit_id' => 'required|exists:item_units,id',
            'to_unit_id' => 'required|exists:item_units,id',
            'quantity' => 'required|numeric|min:0',
        ]);

        $companyId = auth()->user()->company_id ?? $request->company_id;

        $fromUnit = ItemUnit::forCompany($companyId)->findOrFail($request->from_unit_id);
        $toUnit = ItemUnit::forCompany($companyId)->findOrFail($request->to_unit_id);

        // Ensure both units belong to the same item
        if ($fromUnit->item_id !== $toUnit->item_id) {
            return response()->json([
                'success' => false,
                'message' => 'Units must belong to the same item',
                'message_ar' => 'يجب أن تنتمي الوحدات لنفس الصنف'
            ], 422);
        }

        $quantity = $request->quantity;

        // Convert to base unit first, then to target unit
        $baseQuantity = $fromUnit->convertQuantityWithFactor($quantity, true);
        $convertedQuantity = $toUnit->convertQuantityWithFactor($baseQuantity, false);

        return response()->json([
            'success' => true,
            'data' => [
                'original_quantity' => $quantity,
                'original_unit' => $fromUnit->unit->name,
                'converted_quantity' => round($convertedQuantity, 6),
                'target_unit' => $toUnit->unit->name,
                'conversion_details' => [
                    'from_conversion_rate' => $fromUnit->conversion_rate,
                    'from_quantity_factor' => $fromUnit->quantity_factor,
                    'to_conversion_rate' => $toUnit->conversion_rate,
                    'to_quantity_factor' => $toUnit->quantity_factor,
                    'base_quantity' => $baseQuantity,
                ]
            ],
            'message' => 'Conversion calculated successfully',
            'message_ar' => 'تم حساب التحويل بنجاح'
        ]);
    }

    /**
     * Get item unit form data.
     */
    public function getFormData(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $data = [
            'unit_type_options' => ItemUnit::UNIT_TYPE_OPTIONS,
            'contains_options' => ItemUnit::getAllContainsOptions($companyId),
            'available_units' => \Modules\Inventory\Models\Unit::forCompany($companyId)
                ->where('status', 'active')
                ->select('id', 'name', 'symbol', 'code')
                ->get(),
            'available_items' => \Modules\Inventory\Models\Item::forCompany($companyId)
                ->where('active', true)
                ->select('id', 'name', 'item_number', 'code')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Item unit form data retrieved successfully',
            'message_ar' => 'تم استرداد بيانات نموذج وحدة الصنف بنجاح'
        ]);
    }
}
