<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\BomItem;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Http\Requests\StoreBomItemRequest;
use Modules\Inventory\Http\Requests\UpdateBomItemRequest;
use Illuminate\Support\Facades\DB;

class BomItemController extends Controller
{
    /**
     * ✅ Display a listing of BOM items with enhanced search and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $query = BomItem::with([
            'company', 'branch', 'user', 'item', 'component', 'unit', 'preferredSupplier',
            'creator', 'updater'
        ])->forCompany($companyId);

        // ✅ Search functionality
        if ($request->has('search')) {
            $search = strtolower($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(formula_number) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(formula_name) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('item', function ($itemQuery) use ($search) {
                      $itemQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                               ->orWhereRaw('LOWER(item_number) LIKE ?', ["%{$search}%"]);
                  })
                  ->orWhereHas('component', function ($componentQuery) use ($search) {
                      $componentQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                                    ->orWhereRaw('LOWER(item_number) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        // ✅ Enhanced filters
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

        // ✅ Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // ✅ Filter by active items
        if ($request->has('active_only') && $request->boolean('active_only')) {
            $query->active();
        }

        // ✅ Filter by component type
        if ($request->has('component_type')) {
            $query->where('component_type', $request->get('component_type'));
        }

        // ✅ Filter by critical components
        if ($request->has('critical_only') && $request->boolean('critical_only')) {
            $query->critical();
        }

        // ✅ Filter by components needing reorder
        if ($request->has('needs_reorder') && $request->boolean('needs_reorder')) {
            $query->needsReorder();
        }

        // ✅ Filter by components with shortage
        if ($request->has('with_shortage') && $request->boolean('with_shortage')) {
            $query->withShortage();
        }

        // ✅ Filter by low stock components
        if ($request->has('low_stock') && $request->boolean('low_stock')) {
            $query->lowStock();
        }

        // ✅ Filter by date range
        if ($request->has('date_from')) {
            $query->where('formula_date', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('formula_date', '<=', $request->get('date_to'));
        }

        // ✅ Enhanced sorting
        $sortBy = $request->get('sort_by', 'sequence_order');
        $sortDirection = $request->get('sort_direction', 'asc');

        $sortableColumns = [
            'id', 'formula_number', 'formula_name', 'item_id', 'component_id',
            'quantity', 'required_quantity', 'unit_cost', 'total_cost', 'sequence_order',
            'component_type', 'is_critical', 'status', 'formula_date', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered(); // Use the ordered scope
        }

        // ✅ Pagination
        $perPage = $request->get('per_page', 15);
        $bomItems = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bomItems,
            'message' => 'BOM items retrieved successfully',
            'message_ar' => 'تم استرداد عناصر قائمة المواد بنجاح'
        ]);
    }

    /**
     * ✅ Store a newly created BOM item with all Manufacturing Formula fields.
     */
    public function store(StoreBomItemRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;

        try {
            DB::beginTransaction();

            // ✅ Get validated data
            $data = $request->validated();

            // ✅ Set system fields
            $data['company_id'] = $companyId;
            $data['user_id'] = $userId;
            $data['created_by'] = $userId;

            // ✅ Auto-generate formula number if not provided
            if (empty($data['formula_number'])) {
                $data['formula_number'] = $this->generateFormulaNumber($companyId);
            }

            // ✅ Set automatic date and time on insert
            $data['formula_date'] = now()->toDateString();
            $data['formula_time'] = now()->toTimeString();
            $data['formula_datetime'] = now();

            // ✅ Get additional item information (non-redundant fields only)
            $item = Item::find($data['item_id']);
            if ($item) {
                $data['balance'] = $item->balance ?? 0;
                $data['minimum_limit'] = $item->minimum_limit ?? 0;
                $data['maximum_limit'] = $item->maximum_limit ?? 0;
                $data['minimum_reorder_level'] = $item->minimum_reorder_level ?? 0;

                // ✅ Get selling/purchase prices
                $data['selling_price'] = $item->selling_price ?? 0;
                $data['purchase_price'] = $item->purchase_price ?? 0;

                // ✅ Get historical prices from invoices (placeholder)
                $this->setHistoricalPrices($data, $item->id);
            }

            // ✅ Get component item information (non-redundant fields only)
            $component = Item::find($data['component_id']);
            if ($component) {
                $data['component_balance'] = $component->balance ?? 0;
                $data['component_minimum_limit'] = $component->minimum_limit ?? 0;
                $data['component_maximum_limit'] = $component->maximum_limit ?? 0;
                $data['reorder_level'] = $component->minimum_reorder_level ?? 0;
            }

            // ✅ Unit information will be retrieved via relationship (no redundant fields needed)

            // ✅ Set default values for new fields
            $data['required_quantity'] = $data['required_quantity'] ?? $data['quantity'];
            $data['available_quantity'] = $data['available_quantity'] ?? $data['component_balance'] ?? 0;
            $data['unit_cost'] = $data['unit_cost'] ?? $data['purchase_price'] ?? 0;
            $data['total_cost'] = ($data['required_quantity'] ?? $data['quantity']) * ($data['unit_cost'] ?? 0);
            $data['component_type'] = $data['component_type'] ?? 'raw_material';
            $data['sequence_order'] = $data['sequence_order'] ?? 1;
            $data['status'] = $data['status'] ?? 'active';
            $data['is_active'] = $data['is_active'] ?? true;

            // ✅ Create BOM item
            $bomItem = BomItem::create($data);

            // ✅ Load relationships for response
            $bomItem->load([
                'company', 'branch', 'user', 'item', 'component', 'unit',
                'preferredSupplier', 'creator'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $bomItem,
                'message' => 'BOM item created successfully',
                'message_ar' => 'تم إنشاء عنصر قائمة المواد بنجاح'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create BOM item: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء عنصر قائمة المواد: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * ✅ Generate unique formula number.
     */
    private function generateFormulaNumber($companyId): string
    {
        $prefix = 'BOM-';
        $year = date('Y');
        $month = date('m');

        // Get the last BOM item formula number for this company
        $lastBomItem = BomItem::where('company_id', $companyId)
            ->where('formula_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('formula_number', 'desc')
            ->first();

        if ($lastBomItem) {
            // Extract the sequence number and increment
            $lastNumber = substr($lastBomItem->formula_number, -4);
            $nextNumber = str_pad((int)$lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$year}{$month}-{$nextNumber}";
    }

    /**
     * ✅ Set historical prices from invoices (placeholder implementation).
     */
    private function setHistoricalPrices(&$data, $itemId): void
    {
        // ✅ This would query actual sales and purchase invoice tables
        // For now, setting placeholder values

        // Historical Purchase Prices from Purchase Invoices
        $data['first_purchase_price'] = 0;
        $data['second_purchase_price'] = 0;
        $data['third_purchase_price'] = 0;

        // Historical Selling Prices from Sales Invoices
        $data['first_selling_price'] = 0;
        $data['second_selling_price'] = 0;
        $data['third_selling_price'] = 0;

        // TODO: Implement actual queries when invoice tables are available
    }

    /**
     * ✅ Filter BOM items by field value.
     */
    public function filterByField(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $field = $request->get('field');
        $value = $request->get('value');

        if (!$field || !$value) {
            return response()->json([
                'success' => false,
                'message' => 'Field and value parameters are required',
                'message_ar' => 'معاملات الحقل والقيمة مطلوبة'
            ], 400);
        }

        $allowedFields = [
            'status', 'item_id', 'component_id', 'component_type',
            'is_active', 'is_critical', 'formula_name'
        ];

        if (!in_array($field, $allowedFields)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid field for filtering',
                'message_ar' => 'حقل غير صالح للتصفية'
            ], 400);
        }

        $query = BomItem::with(['item', 'component', 'unit'])
            ->forCompany($companyId);

        // ✅ Apply field-based filtering
        if ($field === 'is_active' || $field === 'is_critical') {
            $query->where($field, $value === 'true' || $value === '1');
        } else {
            $query->whereRaw("LOWER({$field}) LIKE ?", ['%' . strtolower($value) . '%']);
        }

        $bomItems = $query->ordered()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bomItems,
            'filter' => ['field' => $field, 'value' => $value],
            'message' => 'Filtered BOM items retrieved successfully',
            'message_ar' => 'تم استرداد عناصر قائمة المواد المفلترة بنجاح'
        ]);
    }

    /**
     * ✅ Get first BOM item.
     */
    public function first(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $sortBy = $request->get('sort_by', 'sequence_order');
        $sortDirection = 'asc';

        $bomItem = BomItem::with(['item', 'component', 'unit'])
            ->forCompany($companyId)
            ->orderBy($sortBy, $sortDirection)
            ->first();

        if (!$bomItem) {
            return response()->json([
                'success' => false,
                'message' => 'No BOM items found',
                'message_ar' => 'لم يتم العثور على عناصر قائمة المواد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bomItem,
            'message' => 'First BOM item retrieved successfully',
            'message_ar' => 'تم استرداد أول عنصر قائمة مواد بنجاح'
        ]);
    }

    /**
     * ✅ Get last BOM item.
     */
    public function last(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $sortBy = $request->get('sort_by', 'sequence_order');
        $sortDirection = 'desc';

        $bomItem = BomItem::with(['item', 'component', 'unit'])
            ->forCompany($companyId)
            ->orderBy($sortBy, $sortDirection)
            ->first();

        if (!$bomItem) {
            return response()->json([
                'success' => false,
                'message' => 'No BOM items found',
                'message_ar' => 'لم يتم العثور على عناصر قائمة المواد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bomItem,
            'message' => 'Last BOM item retrieved successfully',
            'message_ar' => 'تم استرداد آخر عنصر قائمة مواد بنجاح'
        ]);
    }
}
