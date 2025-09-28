<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Models\ItemType;

/**
 * @group Inventory Management / Item Types
 *
 * APIs for managing item types, categories, and item classification.
 */
class ItemTypeController extends Controller
{
    /**
     * Display a listing of item types.
     */
    public function index(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = ItemType::
        // forCompany($companyId)->
        active();

        // Apply search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            if ($request->type === 'system') {
                $query->system();
            } elseif ($request->type === 'custom') {
                $query->custom();
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $itemTypes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $itemTypes,
            'message' => 'Item types retrieved successfully',
            'message_ar' => 'تم استرداد أنواع الأصناف بنجاح'
        ]);
    }

    /**
     * Store a newly created item type.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
        ]);

        try {
            $itemType = ItemType::createCustomType(
                $companyId,
                $request->name,
                $request->name_ar
            );

            if ($request->description) {
                $itemType->update([
                    'description' => $request->description,
                    'description_ar' => $request->description_ar ?? $request->description,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $itemType,
                'message' => 'Item type created successfully',
                'message_ar' => 'تم إنشاء نوع الصنف بنجاح'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item type: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء نوع الصنف: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified item type.
     */
    public function show($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;
        $itemType = ItemType::
        // forCompany($companyId)->
        findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $itemType,
            'message' => 'Item type retrieved successfully',
            'message_ar' => 'تم استرداد نوع الصنف بنجاح'
        ]);
    }

    /**
     * Update the specified item type.
     */
    public function update(Request $request, $id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;
        $itemType = ItemType::
        // forCompany($companyId)->
        findOrFail($id);

        // Prevent updating system types
        if ($itemType->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update system item type',
                'message_ar' => 'لا يمكن تحديث نوع الصنف النظامي'
            ], 422);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $itemType->update($request->only([
            'name', 'name_ar', 'description', 'description_ar', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'data' => $itemType,
            'message' => 'Item type updated successfully',
            'message_ar' => 'تم تحديث نوع الصنف بنجاح'
        ]);
    }

    /**
     * Remove the specified item type.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;
        $itemType = ItemType::forCompany($companyId)->findOrFail($id);

        // Prevent deleting system types
        if ($itemType->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system item type',
                'message_ar' => 'لا يمكن حذف نوع الصنف النظامي'
            ], 422);
        }      

        // Check if item type is in use
        if ($itemType->items()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete item type that is in use',
                'message_ar' => 'لا يمكن حذف نوع الصنف المستخدم'
            ], 422);
        }

        $itemType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item type deleted successfully',
            'message_ar' => 'تم حذف نوع الصنف بنجاح'
        ]);
    }

    /**
     * Get item type options for dropdown.
     */
    public function getOptions(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $itemTypes = ItemType::
        // forCompany($companyId)->
        active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'code', 'name', 'name_ar', 'is_system']);

        $options = $itemTypes->map(function ($type) {
            return [
                'value' => $type->code,
                'label' => $type->display_name,
                'id' => $type->id,
                'is_system' => $type->is_system,
            ];
        });

        // Add system types as base options
        $systemTypes = collect(ItemType::getSystemTypes())->map(function ($nameAr, $code) {
            return [
                'value' => $code,
                'label' => $nameAr,
                'is_system' => true,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $options,
            'system_types' => $systemTypes,
            'message' => 'Item type options retrieved successfully',
            'message_ar' => 'تم استرداد خيارات أنواع الأصناف بنجاح'
        ]);
    }
}
