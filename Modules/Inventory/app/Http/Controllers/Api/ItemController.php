<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Http\Requests\StoreItemRequest;
use Modules\Inventory\Http\Requests\UpdateItemRequest;
use Modules\Inventory\Exports\ItemTransactionsExport;
use Modules\Inventory\Exports\ItemTransactionsMultiSheetExport;
use Modules\Inventory\Exports\ItemTransactionsSummaryExport;

/**
 * @group Inventory Management / Items
 *
 * APIs for managing inventory items, including creation, updates, search, and transaction tracking.
 */

class ItemController extends Controller
{
    /**
     * List Items
     *
     * Retrieve a paginated list of inventory items with comprehensive filtering options.
     *
     * @queryParam type string Filter by item type. Example: product
     * @queryParam branch_id integer Filter by branch ID. Example: 1
     * @queryParam unit_id integer Filter by unit ID. Example: 1
     * @queryParam parent_id integer Filter by parent item ID. Example: 1
     * @queryParam stock_tracking boolean Filter by stock tracking enabled. Example: true
     * @queryParam search string Search across item names and descriptions. Example: laptop
     * @queryParam company_id integer Filter by company ID. Example: 1
     * @queryParam per_page integer Number of items per page (default: 15). Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Laptop Dell XPS 13",
     *       "code": "DELL-XPS-13",
     *       "type": "product",
     *       "unit": {
     *         "id": 1,
     *         "name": "Piece"
     *       },
     *       "stock_tracking": true,
     *       "company": {
     *         "id": 1,
     *         "name": "ABC Company"
     *       },
     *       "created_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ],
     *   "message": "Items retrieved successfully"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving items: Database connection failed"
     * }
     */
    public function index(Request $request): JsonResponse
    {
      //  $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = Item::with(['company:id,title', 'branch', 'user:id,first_name,second_name,email', 'unit', 'parent', 'itemUnits.unit']);
           // ->forCompany($companyId);

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->get('unit_id'));
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->get('parent_id'));
        }

        if ($request->has('stock_tracking')) {
            $query->where('stock_tracking', $request->boolean('stock_tracking'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Individual field filters
        if ($request->has('item_number')) {
            $query->where('item_number', 'like', "%{$request->get('item_number')}%");
        }

        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->get('name')}%");
        }

        if ($request->has('model')) {
            $query->where('model', 'like', "%{$request->get('model')}%");
        }

        if ($request->has('balance')) {
            $balance = $request->get('balance');
            if (is_numeric($balance)) {
                $query->where('balance', $balance);
            }
        }

        if ($request->has('balance_min')) {
            $query->where('balance', '>=', $request->get('balance_min'));
        }

        if ($request->has('balance_max')) {
            $query->where('balance', '<=', $request->get('balance_max'));
        }

        if ($request->has('id')) {
            $query->where('id', $request->get('id'));
        }

        // Advanced Sorting with multiple columns support
        $this->applySorting($query, $request);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $items = $query->paginate($perPage);

        // Apply dynamic field selection
        $items = $this->applyFieldSelection($items, $request);

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'Items retrieved successfully'
        ]);
    }

    /**
     * Store a newly created item with all comprehensive data (Save functionality).
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        // Check if warehouses exist for the company
        $warehousesExist = \Modules\Inventory\Models\Warehouse::forCompany($companyId)->exists();

        if (!$warehousesExist) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add item. No warehouses exist for this company.',
                'message_ar' => 'لا يمكن إضافة الصنف. لا توجد مخازن لهذه الشركة.',
                'error_code' => 'NO_WAREHOUSES'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['company_id'] = $companyId;
            $data['user_id'] = $userId;
            $data['created_by'] = $userId;

            // Auto-generate item number if not provided
            if (empty($data['item_number'])) {
                $data['item_number'] = $this->generateItemNumber($companyId);
            }

            // Set default warehouse if not specified
            if (empty($data['warehouse_id'])) {
                $defaultWarehouse = $this->getDefaultWarehouse($companyId);
                if ($defaultWarehouse) {
                    $data['warehouse_id'] = $defaultWarehouse->id;
                }
            }

            // Set default barcode type if barcode is provided but type is not
            if (!empty($data['barcode']) && empty($data['barcode_type'])) {
                $data['barcode_type'] = Item::getDefaultBarcodeType();
            }

            // Validate barcode if provided
            if (!empty($data['barcode']) && !empty($data['barcode_type'])) {
                $tempItem = new Item([
                    'barcode' => $data['barcode'],
                    'barcode_type' => $data['barcode_type'],
                ]);
                $validation = $tempItem->validateBarcode();
                if (!$validation['valid']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Barcode validation failed',
                        'message_ar' => 'فشل في التحقق من صحة الباركود',
                        'errors' => ['barcode' => $validation['errors']]
                    ], 422);
                }
            }

            // Handle custom item type creation if needed
            if (!empty($data['custom_item_type'])) {
                $customType = \Modules\Inventory\Models\ItemType::createCustomType(
                    $companyId,
                    $data['custom_item_type'],
                    $data['custom_item_type_ar'] ?? $data['custom_item_type']
                );
                $data['item_type'] = $customType->code;
                unset($data['custom_item_type'], $data['custom_item_type_ar']);
            }

            // Create the main item record
            $item = Item::create($data);

            // Load all relationships for comprehensive response
            $item->load([
                'company:id,title',
                'branch:id,name',
                'user:id,first_name,second_name,email',
                'unit:id,name,symbol',
                'parent:id,item_number,name',
                'createdBy:id,first_name,second_name,email',
                'children:id,item_number,name',
                'itemUnits.unit:id,name,symbol'
            ]);

            // Create initial inventory stock record if warehouse is specified
            if (!empty($data['warehouse_id'])) {
                $this->createInitialInventoryStock($item, $data['warehouse_id'], $data);
            }

            DB::commit();

            // Prepare comprehensive response data
            $responseData = [
                'item' => $item,
                'comprehensive_data' => $this->getComprehensiveItemData($item),
                'validation_results' => [
                    'barcode_validation' => !empty($item->barcode) ? $item->validateBarcode() : null,
                    'expiry_status' => $item->expiry_status,
                    'expiry_status_ar' => $item->expiry_status_arabic,
                    'days_until_expiry' => $item->days_until_expiry,
                    'is_expired' => $item->is_expired,
                ],
                'pricing_info' => $item->pricing_info,
                'formatted_data' => [
                    'barcode_type_display' => $item->barcode_type_display,
                    'item_type_display' => $item->item_type_display,
                    'formatted_discount_rates' => $item->formatted_discount_rates,
                    'expiry_date_formatted' => $item->expiry_date ? $item->expiry_date->format('Y-m-d') : null,
                ],
                'system_info' => [
                    'item_number_generated' => empty($request->item_number),
                    'warehouse_assigned' => !empty($data['warehouse_id']),
                    'barcode_type_defaulted' => !empty($data['barcode']) && empty($request->barcode_type),
                    'custom_item_type_created' => !empty($request->custom_item_type),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Item saved successfully with all data',
                'message_ar' => 'تم حفظ الصنف بنجاح مع جميع البيانات',
                'save_status' => 'complete'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save item: ' . $e->getMessage(),
                'message_ar' => 'فشل في حفظ الصنف: ' . $e->getMessage(),
                'error_details' => [
                    'type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                'save_status' => 'failed'
            ], 500);
        }
    }

    /**
     * Get available warehouses for item creation.
     */
    public function getAvailableWarehouses(Request $request): JsonResponse
    {
       // $companyId = Auth::user()->company_id ?? $request->company_id;

        $warehouses = \Modules\Inventory\Models\Warehouse::where('status', 'active')

            ->select('id', 'warehouse_number', 'name', 'address', 'status')
            ->orderBy('warehouse_number')
            ->orderBy('name', 'asc')
            ->get();

        if ($warehouses->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouses available. Please create a warehouse first.',
                'message_ar' => 'لا توجد مخازن متاحة. يرجى إنشاء مخزن أولاً.',
                'data' => [],
                'can_add_items' => false
            ]);
        }

        // Find a default warehouse (first one with "main" in name or first by warehouse_number)
        $defaultWarehouse = $warehouses->first(function ($warehouse) {
            return stripos($warehouse->name, 'main') !== false ||
                   stripos($warehouse->name, 'رئيسي') !== false ||
                   stripos($warehouse->name, 'المخزن الرئيسي') !== false ||
                   stripos($warehouse->name, 'المستودع الرئيسي') !== false;
        }) ?: $warehouses->first();

        return response()->json([
            'success' => true,
            'data' => $warehouses,
            'message' => 'Available warehouses retrieved successfully',
            'message_ar' => 'تم استرداد المخازن المتاحة بنجاح',
            'can_add_items' => true,
            'default_warehouse' => $defaultWarehouse
        ]);
    }

    /**
     * Generate unique item number for the company.
     */
    private function generateItemNumber($companyId): string
    {
        $prefix = 'ITM';
        $year = date('Y');

        // Get the last item number for this company and year
        $lastItem = Item::forCompany($companyId)
            ->where('item_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('item_number', 'desc')
            ->first();

        if ($lastItem) {
            // Extract the sequence number and increment
            $parts = explode('-', $lastItem->item_number);
            $sequence = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    /**
     * Get default warehouse for the company.
     */
    private function getDefaultWarehouse($companyId)
    {
        // Try to find "Main Warehouse" or similar names
        $mainWarehouse = \Modules\Inventory\Models\Warehouse::forCompany($companyId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where('name', 'like', '%main%')
                      ->orWhere('name', 'like', '%رئيسي%')
                      ->orWhere('name', 'like', '%المخزن الرئيسي%')
                      ->orWhere('name', 'like', '%المستودع الرئيسي%')
                      ->orWhere('warehouse_number', 'like', 'WH-001')
                      ->orWhere('warehouse_number', 'like', 'WH-01');
            })
            ->first();

        if ($mainWarehouse) {
            return $mainWarehouse;
        }

        // If no main warehouse found, get the first active warehouse
        return \Modules\Inventory\Models\Warehouse::forCompany($companyId)
            ->where('status', 'active')
            ->orderBy('warehouse_number')
            ->first();
    }

    /**
     * Create initial inventory stock record.
     */
    private function createInitialInventoryStock($item, $warehouseId, $data)
    {
        $initialQuantity = $data['quantity'] ?? 0;

        // Clean up any orphaned stock records for this item first
        // This handles cases where previous failed transactions left orphaned records
        \Modules\Inventory\Models\InventoryStock::where('inventory_item_id', $item->id)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('items')
                    ->whereRaw('items.id = inventory_stock.inventory_item_id');
            })
            ->delete();

        // Use firstOrCreate to avoid duplicate key errors
        $stock = \Modules\Inventory\Models\InventoryStock::firstOrCreate([
            'company_id' => $item->company_id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouseId,
        ], [
            'quantity' => $initialQuantity,
            'reserved_quantity' => 0,
            'available_quantity' => $initialQuantity,
        ]);

        // If the record already existed, update the quantities
        if (!$stock->wasRecentlyCreated && $initialQuantity > 0) {
            $stock->quantity = $initialQuantity;
            $stock->available_quantity = $initialQuantity;
            $stock->save();
        }
    }

    /**
     * Display the specified item with all available data.
     */
    public function show($id): JsonResponse
    {
      //  $companyId = Auth::user()->company_id ?? request()->company_id;

        $item = Item::with([
            'company:id,title,email,landline',
            'branch:id,name,address,phone',
            'user:id,first_name,second_name,email',
            'unit:id,name,symbol,description',
            'parent:id,item_number,name,code',
            'children:id,item_number,name,code,parent_id',
            'itemUnits.unit:id,name,symbol',
            'createdBy:id,first_name,second_name,email',
            'updatedBy:id,first_name,second_name,email',
            'deletedBy:id,first_name,second_name,email'
        ])
       // ->forCompany($companyId)
        ->findOrFail($id);

        // Get comprehensive item data
        $itemData = $this->getComprehensiveItemData($item);

        return response()->json([
            'success' => true,
            'data' => $itemData,
            'message' => 'Item details retrieved successfully'
        ]);
    }

    /**
     * Get comprehensive preview/review data for an item.
     */
    public function preview($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $item = Item::with([
            'company:id,title,email,landline,address',
            'branch:id,name,address,phone,email',
            'user:id,first_name,second_name,email,phone',
            'unit:id,name,symbol,description',
            'parent:id,item_number,name,code,description',
            'children:id,item_number,name,code,parent_id,quantity,balance',
            'itemUnits.unit:id,name,symbol,description',
            'createdBy:id,first_name,second_name,email',
            'updatedBy:id,first_name,second_name,email',
            'deletedBy:id,first_name,second_name,email'
        ])
        // ->forCompany($companyId)
        ->findOrFail($id);

        // Get comprehensive preview data
        $previewData = $this->getComprehensivePreviewData($item);

        return response()->json([
            'success' => true,
            'data' => $previewData,
            'message' => 'Item preview data retrieved successfully'
        ]);
    }

    /**
     * Update the specified item.
     */
    public function update(UpdateItemRequest $request, $id): JsonResponse
    {
       // $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        $item = Item::findOrFail($id);

        //forCompany($companyId)->

        $data = $request->validated();
        $data['updated_by'] = $userId;

        // Store original values for comparison
        $originalData = $item->only([
            'item_number', 'name', 'description', 'model', 'unit_id',
            'balance', 'minimum_limit', 'maximum_limit', 'reorder_limit'
        ]);

        $item->update($data);
        $item->load(['company:id,title', 'branch', 'user:id,first_name,second_name,email', 'unit', 'parent']);

        // Get updated values for response
        $updatedFields = [];
        foreach ($originalData as $field => $originalValue) {
            if (isset($data[$field]) && $data[$field] != $originalValue) {
                $updatedFields[$field] = [
                    'from' => $originalValue,
                    'to' => $data[$field]
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $item,
            'updated_fields' => $updatedFields,
            'message' => 'Item updated successfully'
        ]);
    }

    /**
     * Remove the specified item (soft delete).
     */
    public function destroy($id): JsonResponse
    {
    //    $companyId = Auth::user()->company_id ?? request()->company_id;
        $userId = Auth::id() ?? request()->user_id;

        $item = Item::findOrFail($id);

        // forCompany($companyId)->

        // Check if item has children or item units
        if ($item->children()->exists() || $item->itemUnits()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete item with existing children or item units',
                'message_ar' => 'لا يمكن حذف الصنف الذي يحتوي على أصناف فرعية أو وحدات'
            ], 422);
        }

        // Set deleted_by before soft delete
        $item->update(['deleted_by' => $userId]);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully',
            'message_ar' => 'تم حذف الصنف بنجاح',
            'deleted_at' => $item->deleted_at,
            'deleted_by' => $userId
        ]);
    }

    /**
     * Restore a soft deleted item.
     */
    public function restore($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;
        $userId = Auth::id() ?? request()->user_id;

        $item = Item::withTrashed()
            // ->forCompany($companyId)
            ->findOrFail($id);

        if (!$item->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Item is not deleted',
                'message_ar' => 'الصنف غير محذوف'
            ], 422);
        }

        // Clear deleted_by and restore
        $item->update([
            'deleted_by' => null,
            'updated_by' => $userId
        ]);
        $item->restore();

        return response()->json([
            'success' => true,
            'message' => 'Item restored successfully',
            'message_ar' => 'تم استعادة الصنف بنجاح',
            'data' => $item->load(['unit', 'company:id,title', 'branch'])
        ]);
    }

    /**
     * Permanently delete an item (force delete).
     */
    public function forceDelete($id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $item = Item::withTrashed()
            // ->forCompany($companyId)
            ->findOrFail($id);

        // Check if item has children or item units
        if ($item->children()->withTrashed()->exists() || $item->itemUnits()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot permanently delete item with existing children or item units',
                'message_ar' => 'لا يمكن حذف الصنف نهائياً الذي يحتوي على أصناف فرعية أو وحدات'
            ], 422);
        }

        $itemName = $item->name;
        $item->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Item permanently deleted successfully',
            'message_ar' => 'تم حذف الصنف نهائياً بنجاح',
            'deleted_item' => $itemName
        ]);
    }

    /**
     * Get trashed (soft deleted) items.
     */
    public function trashed(Request $request): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = Item::onlyTrashed()
            ->with(['company:id,title', 'branch', 'unit', 'deletedBy']);
            // ->forCompany($companyId);

        // Apply search to trashed items
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'deleted_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $trashedItems = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $trashedItems,
            'message' => 'Trashed items retrieved successfully',
            'message_ar' => 'تم استرداد الأصناف المحذوفة بنجاح'
        ]);
    }

    /**
     * Get the first item.
     */
    public function first(): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $item = Item::with(['company:id,title', 'branch', 'user:id,first_name,second_name,email', 'unit', 'parent'])
            // ->forCompany($companyId)
            ->orderBy('name')
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'No items found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'First item retrieved successfully'
        ]);
    }

    /**
     * Get the last item.
     */
    public function last(): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $item = Item::with(['company:id,title', 'branch', 'user:id,first_name,second_name,email', 'unit', 'parent'])
            // ->forCompany($companyId)
            ->orderBy('name', 'desc')
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'No items found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Last item retrieved successfully'
        ]);
    }

    /**
     * Get items by type.
     */
    public function byType($type): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $items = Item::with(['company:id,title', 'branch', 'user:id,first_name,second_name,email', 'unit', 'parent'])
            // ->forCompany($companyId)
            ->byType($type)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => "Items of type {$type} retrieved successfully"
        ]);
    }

    /**
     * Get parent items only.
     */
    public function parents(): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? request()->company_id;

        $items = Item::with(['company:id,title', 'branch', 'user:id,first_name,second_name,email', 'unit', 'children'])
            // ->forCompany($companyId)
            ->parentsOnly()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'Parent items retrieved successfully'
        ]);
    }

    /**
     * Search for items with advanced filtering.
     */
    public function search(Request $request): JsonResponse
    {
       // $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = Item::with(['company:id,title', 'branch', 'user:id,first_name,second_name,email', 'unit', 'parent']);
           // ->forCompany($companyId);

        // Search by ID (Item Number)
        if ($request->has('id') && !empty($request->get('id'))) {
            $query->where('id', $request->get('id'));
        }

        // Search by Item Number
        if ($request->has('item_number') && !empty($request->get('item_number'))) {
            $query->where('item_number', 'like', "%{$request->get('item_number')}%");
        }

        // Search by Name (اسم الصنف)
        if ($request->has('name') && !empty($request->get('name'))) {
            $query->where('name', 'like', "%{$request->get('name')}%");
        }

        // Search by Model (موديل)
        if ($request->has('model') && !empty($request->get('model'))) {
            $query->where('model', 'like', "%{$request->get('model')}%");
        }

        // Search by Balance (الرصيد)
        if ($request->has('balance') && is_numeric($request->get('balance'))) {
            $query->where('balance', $request->get('balance'));
        }

        // Balance range filters
        if ($request->has('balance_from') && is_numeric($request->get('balance_from'))) {
            $query->where('balance', '>=', $request->get('balance_from'));
        }

        if ($request->has('balance_to') && is_numeric($request->get('balance_to'))) {
            $query->where('balance', '<=', $request->get('balance_to'));
        }

        // Additional filters
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $items = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'Items search completed successfully',
            'search_criteria' => [
                'id' => $request->get('id'),
                'item_number' => $request->get('item_number'),
                'name' => $request->get('name'),
                'model' => $request->get('model'),
                'balance' => $request->get('balance'),
                'balance_from' => $request->get('balance_from'),
                'balance_to' => $request->get('balance_to'),
            ]
        ]);
    }

    /**
     * Get comprehensive item data for detailed view.
     */
    private function getComprehensiveItemData($item): array
    {
        return [
            // Basic Information
            'basic_info' => [
                'id' => $item->id,
                'item_number' => $item->item_number,
                'code' => $item->code,
                'catalog_number' => $item->catalog_number,
                'name' => $item->name,
                'description' => $item->description,
                'model' => $item->model,
                'type' => $item->type,
                'type_label' => $this->getTypeLabel($item->type),
                'barcode' => $item->barcode,
                'color' => $item->color,
                'image' => $item->image,
                'active' => $item->active,
                'stock_tracking' => $item->stock_tracking,
            ],

            // Stock Information
            'stock_info' => [
                'quantity' => $item->quantity,
                'balance' => $item->balance,
                'minimum_limit' => $item->minimum_limit,
                'maximum_limit' => $item->maximum_limit,
                'reorder_limit' => $item->reorder_limit,
                'max_reorder_limit' => $item->max_reorder_limit,
                'stock_status' => $this->getStockStatus($item),
            ],

            // Sales & Purchase Prices Information
            'pricing_info' => [
                'purchase_prices' => [
                    'first' => $item->first_purchase_price,
                    'second' => $item->second_purchase_price,
                    'third' => $item->third_purchase_price,
                    'discount_rate' => $item->purchase_discount_rate,
                    'include_vat' => $item->purchase_prices_include_vat,
                    'last_from_purchases' => $item->getLastPurchasePriceFromPurchases(),
                    'first_from_purchases' => $item->getFirstPurchasePriceFromPurchases(),
                ],
                'sale_prices' => [
                    'first' => $item->first_sale_price,
                    'second' => $item->second_sale_price,
                    'third' => $item->third_sale_price,
                    'discount_rate' => $item->sale_discount_rate,
                    'maximum_discount_rate' => $item->maximum_sale_discount_rate,
                    'minimum_allowed_price' => $item->minimum_allowed_sale_price,
                    'include_vat' => $item->sale_prices_include_vat,
                    'last_from_invoices' => $item->getLastSalePriceFromInvoices(),
                    'first_from_invoices' => $item->getFirstSalePriceFromInvoices(),
                ],
                'vat_info' => [
                    'item_subject_to_vat' => $item->item_subject_to_vat,
                ],
            ],

            // Relationships
            'relationships' => [
                'company' => $item->company,
                'branch' => $item->branch,
                'user' => $item->user,
                'unit' => $item->unit,
                'parent' => $item->parent,
                'children' => $item->children,
                'item_units' => $item->itemUnits,
            ],

            // Audit Information
            'audit_info' => [
                'created_by' => $item->createdBy,
                'updated_by' => $item->updatedBy,
                'deleted_by' => $item->deletedBy,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ],

            // Additional Notes
            'notes' => $item->notes,
        ];
    }

    /**
     * Get comprehensive preview data with Arabic labels.
     */
    private function getComprehensivePreviewData($item): array
    {
        return [
            // معلومات أساسية
            'basic_info' => [
                'title' => 'المعلومات الأساسية',
                'data' => [
                    'رقم الصنف' => $item->item_number,
                    'كود الصنف' => $item->code,
                    'رقم الكتالوج' => $item->catalog_number,
                    'اسم الصنف' => $item->name,
                    'بيان الصنف' => $item->description,
                    'موديل' => $item->model,
                    'نوع الصنف' => $this->getTypeLabel($item->type),
                    'الباركود' => $item->barcode,
                    'اللون' => $item->color,
                    'الصورة' => $item->image,
                    'نشط' => $item->active ? 'نعم' : 'لا',
                    'تتبع المخزون' => $item->stock_tracking ? 'نعم' : 'لا',
                ]
            ],

            // معلومات المخزون
            'stock_info' => [
                'title' => 'معلومات المخزون',
                'data' => [
                    'الكمية الحالية' => $item->quantity,
                    'الرصيد' => $item->balance,
                    'الحد الأدنى' => $item->minimum_limit,
                    'الحد الأقصى' => $item->maximum_limit,
                    'حد إعادة الطلب' => $item->reorder_limit,
                    'أغلى حد لإعادة الطلب' => $item->max_reorder_limit,
                    'حالة المخزون' => $this->getStockStatusArabic($item),
                ]
            ],

            // أسعار البيع والشراء
            'sales_purchase_prices' => [
                'title' => 'أسعار البيع والشراء',
                'data' => [
                    // Sale Prices
                    'سعر البيع الأول' => $item->first_sale_price ? number_format($item->first_sale_price, 2) . ' ريال' : 'غير محدد',
                    'سعر البيع الثاني' => $item->second_sale_price ? number_format($item->second_sale_price, 2) . ' ريال' : 'غير محدد',
                    'سعر البيع الثالث' => $item->third_sale_price ? number_format($item->third_sale_price, 2) . ' ريال' : 'غير محدد',
                    'نسبة الخصم عند البيع' => $item->sale_discount_rate ? $item->sale_discount_rate . '%' : 'غير محدد',
                    'أعلى نسبة خصم عند البيع' => $item->maximum_sale_discount_rate ? $item->maximum_sale_discount_rate . '%' : 'غير محدد',
                    'أقل سعر بيع مسموح به' => $item->minimum_allowed_sale_price ? number_format($item->minimum_allowed_sale_price, 2) . ' ريال' : 'غير محدد',
                    'أسعار البيع تشمل الضريبة المضافة' => $item->sale_prices_include_vat ? 'نعم' : 'لا',

                    // Purchase Prices
                    'سعر الشراء الأول' => $item->first_purchase_price ? number_format($item->first_purchase_price, 2) . ' ريال' : 'غير محدد',
                    'سعر الشراء الثاني' => $item->second_purchase_price ? number_format($item->second_purchase_price, 2) . ' ريال' : 'غير محدد',
                    'سعر الشراء الثالث' => $item->third_purchase_price ? number_format($item->third_purchase_price, 2) . ' ريال' : 'غير محدد',
                    'نسبة الخصم عند الشراء' => $item->purchase_discount_rate ? $item->purchase_discount_rate . '%' : 'غير محدد',
                    'أسعار الشراء تشمل الضريبة المضافة' => $item->purchase_prices_include_vat ? 'نعم' : 'لا',

                    // VAT Information
                    'يخضع الصنف لضريبة المضافة' => $item->item_subject_to_vat ? 'نعم' : 'لا',
                ]
            ],

            // العلاقات
            'relationships' => [
                'title' => 'العلاقات والارتباطات',
                'data' => [
                    'الشركة' => $item->company ? $item->company->name : 'غير محدد',
                    'الفرع' => $item->branch ? $item->branch->name : 'غير محدد',
                    'المستخدم' => $item->user ? $item->user->name : 'غير محدد',
                    'الوحدة' => $item->unit ? $item->unit->name . ' (' . $item->unit->symbol . ')' : 'غير محدد',
                    'الصنف الأب' => $item->parent ? $item->parent->name : 'لا يوجد',
                    'عدد الأصناف الفرعية' => $item->children ? $item->children->count() : 0,
                ]
            ],

            // معلومات التدقيق
            'audit_info' => [
                'title' => 'معلومات التدقيق',
                'data' => [
                    'أنشئ بواسطة' => $item->createdBy ? $item->createdBy->name : 'غير محدد',
                    'عُدل بواسطة' => $item->updatedBy ? $item->updatedBy->name : 'غير محدد',
                    'حُذف بواسطة' => $item->deletedBy ? $item->deletedBy->name : 'غير محدد',
                    'تاريخ الإنشاء' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : 'غير محدد',
                    'تاريخ التحديث' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : 'غير محدد',
                    'تاريخ الحذف' => $item->deleted_at ? $item->deleted_at->format('Y-m-d H:i:s') : 'غير محذوف',
                ]
            ],

            // ملاحظات إضافية
            'additional_info' => [
                'title' => 'معلومات إضافية',
                'data' => [
                    'الملاحظات' => $item->notes ?: 'لا توجد ملاحظات',
                    'الباركود' => $item->barcode ?: 'غير محدد',
                    'نوع الباركود' => $item->barcode_type_display,
                    'تاريخ الانتهاء' => $item->expiry_date ? $item->expiry_date->format('Y-m-d') : 'لا ينتهي',
                    'حالة الانتهاء' => $item->expiry_status_arabic,
                    'الأيام المتبقية' => $item->days_until_expiry !== null ? $item->days_until_expiry . ' يوم' : 'لا ينطبق',
                    'الصورة' => $item->image ? 'متوفرة' : 'غير متوفرة',
                    'اللون' => $item->color ?: 'غير محدد',
                    'نوع الصنف' => $item->item_type_display,
                    'نشط' => $item->active ? 'نعم' : 'لا',
                    'تتبع المخزون' => $item->stock_tracking ? 'نعم' : 'لا',
                    'وحدات الصنف' => $item->itemUnits ? $item->itemUnits->map(function($itemUnit) {
                        return $itemUnit->unit->name . ' (' . $itemUnit->unit->symbol . ')';
                    })->implode(', ') : 'لا توجد وحدات إضافية',
                ]
            ],

            // الأصناف الفرعية
            'children_items' => [
                'title' => 'الأصناف الفرعية',
                'data' => $item->children ? $item->children->map(function($child) {
                    return [
                        'رقم الصنف' => $child->item_number,
                        'اسم الصنف' => $child->name,
                        'الكمية' => $child->quantity,
                        'الرصيد' => $child->balance,
                    ];
                }) : []
            ]
        ];
    }

    /**
     * Get stock status in English.
     */
    private function getStockStatus($item): string
    {
        if ($item->quantity <= $item->minimum_limit) {
            return 'Low Stock';
        } elseif ($item->quantity <= $item->reorder_limit) {
            return 'Reorder Required';
        } elseif ($item->quantity >= $item->maximum_limit) {
            return 'Overstock';
        } else {
            return 'Normal';
        }
    }

    /**
     * Get stock status in Arabic.
     */
    private function getStockStatusArabic($item): string
    {
        if ($item->quantity <= $item->minimum_limit) {
            return 'مخزون منخفض';
        } elseif ($item->quantity <= $item->reorder_limit) {
            return 'يحتاج إعادة طلب';
        } elseif ($item->quantity >= $item->maximum_limit) {
            return 'مخزون زائد';
        } else {
            return 'طبيعي';
        }
    }

    /**
     * Apply dynamic field selection based on user preferences.
     */
    private function applyFieldSelection($items, Request $request)
    {
        // Get selected fields from request
        $selectedFields = $request->get('fields', []);

        // If no fields specified, return all data
        if (empty($selectedFields) || !is_array($selectedFields)) {
            return $items;
        }

        // Define available fields mapping (Arabic to English)
        $fieldMapping = [
            'item_number' => 'item_number',        // رقم الصنف
            'code' => 'code',                      // كود الصنف
            'catalog_number' => 'catalog_number',  // رقم الكتالوج
            'name' => 'name',                      // اسم الصنف
            'description' => 'description',        // بيان الصنف
            'model' => 'model',                    // موديل
            'unit' => 'unit',                      // الوحدة
            'balance' => 'balance',                // الرصيد
            'minimum_limit' => 'minimum_limit',    // الحد الأدنى
            'maximum_limit' => 'maximum_limit',    // الحد الأقصى
            'reorder_limit' => 'reorder_limit',    // حد إعادة الطلب
            'max_reorder_limit' => 'max_reorder_limit', // أغلى حد لإعادة الطلب
            'first_purchase_price' => 'first_purchase_price', // سعر الشراء الأول
            'second_purchase_price' => 'second_purchase_price', // سعر الشراء الثاني
            'third_purchase_price' => 'third_purchase_price', // سعر الشراء الثالث
            'color' => 'color',                    // اللون
            'image' => 'image',                    // الصورة
        ];

        // Always include ID for reference
        $fieldsToInclude = ['id'];

        // Add selected fields
        foreach ($selectedFields as $field) {
            if (isset($fieldMapping[$field])) {
                $fieldsToInclude[] = $fieldMapping[$field];
            }
        }

        // Transform the paginated data
        if (method_exists($items, 'getCollection')) {
            // For paginated results
            $transformedCollection = $items->getCollection()->map(function ($item) use ($fieldsToInclude) {
                return $this->selectItemFields($item, $fieldsToInclude);
            });
            $items->setCollection($transformedCollection);
        } else {
            // For regular collections
            $items = $items->map(function ($item) use ($fieldsToInclude) {
                return $this->selectItemFields($item, $fieldsToInclude);
            });
        }

        return $items;
    }

    /**
     * Select specific fields from an item.
     */
    private function selectItemFields($item, array $fieldsToInclude)
    {
        $selectedData = [];

        foreach ($fieldsToInclude as $field) {
            if ($field === 'unit' && $item->unit) {
                $selectedData['unit'] = [
                    'id' => $item->unit->id,
                    'name' => $item->unit->name,
                    'symbol' => $item->unit->symbol ?? null,
                ];
            } else {
                $selectedData[$field] = $item->{$field} ?? null;
            }
        }

        return $selectedData;
    }

    /**
     * Apply advanced sorting to the query.
     */
    private function applySorting($query, Request $request)
    {
        // Define sortable columns with their database column names
        $sortableColumns = [
            'id' => 'id',
            'item_number' => 'item_number',
            'name' => 'name',
            'code' => 'code',
            'description' => 'description',
            'model' => 'model',
            'type' => 'type',
            'quantity' => 'quantity',
            'balance' => 'balance',
            'minimum_limit' => 'minimum_limit',
            'maximum_limit' => 'maximum_limit',
            'reorder_limit' => 'reorder_limit',
            'max_reorder_limit' => 'max_reorder_limit',
            'first_purchase_price' => 'first_purchase_price',
            'second_purchase_price' => 'second_purchase_price',
            'third_purchase_price' => 'third_purchase_price',
            'first_sale_price' => 'first_sale_price',
            'second_sale_price' => 'second_sale_price',
            'third_sale_price' => 'third_sale_price',
            'active' => 'active',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'unit_name' => 'units.name', // For joined sorting
        ];

        // Multiple column sorting support
        if ($request->has('sorts') && is_array($request->get('sorts'))) {
            foreach ($request->get('sorts') as $sort) {
                if (isset($sort['column']) && isset($sort['direction'])) {
                    $column = $sort['column'];
                    $direction = strtolower($sort['direction']) === 'desc' ? 'desc' : 'asc';

                    if (isset($sortableColumns[$column])) {
                        if ($column === 'unit_name') {
                            $query->leftJoin('units', 'items.unit_id', '=', 'units.id')
                                  ->orderBy('units.name', $direction);
                        } else {
                            $query->orderBy($sortableColumns[$column], $direction);
                        }
                    }
                }
            }
        } else {
            // Single column sorting (backward compatibility)
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');

            if (isset($sortableColumns[$sortBy])) {
                if ($sortBy === 'unit_name') {
                    $query->leftJoin('units', 'items.unit_id', '=', 'units.id')
                          ->orderBy('units.name', $sortDirection);
                } else {
                    $query->orderBy($sortableColumns[$sortBy], $sortDirection);
                }
            } else {
                $query->orderBy('name', 'asc'); // Default sorting
            }
        }
    }

    /**
     * Get sortable columns with their Arabic labels.
     */
    public function getSortableColumns(): JsonResponse
    {
        $columns = [
            [
                'key' => 'id',
                'label' => 'الرقم',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'item_number',
                'label' => 'رقم الصنف',
                'type' => 'string',
                'sortable' => true
            ],
            [
                'key' => 'name',
                'label' => 'اسم الصنف',
                'type' => 'string',
                'sortable' => true
            ],
            [
                'key' => 'code',
                'label' => 'كود الصنف',
                'type' => 'string',
                'sortable' => true
            ],
            [
                'key' => 'description',
                'label' => 'بيان الصنف',
                'type' => 'text',
                'sortable' => true
            ],
            [
                'key' => 'model',
                'label' => 'موديل',
                'type' => 'string',
                'sortable' => true
            ],
            [
                'key' => 'type',
                'label' => 'نوع الصنف',
                'type' => 'string',
                'sortable' => true
            ],
            [
                'key' => 'unit_name',
                'label' => 'الوحدة',
                'type' => 'string',
                'sortable' => true
            ],
            [
                'key' => 'quantity',
                'label' => 'الكمية',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'balance',
                'label' => 'الرصيد',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'minimum_limit',
                'label' => 'الحد الأدنى',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'maximum_limit',
                'label' => 'الحد الأقصى',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'reorder_limit',
                'label' => 'حد إعادة الطلب',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'first_purchase_price',
                'label' => 'سعر الشراء الأول',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'second_purchase_price',
                'label' => 'سعر الشراء الثاني',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'third_purchase_price',
                'label' => 'سعر الشراء الثالث',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'first_sale_price',
                'label' => 'سعر البيع الأول',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'second_sale_price',
                'label' => 'سعر البيع الثاني',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'third_sale_price',
                'label' => 'سعر البيع الثالث',
                'type' => 'number',
                'sortable' => true
            ],
            [
                'key' => 'active',
                'label' => 'نشط',
                'type' => 'boolean',
                'sortable' => true
            ],
            [
                'key' => 'created_at',
                'label' => 'تاريخ الإنشاء',
                'type' => 'datetime',
                'sortable' => true
            ],
            [
                'key' => 'updated_at',
                'label' => 'تاريخ التحديث',
                'type' => 'datetime',
                'sortable' => true
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $columns,
            'message' => 'Sortable columns retrieved successfully'
        ]);
    }

    /**
     * Get available categories/types for filtering.
     */
    public function getCategories(): JsonResponse
    {
     //   $companyId = Auth::user()->company_id ?? request()->company_id;

        // Get distinct types from items
        $types = Item::select('type')

            ->distinct()
            ->whereNotNull('type')
            ->pluck('type')
            ->map(function ($type) {
                return [
                    'key' => $type,
                    'label' => $this->getTypeLabel($type),
                    'count' => Item::forCompany(Auth::user()->company_id ?? request()->company_id)
                        ->where('type', $type)
                        ->count()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $types,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Get Arabic label for item type.
     */
    private function getTypeLabel($type): string
    {
        $typeLabels = [
            'product' => 'منتج',
            'service' => 'خدمة',
            'material' => 'مادة',
            'raw_material' => 'مادة خام'
        ];

        return $typeLabels[$type] ?? $type;
    }

    /**
     * Get available fields for dynamic selection.
     */
    public function getAvailableFields(): JsonResponse
    {
        $fields = [
            [
                'key' => 'item_number',
                'label' => 'رقم الصنف',
                'type' => 'string',
                'default_selected' => true
            ],
            [
                'key' => 'code',
                'label' => 'كود الصنف',
                'type' => 'string',
                'default_selected' => true
            ],
            [
                'key' => 'catalog_number',
                'label' => 'رقم الكتالوج',
                'type' => 'string',
                'default_selected' => true
            ],
            [
                'key' => 'name',
                'label' => 'اسم الصنف',
                'type' => 'string',
                'default_selected' => true
            ],
            [
                'key' => 'description',
                'label' => 'بيان الصنف',
                'type' => 'text',
                'default_selected' => true
            ],
            [
                'key' => 'model',
                'label' => 'موديل',
                'type' => 'string',
                'default_selected' => true
            ],
            [
                'key' => 'unit',
                'label' => 'الوحدة',
                'type' => 'object',
                'default_selected' => true
            ],
            [
                'key' => 'balance',
                'label' => 'الرصيد',
                'type' => 'decimal',
                'default_selected' => true
            ],
            [
                'key' => 'minimum_limit',
                'label' => 'الحد الأدنى',
                'type' => 'decimal',
                'default_selected' => true
            ],
            [
                'key' => 'maximum_limit',
                'label' => 'الحد الأقصى',
                'type' => 'decimal',
                'default_selected' => true
            ],
            [
                'key' => 'reorder_limit',
                'label' => 'حد إعادة الطلب',
                'type' => 'decimal',
                'default_selected' => true
            ],
            [
                'key' => 'max_reorder_limit',
                'label' => 'أغلى حد لإعادة الطلب',
                'type' => 'decimal',
                'default_selected' => false
            ],
            [
                'key' => 'first_purchase_price',
                'label' => 'سعر الشراء الأول',
                'type' => 'decimal',
                'default_selected' => false
            ],
            [
                'key' => 'second_purchase_price',
                'label' => 'سعر الشراء الثاني',
                'type' => 'decimal',
                'default_selected' => false
            ],
            [
                'key' => 'third_purchase_price',
                'label' => 'سعر الشراء الثالث',
                'type' => 'decimal',
                'default_selected' => false
            ],
            [
                'key' => 'color',
                'label' => 'اللون',
                'type' => 'string',
                'default_selected' => false
            ],
            [
                'key' => 'image',
                'label' => 'الصورة',
                'type' => 'string',
                'default_selected' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $fields,
            'message' => 'Available fields retrieved successfully'
        ]);
    }

    /**
     * Get pricing validation and form data.
     */
    public function getPricingFormData(Request $request): JsonResponse
    {
      ///  $companyId = Auth::user()->company_id ?? $request->company_id;

        $data = [
            'pricing_fields' => [
                'sale_prices' => [
                    'first_sale_price' => [
                        'label' => 'سعر البيع الأول',
                        'type' => 'decimal',
                        'source' => 'invoice_lines_table',
                        'logic' => 'fetch_last_sale_price_recorded'
                    ],
                    'second_sale_price' => [
                        'label' => 'سعر البيع الثاني',
                        'type' => 'decimal',
                        'source' => 'manual_input'
                    ],
                    'third_sale_price' => [
                        'label' => 'سعر البيع الثالث',
                        'type' => 'decimal',
                        'source' => 'invoice_lines_table',
                        'logic' => 'fetch_first_sale_price_recorded'
                    ],
                    'sale_discount_rate' => [
                        'label' => 'نسبة الخصم عند البيع',
                        'type' => 'percentage',
                        'source' => 'manual_input',
                        'format' => 'must_be_percentage',
                        'max' => 100
                    ],
                    'maximum_sale_discount_rate' => [
                        'label' => 'أعلى نسبة خصم عند البيع',
                        'type' => 'percentage',
                        'source' => 'manual_input',
                        'format' => 'must_be_percentage',
                        'max' => 100
                    ],
                    'minimum_allowed_sale_price' => [
                        'label' => 'أقل سعر بيع مسموح به',
                        'type' => 'decimal',
                        'source' => 'manual_input'
                    ],
                    'sale_prices_include_vat' => [
                        'label' => 'أسعار البيع المذكورة تشمل الضريبة المضافة',
                        'type' => 'toggle',
                        'source' => 'manual_input',
                        'logic' => 'activate_tax_handling_using_tax_rate_from_sales_items_table'
                    ]
                ],
                'purchase_prices' => [
                    'first_purchase_price' => [
                        'label' => 'سعر الشراء الأول',
                        'type' => 'decimal',
                        'source' => 'purchase_items_table',
                        'logic' => 'fetch_last_purchase_price_recorded'
                    ],
                    'second_purchase_price' => [
                        'label' => 'سعر الشراء الثاني',
                        'type' => 'decimal',
                        'source' => 'purchase_items_table'
                    ],
                    'third_purchase_price' => [
                        'label' => 'سعر الشراء الثالث',
                        'type' => 'decimal',
                        'source' => 'purchase_items_table',
                        'logic' => 'fetch_first_purchase_price_recorded'
                    ],
                    'purchase_discount_rate' => [
                        'label' => 'نسبة الخصم عند الشراء',
                        'type' => 'percentage',
                        'source' => 'manual_input',
                        'format' => 'must_be_percentage',
                        'max' => 100
                    ],
                    'purchase_prices_include_vat' => [
                        'label' => 'أسعار الشراء المذكورة تشمل الضريبة المضافة',
                        'type' => 'toggle',
                        'source' => 'manual_input',
                        'logic' => 'activate_tax_handling_using_tax_rate_from_purchase_items_table'
                    ]
                ],
                'vat_info' => [
                    'item_subject_to_vat' => [
                        'label' => 'يخضع الصنف لضريبة المضافة',
                        'type' => 'toggle',
                        'source' => 'manual_input',
                        'logic' => 'activate_tax_handling_using_rate_from_tax_rates_table'
                    ]
                ]
            ],
            'validation_rules' => [
                'discount_rates_max' => 100,
                'prices_min' => 0,
                'percentage_format' => 'required_percentage_symbol'
            ],
            'external_table_references' => [
                'invoice_lines' => [
                    'fields' => ['first_sale_price'],
                    'module' => 'sales'
                ],
                'purchase_items' => [
                    'fields' => ['first_purchase_price'],
                    'module' => 'purchases'
                ],
                'sales_items' => [
                    'fields' => ['tax_rate'],
                    'module' => 'sales'
                ],

                'tax_rates' => [
                    'fields' => ['rate'],
                    'module' => 'invoices'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Pricing form data retrieved successfully',
            'message_ar' => 'تم استرداد بيانات نموذج التسعير بنجاح'
        ]);
    }

    /**
     * Validate pricing data.
     */
    public function validatePricingData(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'proposed_sale_price' => 'nullable|numeric|min:0',
            'proposed_discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

      //  $companyId = Auth::user()->company_id ?? $request->company_id;
        $item = Item::findOrFail($request->item_id);

        $validations = [];

        // Validate minimum sale price
        if ($request->has('proposed_sale_price')) {
            $validations['minimum_price_check'] = [
                'valid' => $item->validateMinimumSalePrice($request->proposed_sale_price),
                'minimum_allowed' => $item->minimum_allowed_sale_price,
                'proposed_price' => $request->proposed_sale_price,
                'message' => $item->validateMinimumSalePrice($request->proposed_sale_price)
                    ? 'السعر المقترح مقبول'
                    : 'السعر المقترح أقل من الحد الأدنى المسموح'
            ];
        }

        // Validate maximum discount rate
        if ($request->has('proposed_discount_rate')) {
            $validations['maximum_discount_check'] = [
                'valid' => $item->validateMaximumDiscountRate($request->proposed_discount_rate),
                'maximum_allowed' => $item->maximum_sale_discount_rate,
                'proposed_discount' => $request->proposed_discount_rate,
                'message' => $item->validateMaximumDiscountRate($request->proposed_discount_rate)
                    ? 'نسبة الخصم المقترحة مقبولة'
                    : 'نسبة الخصم المقترحة تتجاوز الحد الأقصى المسموح'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $validations,
            'message' => 'Pricing validation completed',
            'message_ar' => 'تم التحقق من صحة التسعير'
        ]);
    }

    /**
     * Get barcode types for dropdown.
     */
    public function getBarcodeTypes(): JsonResponse
    {
        $barcodeTypes = Item::getAvailableBarcodeTypes();
        $defaultType = Item::getDefaultBarcodeType();

        $options = collect($barcodeTypes)->map(function ($name, $code) use ($defaultType) {
            return [
                'value' => $code,
                'label' => $name,
                'code' => $code,
                'is_default' => $code === $defaultType,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $options,
            'default_type' => $defaultType,
            'supported_types' => [
                'C128' => 'Code 128 - كود 128',
                'EAN13' => 'EAN-13 - إي إيه إن-13',
                'C39' => 'Code 39 - كود 39',
                'UPCA' => 'UPC-A - يو بي سي-إيه',
                'ITF' => 'Interleaved 2 of 5 - متداخل 2 من 5',
            ],
            'message' => 'Barcode types retrieved successfully',
            'message_ar' => 'تم استرداد أنواع الباركود بنجاح'
        ]);
    }

    /**
     * Get item types for dropdown.
     */
    public function getItemTypes(Request $request): JsonResponse
    {
       // $companyId = Auth::user()->company_id ?? $request->company_id;

        $itemTypes = \Modules\Inventory\Models\ItemType::active()

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
        $systemTypes = collect(\Modules\Inventory\Models\ItemType::getSystemTypes())->map(function ($nameAr, $code) {
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
            'message' => 'Item types retrieved successfully',
            'message_ar' => 'تم استرداد أنواع الأصناف بنجاح'
        ]);
    }

    /**
     * Generate barcode for item (PNG or SVG).
     */
    public function generateBarcode(Request $request, $id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $item = Item::forCompany($companyId)->findOrFail($id);

        $request->validate([
            'width' => 'nullable|integer|min:1|max:10',
            'height' => 'nullable|integer|min:10|max:100',
            'format' => 'nullable|string|in:png,svg',
            'color' => 'nullable|string',
        ]);

        try {
            $format = strtolower($request->get('format', 'png'));

            $options = [
                'w' => $request->get('width', 2),
                'h' => $request->get('height', 30),
                'format' => $format,
            ];

            // Set color based on format
            if ($format === 'svg') {
                $options['color'] = $request->get('color', 'black'); // SVG uses color names/hex
            } else {
                $options['color'] = [0, 0, 0]; // PNG uses RGB array
            }

            $barcodeImage = $item->generateBarcode($options);

            // Prepare response based on format
            if ($format === 'svg') {
                $dataUri = 'data:image/svg+xml;base64,' . base64_encode($barcodeImage);
            } else {
                $dataUri = 'data:image/png;base64,' . base64_encode($barcodeImage);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'item_id' => $item->id,
                    'barcode' => $item->barcode,
                    'barcode_type' => $item->barcode_type,
                    'barcode_type_display' => $item->barcode_type_display,
                    'format' => $format,
                    'image' => $dataUri,
                    'raw_svg' => $format === 'svg' ? $barcodeImage : null, // Include raw SVG for direct use
                ],
                'message' => 'Barcode generated successfully',
                'message_ar' => 'تم إنشاء الباركود بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate barcode: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء الباركود: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Create custom item type.
     */
    public function createCustomItemType(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
        ]);

        try {
            // Generate unique code from name
            $code = strtolower(str_replace(' ', '_', $request->name));

            // Ensure unique code globally (not company-specific)
            $originalCode = $code;
            $counter = 1;
            while (\Modules\Inventory\Models\ItemType::where('code', $code)->exists()) {
                $code = $originalCode . '_' . $counter;
                $counter++;
            }

            // Create custom item type without company restriction
            $itemType = \Modules\Inventory\Models\ItemType::create([
                'company_id' => 1, // Use a default company ID or make it nullable in migration
                'code' => $code,
                'name' => $request->name,
                'name_ar' => $request->name_ar ?? $request->name,
                'description' => "Custom item type: {$request->name}",
                'description_ar' => "نوع صنف مخصص: " . ($request->name_ar ?? $request->name),
                'is_system' => false,
                'is_active' => true,
                'sort_order' => \Modules\Inventory\Models\ItemType::max('sort_order') + 1,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'value' => $itemType->code,
                    'label' => $itemType->display_name,
                    'id' => $itemType->id,
                    'is_system' => false,
                ],
                'message' => 'Custom item type created successfully',
                'message_ar' => 'تم إنشاء نوع الصنف المخصص بنجاح'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create custom item type: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء نوع الصنف المخصص: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate SVG barcode for item specifically.
     */
    public function generateBarcodeSVG(Request $request, $id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;
        $item = Item::
        // forCompany($companyId)->
        findOrFail($id);

        $request->validate([
            'width' => 'nullable|integer|min:1|max:10',
            'height' => 'nullable|integer|min:10|max:100',
            'color' => 'nullable|string',
        ]);

        try {
            $options = [
                'w' => $request->get('width', 2),
                'h' => $request->get('height', 30),
                'color' => $request->get('color', 'black'),
            ];

            $svgBarcode = $item->generateBarcodeSVG($options);

            return response()->json([
                'success' => true,
                'data' => [
                    'item_id' => $item->id,
                    'barcode' => $item->barcode,
                    'barcode_type' => $item->barcode_type,
                    'barcode_type_display' => $item->barcode_type_display,
                    'format' => 'svg',
                    'svg_content' => $svgBarcode,
                    'data_uri' => 'data:image/svg+xml;base64,' . base64_encode($svgBarcode),
                    'inline_svg' => $svgBarcode, // Raw SVG for direct HTML insertion
                ],
                'message' => 'SVG Barcode generated successfully',
                'message_ar' => 'تم إنشاء الباركود SVG بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate SVG barcode: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء الباركود SVG: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Validate barcode format.
     */
    public function validateBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string',
            'barcode_type' => 'required|string|in:C128,EAN13,C39,UPCA,ITF',
        ]);

        // Create a temporary item to use validation method
        $tempItem = new Item([
            'barcode' => $request->barcode,
            'barcode_type' => $request->barcode_type,
        ]);

        $validation = $tempItem->validateBarcode();

        return response()->json([
            'success' => true,
            'data' => [
                'barcode' => $request->barcode,
                'barcode_type' => $request->barcode_type,
                'barcode_type_display' => Item::BARCODE_TYPE_OPTIONS[$request->barcode_type] ?? $request->barcode_type,
                'validation' => $validation,
            ],
            'message' => 'Barcode validation completed',
            'message_ar' => 'تم التحقق من صحة الباركود'
        ]);
    }

    /**
     * Get all transactions/movements for a specific item.
     */
    public function getItemTransactions(Request $request, $id): JsonResponse
    {
        // $companyId = Auth::user()->company_id ?? $request->company_id;
        $item = Item::
        // forCompany($companyId)->
        findOrFail($id);

        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'transaction_type' => 'nullable|string|in:sales,purchases,stock_movements,all',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string|in:date,type,quantity,amount',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        try {
            $transactions = collect();
            $transactionType = $request->get('transaction_type', 'all');
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;
            $sortBy = $request->get('sort_by', 'date');
            $sortDirection = $request->get('sort_direction', 'desc');

            // Fetch Sales Transactions
            if ($transactionType === 'all' || $transactionType === 'sales') {
                $salesTransactions = $this->getSalesTransactions($item->id, $dateFrom, $dateTo);
                $transactions = $transactions->merge($salesTransactions);
            }

            // Fetch Purchase Transactions
            if ($transactionType === 'all' || $transactionType === 'purchases') {
                $purchaseTransactions = $this->getPurchaseTransactions($item->id, $dateFrom, $dateTo);
                $transactions = $transactions->merge($purchaseTransactions);
            }

            // Fetch Stock Movement Transactions
            if ($transactionType === 'all' || $transactionType === 'stock_movements') {
                $stockMovements = $this->getStockMovements($item->id, $dateFrom, $dateTo);
                $transactions = $transactions->merge($stockMovements);
            }

            // Sort transactions
            $transactions = $this->sortTransactions($transactions, $sortBy, $sortDirection);

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $currentPage = $request->get('page', 1);
            $total = $transactions->count();
            $paginatedTransactions = $transactions->forPage($currentPage, $perPage);

            // Calculate summary statistics
            $summary = $this->calculateTransactionSummary($transactions, $item);

            return response()->json([
                'success' => true,
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'code' => $item->code,
                        'item_number' => $item->item_number,
                        'current_balance' => $item->balance,
                        'unit' => $item->unit ? $item->unit->name : null,
                    ],
                    'transactions' => $paginatedTransactions->values(),
                    'summary' => $summary,
                    'pagination' => [
                        'current_page' => $currentPage,
                        'per_page' => $perPage,
                        'total' => $total,
                        'last_page' => ceil($total / $perPage),
                        'from' => ($currentPage - 1) * $perPage + 1,
                        'to' => min($currentPage * $perPage, $total),
                    ],
                    'filters' => [
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'transaction_type' => $transactionType,
                        'sort_by' => $sortBy,
                        'sort_direction' => $sortDirection,
                    ]
                ],
                'message' => 'Item transactions retrieved successfully',
                'message_ar' => 'تم استرداد حركات الصنف بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve item transactions: ' . $e->getMessage(),
                'message_ar' => 'فشل في استرداد حركات الصنف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales transactions for an item.
     */
    private function getSalesTransactions($itemId, $dateFrom = null, $dateTo = null): \Illuminate\Support\Collection
    {
        $query = DB::table('sales_items')
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales_items.item_id', $itemId);

        if ($dateFrom) {
            $query->whereDate('sales.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('sales.created_at', '<=', $dateTo);
        }

        $salesItems = $query->select([
            'sales.id as transaction_id',
            'sales.invoice_number as document_number',
            'sales.created_at as transaction_date',
            'sales_items.quantity',
            'sales_items.unit_price',
            'sales_items.total',
            'sales_items.discount_rate',
            'customers.company_name as customer_name',
            'sales.notes',
            'sales.status'
        ])->get();

        return $salesItems->map(function ($item) {
            // Calculate discount amount from discount rate
            $discountAmount = ($item->unit_price * $item->quantity * $item->discount_rate) / 100;

            return [
                'id' => 'sale_' . $item->transaction_id,
                'type' => 'sale',
                'type_ar' => 'مبيعات',
                'document_number' => $item->document_number,
                'transaction_date' => $item->transaction_date,
                'quantity' => -abs($item->quantity), // Negative for outgoing
                'unit_price' => $item->unit_price,
                'total_amount' => $item->total,
                'discount_amount' => $discountAmount,
                'net_amount' => $item->total - $discountAmount,
                'reference' => $item->customer_name ?? 'عميل غير محدد',
                'notes' => $item->notes,
                'status' => $item->status,
                'status_ar' => $this->getStatusArabic($item->status),
                'direction' => 'out',
                'direction_ar' => 'صادر',
                'icon' => 'sale',
                'color' => 'success'
            ];
        });
    }

    /**
     * Get purchase transactions for an item.
     */
    private function getPurchaseTransactions($itemId, $dateFrom = null, $dateTo = null): \Illuminate\Support\Collection
    {
        $query = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->where('purchase_items.item_id', $itemId);

        if ($dateFrom) {
            $query->whereDate('purchases.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('purchases.created_at', '<=', $dateTo);
        }

        $purchaseItems = $query->select([
            'purchases.id as transaction_id',
            'purchases.journal_number as document_number',
            'purchases.created_at as transaction_date',
            'purchase_items.quantity',
            'purchase_items.unit_price',
            'purchase_items.total',
            'purchase_items.discount_rate',
            'suppliers.supplier_name_ar as supplier_name',
            'purchases.notes',
            'purchases.status'
        ])->get();

        return $purchaseItems->map(function ($item) {
            // Calculate discount amount from discount rate
            $discountAmount = ($item->unit_price * $item->quantity * $item->discount_rate) / 100;

            return [
                'id' => 'purchase_' . $item->transaction_id,
                'type' => 'purchase',
                'type_ar' => 'مشتريات',
                'document_number' => $item->document_number,
                'transaction_date' => $item->transaction_date,
                'quantity' => abs($item->quantity), // Positive for incoming
                'unit_price' => $item->unit_price,
                'total_amount' => $item->total,
                'discount_amount' => $discountAmount,
                'net_amount' => $item->total - $discountAmount,
                'reference' => $item->supplier_name ?? 'مورد غير محدد',
                'notes' => $item->notes,
                'status' => $item->status,
                'status_ar' => $this->getStatusArabic($item->status),
                'direction' => 'in',
                'direction_ar' => 'وارد',
                'icon' => 'purchase',
                'color' => 'primary'
            ];
        });
    }

    /**
     * Get stock movements for an item.
     */
    private function getStockMovements($itemId, $dateFrom = null, $dateTo = null): \Illuminate\Support\Collection
    {
        $query = DB::table('stock_movements')
            ->leftJoin('warehouses', 'stock_movements.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('users', 'stock_movements.created_by', '=', 'users.id')
            ->where('stock_movements.item_id', $itemId);

        if ($dateFrom) {
            $query->where('stock_movements.transaction_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('stock_movements.transaction_date', '<=', $dateTo);
        }

        $stockMovements = $query->select([
            'stock_movements.id as transaction_id',
            'stock_movements.id as document_number',
            'stock_movements.transaction_date',
            'stock_movements.quantity',
            'stock_movements.movement_type',
            'stock_movements.type',
            'stock_movements.notes',
            'warehouses.name as warehouse_name',
            DB::raw("CONCAT(users.first_name, ' ', users.second_name) as created_by_name")
        ])->get();

        return $stockMovements->map(function ($item) {
            $movementTypeAr = $this->getMovementTypeArabic($item->movement_type);
            $isIncoming = $item->movement_type === 'in';

            return [
                'id' => 'stock_' . $item->transaction_id,
                'type' => 'stock_movement',
                'type_ar' => 'حركة مخزون',
                'document_number' => 'STK-' . $item->document_number,
                'transaction_date' => $item->transaction_date,
                'quantity' => $isIncoming ? abs($item->quantity) : -abs($item->quantity),
                'unit_price' => null,
                'total_amount' => null,
                'discount_amount' => 0,
                'net_amount' => null,
                'reference' => $item->warehouse_name ?? 'حركة مخزون',
                'notes' => $item->notes,
                'status' => 'completed',
                'status_ar' => 'مكتمل',
                'direction' => $isIncoming ? 'in' : 'out',
                'direction_ar' => $isIncoming ? 'وارد' : 'صادر',
                'movement_type' => $item->movement_type,
                'movement_type_ar' => $movementTypeAr,
                'type_detail' => $item->type,
                'created_by' => $item->created_by_name,
                'icon' => 'stock_movement',
                'color' => $isIncoming ? 'info' : 'warning'
            ];
        });
    }

    /**
     * Sort transactions by specified field and direction.
     */
    private function sortTransactions($transactions, $sortBy, $sortDirection)
    {
        return $transactions->sortBy(function ($transaction) use ($sortBy) {
            switch ($sortBy) {
                case 'date':
                    return $transaction['transaction_date'];
                case 'type':
                    return $transaction['type'];
                case 'quantity':
                    return abs($transaction['quantity']);
                case 'amount':
                    return $transaction['total_amount'] ?? 0;
                default:
                    return $transaction['transaction_date'];
            }
        }, SORT_REGULAR, $sortDirection === 'desc');
    }

    /**
     * Calculate transaction summary statistics.
     */
    private function calculateTransactionSummary($transactions, $item)
    {
        $totalIn = $transactions->where('direction', 'in')->sum('quantity');
        $totalOut = abs($transactions->where('direction', 'out')->sum('quantity'));
        $totalSales = $transactions->where('type', 'sale')->sum('total_amount');
        $totalPurchases = $transactions->where('type', 'purchase')->sum('total_amount');

        $salesCount = $transactions->where('type', 'sale')->count();
        $purchasesCount = $transactions->where('type', 'purchase')->count();
        $stockMovementsCount = $transactions->where('type', 'stock_movement')->count();

        return [
            'current_balance' => $item->balance,
            'total_transactions' => $transactions->count(),
            'quantity_summary' => [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'net_movement' => $totalIn - $totalOut,
            ],
            'amount_summary' => [
                'total_sales_amount' => $totalSales,
                'total_purchases_amount' => $totalPurchases,
                'net_amount' => $totalSales - $totalPurchases,
            ],
            'transaction_counts' => [
                'sales' => $salesCount,
                'purchases' => $purchasesCount,
                'stock_movements' => $stockMovementsCount,
            ],
            'transaction_counts_ar' => [
                'مبيعات' => $salesCount,
                'مشتريات' => $purchasesCount,
                'حركات مخزون' => $stockMovementsCount,
            ]
        ];
    }

    /**
     * Get status in Arabic.
     */
    private function getStatusArabic($status)
    {
        $statusMap = [
            'pending' => 'معلق',
            'confirmed' => 'مؤكد',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'draft' => 'مسودة',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Get movement type in Arabic.
     */
    private function getMovementTypeArabic($movementType)
    {
        $movementTypeMap = [
            'adjustment_in' => 'تسوية وارد',
            'adjustment_out' => 'تسوية صادر',
            'transfer_in' => 'تحويل وارد',
            'transfer_out' => 'تحويل صادر',
            'return_in' => 'مرتجع وارد',
            'return_out' => 'مرتجع صادر',
            'damage' => 'تالف',
            'expired' => 'منتهي الصلاحية',
            'lost' => 'مفقود',
            'found' => 'موجود',
        ];

        return $movementTypeMap[$movementType] ?? $movementType;
    }

    /**
     * Get stock movement reference description.
     */
    private function getStockMovementReference($item)
    {
        if ($item->warehouse_name) {
            return "مستودع: {$item->warehouse_name}";
        } elseif ($item->created_by_name) {
            return "بواسطة {$item->created_by_name}";
        } else {
            return 'حركة مخزون';
        }
    }

    /**
     * Export item transactions to Excel.
     */
    public function exportItemTransactions(Request $request, $id)
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $item = Item::forCompany($companyId)->findOrFail($id);

        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'transaction_type' => 'nullable|string|in:sales,purchases,stock_movements,all',
            'export_type' => 'nullable|string|in:transactions,summary,multi_sheet',
            'format' => 'nullable|string|in:xlsx,csv,pdf',
        ]);

        try {
            $transactions = collect();
            $transactionType = $request->get('transaction_type', 'all');
            $exportType = $request->get('export_type', 'multi_sheet');
            $format = $request->get('format', 'xlsx');
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            // Fetch transactions based on type
            if ($transactionType === 'all' || $transactionType === 'sales') {
                $salesTransactions = $this->getSalesTransactions($item->id, $dateFrom, $dateTo);
                $transactions = $transactions->merge($salesTransactions);
            }

            if ($transactionType === 'all' || $transactionType === 'purchases') {
                $purchaseTransactions = $this->getPurchaseTransactions($item->id, $dateFrom, $dateTo);
                $transactions = $transactions->merge($purchaseTransactions);
            }

            if ($transactionType === 'all' || $transactionType === 'stock_movements') {
                $stockMovements = $this->getStockMovements($item->id, $dateFrom, $dateTo);
                $transactions = $transactions->merge($stockMovements);
            }

            // Sort transactions by date (newest first)
            $transactions = $this->sortTransactions($transactions, 'date', 'desc');

            // Calculate summary
            $summary = $this->calculateTransactionSummary($transactions, $item);

            // Prepare item data
            $itemData = [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'item_number' => $item->item_number,
                'current_balance' => $item->balance,
                'unit' => $item->unit ? $item->unit->name : null,
            ];

            // Prepare filters data
            $filters = [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'transaction_type' => $transactionType,
                'export_date' => now()->format('Y-m-d H:i:s'),
            ];

            // Generate filename
            $filename = $this->generateExportFilename($item, $transactionType, $format);

            // Export based on type
            switch ($exportType) {
                case 'transactions':
                    return Excel::download(
                        new ItemTransactionsExport($transactions, $itemData, $summary, $filters),
                        $filename
                    );

                case 'summary':
                    return Excel::download(
                        new ItemTransactionsSummaryExport($itemData, $summary, $filters),
                        $filename
                    );

                case 'multi_sheet':
                default:
                    return Excel::download(
                        new ItemTransactionsMultiSheetExport($transactions, $itemData, $summary, $filters),
                        $filename
                    );
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export item transactions: ' . $e->getMessage(),
                'message_ar' => 'فشل في تصدير حركات الصنف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate export filename.
     */
    private function generateExportFilename($item, $transactionType, $format): string
    {
        $typeMap = [
            'all' => 'جميع_الحركات',
            'sales' => 'المبيعات',
            'purchases' => 'المشتريات',
            'stock_movements' => 'حركات_المخزون'
        ];

        $typeName = $typeMap[$transactionType] ?? 'الحركات';
        $date = now()->format('Y-m-d');

        return "حركات_الصنف_{$item->code}_{$typeName}_{$date}.{$format}";
    }
}
