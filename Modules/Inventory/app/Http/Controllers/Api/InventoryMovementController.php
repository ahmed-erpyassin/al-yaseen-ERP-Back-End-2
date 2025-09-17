<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryMovementData;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Http\Requests\StoreInventoryMovementRequest;
use Modules\Inventory\Http\Requests\UpdateInventoryMovementRequest;
use Illuminate\Support\Facades\DB;

/**
 * @group Inventory Management / Inventory Movements
 *
 * APIs for managing inventory movements, including warehouse transfers, adjustments, and movement tracking.
 */
class InventoryMovementController extends Controller
{
    /**
     * ✅ Display a listing of inventory movements.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $query = InventoryMovement::with([
            'company', 'user', 'warehouse', 'movementData.item', 'movementData.unit',
            'vendor', 'customer', 'creator', 'updater'
        ])->forCompany($companyId);

        // ✅ Enhanced Search functionality (Movement Number / Date / Customer or Vendor / Movement Type)
        if ($request->has('search')) {
            $search = strtolower($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(movement_number) LIKE ?', ["%{$search}%"])     // ✅ Movement Number
                  ->orWhereRaw('LOWER(movement_description) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(reference) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(invoice_number) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(shipment_number) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(vendor_name) LIKE ?', ["%{$search}%"])       // ✅ Vendor
                  ->orWhereRaw('LOWER(customer_name) LIKE ?', ["%{$search}%"])     // ✅ Customer
                  ->orWhereRaw('LOWER(movement_type) LIKE ?', ["%{$search}%"])     // ✅ Movement Type
                  ->orWhere('movement_date', 'like', "%{$search}%");               // ✅ Date
            });
        }

        // ✅ Specific field searches
        if ($request->has('movement_number')) {
            $query->whereRaw('LOWER(movement_number) LIKE ?', ['%' . strtolower($request->get('movement_number')) . '%']);
        }

        if ($request->has('vendor_customer')) {
            $vendorCustomer = strtolower($request->get('vendor_customer'));
            $query->where(function ($q) use ($vendorCustomer) {
                $q->whereRaw('LOWER(vendor_name) LIKE ?', ["%{$vendorCustomer}%"])
                  ->orWhereRaw('LOWER(customer_name) LIKE ?', ["%{$vendorCustomer}%"]);
            });
        }

        // ✅ Filter by movement type
        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->get('movement_type'));
        }

        // ✅ Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->get('warehouse_id'));
        }

        // ✅ Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // ✅ Filter by date range
        if ($request->has('date_from')) {
            $query->where('movement_date', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('movement_date', '<=', $request->get('date_to'));
        }

        // ✅ Enhanced Sorting - Support for all inventory movement table columns
        $sortBy = $request->get('sort_by', 'movement_date');
        $sortDirection = $request->get('sort_direction', 'desc');

        $sortableColumns = [
            'id', 'movement_number', 'movement_type', 'movement_date', 'movement_time',
            'vendor_name', 'customer_name', 'movement_description', 'user_number',
            'shipment_number', 'invoice_number', 'reference', 'warehouse_id',
            'status', 'total_quantity', 'total_value', 'total_items',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('movement_date', 'desc'); // Default global sorting
        }

        // ✅ Pagination
        $perPage = $request->get('per_page', 15);
        $movements = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movements,
            'message' => 'Inventory movements retrieved successfully',
            'message_ar' => 'تم استرداد حركات المخزون بنجاح'
        ]);
    }

    /**
     * ✅ Store a newly created inventory movement (Add Warehouse Movement).
     */
    public function store(StoreInventoryMovementRequest $request): JsonResponse
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
            $data['status'] = $data['status'] ?? 'draft';

            // ✅ Auto-generate sequential movement number (linked to warehouse movement)
            $data['movement_number'] = $this->generateSequentialMovementNumber($companyId, $data['warehouse_id'], $data['movement_type']);

            // ✅ Set automatic date and time on insert
            $data['movement_date'] = now()->toDateString();
            $data['movement_time'] = now()->toTimeString();
            $data['movement_datetime'] = now();

            // ✅ Get warehouse information
            $warehouse = Warehouse::find($data['warehouse_id']);
            if ($warehouse) {
                $data['warehouse_number'] = $warehouse->warehouse_number;
                $data['warehouse_name'] = $warehouse->name;
            }

            // ✅ Generate movement description based on movement type
            $data['movement_description'] = $this->generateMovementDescription($data['movement_type'], $data['movement_description'] ?? null);

            // ✅ Handle vendor (only for Outbound and Inbound movement types)
            if (!in_array($data['movement_type'], ['outbound', 'inbound'])) {
                $data['vendor_id'] = null;
                $data['vendor_name'] = null;
            }

            // ✅ Extract movement data
            $movementDataItems = $data['movement_data'] ?? [];
            unset($data['movement_data']);

            // ✅ Create movement header
            $movement = InventoryMovement::create($data);

            // ✅ Create movement data items with serial numbers
            $totalQuantity = 0;
            $totalValue = 0;
            $totalItems = 0;
            $serialNumber = 1;

            foreach ($movementDataItems as $itemData) {
                $itemData['company_id'] = $companyId;
                $itemData['inventory_movement_id'] = $movement->id;
                $itemData['warehouse_id'] = $data['warehouse_id'];
                $itemData['warehouse_number'] = $data['warehouse_number'] ?? null;
                $itemData['warehouse_name'] = $data['warehouse_name'] ?? null;
                $itemData['created_by'] = $userId;

                // ✅ Add serial number
                $itemData['serial_number'] = $serialNumber++;

                // ✅ Get item information from Items table
                $item = Item::find($itemData['item_id']);
                if ($item) {
                    $itemData['item_number'] = $item->item_number;
                    $itemData['item_name'] = $item->name;
                    $itemData['item_description'] = $item->description;

                    // ✅ Get price from warehouse/item if not provided
                    if (!isset($itemData['unit_price']) || $itemData['unit_price'] == 0) {
                        $itemData['unit_price'] = $item->price ?? 0;
                    }
                }

                // ✅ Get unit information from Units table
                if (!empty($itemData['unit_id'])) {
                    $unit = Unit::find($itemData['unit_id']);
                    if ($unit) {
                        $itemData['unit_name'] = $unit->name;
                        $itemData['unit_code'] = $unit->code;
                    }
                }

                // ✅ Get quantity from Warehouses table (current stock)
                $currentStock = $this->getCurrentStock($data['warehouse_id'], $itemData['item_id']);
                $itemData['previous_quantity'] = $currentStock;

                // ✅ Set default values
                $itemData['unit_cost'] = $itemData['unit_cost'] ?? 0;
                $itemData['unit_price'] = $itemData['unit_price'] ?? 0;
                $itemData['inventory_count'] = $itemData['inventory_count'] ?? 0;

                // ✅ Create movement data item
                $movementDataItem = InventoryMovementData::create($itemData);

                // ✅ Calculate totals
                $totalQuantity += $movementDataItem->quantity;
                $totalValue += $movementDataItem->total_cost;
                $totalItems++;
            }

            // ✅ Update movement totals
            $movement->update([
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'total_items' => $totalItems,
            ]);

            // ✅ Load relationships for response
            $movement->load([
                'warehouse', 'movementData.item', 'movementData.unit',
                'vendor', 'customer', 'creator'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $movement,
                'message' => 'Warehouse movement created successfully',
                'message_ar' => 'تم إنشاء حركة المخزن بنجاح'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create warehouse movement: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء حركة المخزن: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Display the specified inventory movement.
     */
    public function show($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $movement = InventoryMovement::with([
            'company', 'user', 'warehouse',
            'movementData.item', 'movementData.unit', 'movementData.warehouse',
            'vendor', 'customer', 'inboundInvoice', 'outboundInvoice',
            'creator', 'updater', 'confirmer'
        ])->forCompany($companyId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => 'Inventory movement retrieved successfully',
            'message_ar' => 'تم استرداد حركة المخزون بنجاح'
        ]);
    }

    /**
     * ✅ Generate sequential movement number linked to warehouse movement.
     */
    private function generateSequentialMovementNumber($companyId, $warehouseId, $movementType): string
    {
        $prefixes = [
            'outbound' => 'OUT',
            'inbound' => 'IN',
            'transfer' => 'TR',
            'manufacturing' => 'MF',
            'inventory_count' => 'IC'
        ];

        $prefix = $prefixes[$movementType] ?? 'MOV';
        $year = date('Y');
        $month = date('m');

        // ✅ Get the next sequential number for this warehouse and type
        $lastMovement = InventoryMovement::where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->where('movement_type', $movementType)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastMovement) {
            // Extract number from movement_number (e.g., "IN-202501-0005" -> 5)
            $parts = explode('-', $lastMovement->movement_number);
            if (count($parts) >= 3) {
                $nextNumber = intval(end($parts)) + 1;
            }
        }

        $nextNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}{$month}-{$nextNumber}";
    }

    /**
     * ✅ Generate movement description based on movement type.
     */
    private function generateMovementDescription($movementType, $customDescription = null): string
    {
        if ($customDescription) {
            return $customDescription;
        }

        $descriptions = [
            'outbound' => 'صادر - بيع البضائع (أصناف غذائية)',
            'inbound' => 'وارد - استلام البضائع من المورد',
            'transfer' => 'تحويل - نقل البضائع بين المخازن',
            'manufacturing' => 'تصنيع - إنتاج البضائع من المواد الخام',
            'inventory_count' => 'جرد - عد مخزون المخزن'
        ];

        return $descriptions[$movementType] ?? 'حركة مخزن';
    }

    /**
     * ✅ Get current stock quantity for item in warehouse.
     */
    private function getCurrentStock($warehouseId, $itemId): float
    {
        // ✅ This would typically query a stock/inventory table
        // For now, return 0 as placeholder
        try {
            // Example: Get from warehouse_stocks table or calculate from movements
            $currentStock = InventoryMovementData::where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->sum('quantity'); // This is simplified - actual implementation would be more complex

            return $currentStock ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ✅ Generate unique movement number (legacy method).
     */
    private function generateMovementNumber($companyId, $movementType): string
    {
        $prefixes = [
            'inbound' => 'IN-',
            'outbound' => 'OUT-',
            'transfer' => 'TR-',
            'manufacturing' => 'MF-',
            'inventory_count' => 'IC-'
        ];

        $prefix = $prefixes[$movementType] ?? 'MV-';
        $year = date('Y');
        $month = date('m');

        // Get the last movement number for this company and type
        $lastMovement = InventoryMovement::where('company_id', $companyId)
            ->where('movement_type', $movementType)
            ->where('movement_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('movement_number', 'desc')
            ->first();

        if ($lastMovement) {
            // Extract the sequence number and increment
            $lastNumber = substr($lastMovement->movement_number, -4);
            $nextNumber = str_pad((int)$lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$year}{$month}-{$nextNumber}";
    }

    /**
     * ✅ Update the specified inventory movement.
     */
    public function update(UpdateInventoryMovementRequest $request, $id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;

        try {
            DB::beginTransaction();

            $movement = InventoryMovement::forCompany($companyId)->findOrFail($id);

            // ✅ Check if movement can be updated
            if ($movement->is_confirmed && $movement->status === 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update confirmed movement',
                    'message_ar' => 'لا يمكن تعديل حركة مؤكدة'
                ], 422);
            }

            // ✅ Get validated data
            $data = $request->validated();
            $data['updated_by'] = $userId;

            // ✅ Get warehouse information if changed
            if (isset($data['warehouse_id']) && $data['warehouse_id'] != $movement->warehouse_id) {
                $warehouse = Warehouse::find($data['warehouse_id']);
                if ($warehouse) {
                    $data['warehouse_number'] = $warehouse->warehouse_number;
                    $data['warehouse_name'] = $warehouse->name;
                }
            }

            // ✅ Handle movement data updates
            if (isset($data['movement_data'])) {
                $movementDataItems = $data['movement_data'];
                unset($data['movement_data']);

                // ✅ Update movement data
                $this->updateMovementData($movement, $movementDataItems, $companyId, $userId);
            }

            // ✅ Update movement header
            $movement->update($data);

            // ✅ Recalculate totals
            $this->recalculateMovementTotals($movement);

            // ✅ Load relationships for response
            $movement->load([
                'warehouse', 'movementData.item', 'movementData.unit',
                'vendor', 'customer', 'updater'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $movement,
                'message' => 'Inventory movement updated successfully',
                'message_ar' => 'تم تحديث حركة المخزون بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory movement: ' . $e->getMessage(),
                'message_ar' => 'فشل في تحديث حركة المخزون: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Remove the specified inventory movement (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        $userId = auth()->id() ?? request()->user_id;

        try {
            DB::beginTransaction();

            $movement = InventoryMovement::forCompany($companyId)->findOrFail($id);

            // ✅ Check if movement can be deleted
            if ($movement->is_confirmed && $movement->status === 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete confirmed movement',
                    'message_ar' => 'لا يمكن حذف حركة مؤكدة'
                ], 422);
            }

            // ✅ Set deleted_by before soft delete
            $movement->update(['deleted_by' => $userId]);
            $movement->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory movement deleted successfully',
                'message_ar' => 'تم حذف حركة المخزون بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inventory movement: ' . $e->getMessage(),
                'message_ar' => 'فشل في حذف حركة المخزون: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Confirm inventory movement.
     */
    public function confirm($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        $userId = auth()->id() ?? request()->user_id;

        try {
            DB::beginTransaction();

            $movement = InventoryMovement::forCompany($companyId)->findOrFail($id);

            if ($movement->is_confirmed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movement is already confirmed',
                    'message_ar' => 'الحركة مؤكدة مسبقاً'
                ], 422);
            }

            // ✅ Confirm the movement
            $movement->update([
                'status' => 'confirmed',
                'is_confirmed' => true,
                'confirmed_at' => now(),
                'confirmed_by' => $userId,
                'updated_by' => $userId,
            ]);

            // ✅ Here you would typically update inventory stock levels
            // This will be implemented when stock management is added

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $movement->fresh(['warehouse', 'movementData', 'confirmer']),
                'message' => 'Inventory movement confirmed successfully',
                'message_ar' => 'تم تأكيد حركة المخزون بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm inventory movement: ' . $e->getMessage(),
                'message_ar' => 'فشل في تأكيد حركة المخزون: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get form data for inventory movement.
     */
    public function getFormData(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        try {
            $data = [
                // ✅ Movement types
                'movement_types' => InventoryMovement::MOVEMENT_TYPES,

                // ✅ Status options
                'status_options' => InventoryMovement::STATUS_OPTIONS,

                // ✅ Warehouses dropdown
                'warehouses' => $this->getWarehousesDropdown($companyId),

                // ✅ Items dropdown
                'items' => $this->getItemsDropdown($companyId),

                // ✅ Units dropdown
                'units' => $this->getUnitsDropdown($companyId),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Form data retrieved successfully',
                'message_ar' => 'تم استرداد بيانات النموذج بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'movement_types' => InventoryMovement::MOVEMENT_TYPES,
                    'status_options' => InventoryMovement::STATUS_OPTIONS,
                    'warehouses' => [],
                    'items' => [],
                    'units' => [],
                ],
                'message' => 'Form data retrieved (some dropdowns may be empty)',
                'message_ar' => 'تم استرداد بيانات النموذج (بعض القوائم قد تكون فارغة)'
            ]);
        }
    }

    /**
     * ✅ Update movement data items with enhanced functionality.
     */
    private function updateMovementData($movement, $movementDataItems, $companyId, $userId)
    {
        $existingItemIds = [];
        $serialNumber = 1;

        foreach ($movementDataItems as $itemData) {
            if (isset($itemData['_delete']) && $itemData['_delete']) {
                // ✅ Soft delete marked items
                if (isset($itemData['id'])) {
                    $movementDataItem = InventoryMovementData::where('id', $itemData['id'])
                        ->where('inventory_movement_id', $movement->id)
                        ->first();
                    if ($movementDataItem) {
                        $movementDataItem->update(['deleted_by' => $userId]);
                        $movementDataItem->delete(); // Soft delete
                    }
                }
                continue;
            }

            $itemData['company_id'] = $companyId;
            $itemData['inventory_movement_id'] = $movement->id;
            $itemData['warehouse_id'] = $movement->warehouse_id;
            $itemData['warehouse_number'] = $movement->warehouse_number;
            $itemData['warehouse_name'] = $movement->warehouse_name;

            // ✅ Add serial number if not provided
            if (!isset($itemData['serial_number'])) {
                $itemData['serial_number'] = $serialNumber++;
            }

            // ✅ Get item information from Items table
            $item = Item::find($itemData['item_id']);
            if ($item) {
                $itemData['item_number'] = $item->item_number;
                $itemData['item_name'] = $item->name;
                $itemData['item_description'] = $item->description;

                // ✅ Get price from warehouse/item if not provided
                if (!isset($itemData['unit_price']) || $itemData['unit_price'] == 0) {
                    $itemData['unit_price'] = $item->price ?? 0;
                }
            }

            // ✅ Get unit information from Units table
            if (!empty($itemData['unit_id'])) {
                $unit = Unit::find($itemData['unit_id']);
                if ($unit) {
                    $itemData['unit_name'] = $unit->name;
                    $itemData['unit_code'] = $unit->code;
                }
            }

            // ✅ Get current stock quantity from Warehouses table
            if (!isset($itemData['previous_quantity'])) {
                $itemData['previous_quantity'] = $this->getCurrentStock($movement->warehouse_id, $itemData['item_id']);
            }

            // ✅ Set default values
            $itemData['unit_cost'] = $itemData['unit_cost'] ?? 0;
            $itemData['unit_price'] = $itemData['unit_price'] ?? 0;
            $itemData['inventory_count'] = $itemData['inventory_count'] ?? 0;

            if (isset($itemData['id']) && $itemData['id']) {
                // ✅ Update existing item
                $itemData['updated_by'] = $userId;
                InventoryMovementData::where('id', $itemData['id'])
                    ->where('inventory_movement_id', $movement->id)
                    ->update($itemData);
                $existingItemIds[] = $itemData['id'];
            } else {
                // ✅ Create new item
                $itemData['created_by'] = $userId;
                $newItem = InventoryMovementData::create($itemData);
                $existingItemIds[] = $newItem->id;
            }
        }

        // ✅ Soft delete items not in the update list
        InventoryMovementData::where('inventory_movement_id', $movement->id)
            ->whereNotIn('id', $existingItemIds)
            ->update(['deleted_by' => $userId]);

        InventoryMovementData::where('inventory_movement_id', $movement->id)
            ->whereNotIn('id', $existingItemIds)
            ->delete(); // Soft delete
    }

    /**
     * ✅ Recalculate movement totals.
     */
    private function recalculateMovementTotals($movement)
    {
        $movementData = $movement->movementData;

        $totalQuantity = $movementData->sum('quantity');
        $totalValue = $movementData->sum('total_cost');
        $totalItems = $movementData->count();

        $movement->update([
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'total_items' => $totalItems,
        ]);
    }

    /**
     * ✅ Get warehouses for dropdown.
     */
    private function getWarehousesDropdown($companyId): array
    {
        try {
            return Warehouse::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'warehouse_number', 'name')
                ->orderBy('name')
                ->get()
                ->map(function ($warehouse) {
                    return [
                        'value' => $warehouse->id,
                        'label' => ($warehouse->warehouse_number ? $warehouse->warehouse_number . ' - ' : '') . $warehouse->name,
                        'warehouse_number' => $warehouse->warehouse_number,
                        'name' => $warehouse->name,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ Get items for dropdown.
     */
    private function getItemsDropdown($companyId): array
    {
        try {
            return Item::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'item_number', 'name', 'description')
                ->orderBy('name')
                ->get()
                ->map(function ($item) {
                    return [
                        'value' => $item->id,
                        'label' => ($item->item_number ? $item->item_number . ' - ' : '') . $item->name,
                        'item_number' => $item->item_number,
                        'name' => $item->name,
                        'description' => $item->description,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ Get units for dropdown.
     */
    private function getUnitsDropdown($companyId): array
    {
        try {
            return Unit::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get()
                ->map(function ($unit) {
                    return [
                        'value' => $unit->id,
                        'label' => $unit->name . ($unit->code ? ' (' . $unit->code . ')' : ''),
                        'name' => $unit->name,
                        'code' => $unit->code,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ Filter inventory movements by specific field value (Selection-Driven Display).
     */
    public function filterByField(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        $field = $request->get('field');
        $value = $request->get('value');

        // ✅ Validate filterable fields
        $filterableFields = [
            'movement_number', 'movement_type', 'movement_date', 'vendor_name',
            'customer_name', 'movement_description', 'user_number', 'shipment_number',
            'invoice_number', 'reference', 'status'
        ];

        if (!in_array($field, $filterableFields)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid field for filtering',
                'message_ar' => 'حقل غير صالح للتصفية'
            ], 422);
        }

        $query = InventoryMovement::with([
            'company', 'user', 'warehouse', 'movementData.item', 'movementData.unit',
            'vendor', 'customer', 'creator', 'updater'
        ])->forCompany($companyId);

        // ✅ Apply field-specific filter (case-insensitive)
        if ($field === 'movement_date') {
            $query->where($field, 'like', '%' . $value . '%');
        } else {
            $query->whereRaw("LOWER({$field}) LIKE ?", ['%' . strtolower($value) . '%']);
        }

        // ✅ Apply sorting
        $sortBy = $request->get('sort_by', 'movement_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // ✅ Paginate results
        $perPage = $request->get('per_page', 15);
        $movements = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movements,
            'filter' => [
                'field' => $field,
                'value' => $value
            ],
            'message' => "Inventory movements filtered by {$field}",
            'message_ar' => "تم تصفية حركات المخزون حسب {$field}"
        ]);
    }

    /**
     * ✅ Duplicate inventory movement with new date and time.
     */
    public function duplicate($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;
        $userId = auth()->id() ?? request()->user_id;

        try {
            DB::beginTransaction();

            $originalMovement = InventoryMovement::with('movementData')
                ->forCompany($companyId)
                ->findOrFail($id);

            // ✅ Prepare data for duplication
            $movementData = $originalMovement->toArray();

            // ✅ Remove system fields and update key fields
            unset($movementData['id'], $movementData['created_at'], $movementData['updated_at'],
                  $movementData['deleted_at'], $movementData['movement_data']);

            // ✅ Generate new movement number
            $movementData['movement_number'] = $this->generateMovementNumber($companyId, $movementData['movement_type']);

            // ✅ Update date and time (key requirement)
            $movementData['movement_date'] = now()->toDateString();
            $movementData['movement_time'] = now()->toTimeString();
            $movementData['movement_datetime'] = now();

            // ✅ Reset status and confirmation
            $movementData['status'] = 'draft';
            $movementData['is_confirmed'] = false;
            $movementData['confirmed_at'] = null;
            $movementData['confirmed_by'] = null;

            // ✅ Set user fields
            $movementData['created_by'] = $userId;
            $movementData['updated_by'] = null;
            $movementData['deleted_by'] = null;

            // ✅ Create new movement
            $newMovement = InventoryMovement::create($movementData);

            // ✅ Duplicate movement data items
            $totalQuantity = 0;
            $totalValue = 0;
            $totalItems = 0;

            foreach ($originalMovement->movementData as $originalItem) {
                $itemData = $originalItem->toArray();

                // ✅ Remove system fields
                unset($itemData['id'], $itemData['created_at'], $itemData['updated_at']);

                // ✅ Update references
                $itemData['inventory_movement_id'] = $newMovement->id;
                $itemData['created_by'] = $userId;
                $itemData['updated_by'] = null;

                // ✅ Create new movement data item
                $newItem = InventoryMovementData::create($itemData);

                // ✅ Calculate totals
                $totalQuantity += $newItem->quantity;
                $totalValue += $newItem->total_cost;
                $totalItems++;
            }

            // ✅ Update movement totals
            $newMovement->update([
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'total_items' => $totalItems,
            ]);

            // ✅ Load relationships for response
            $newMovement->load([
                'warehouse', 'movementData.item', 'movementData.unit',
                'vendor', 'customer', 'creator'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $newMovement,
                'original_id' => $id,
                'message' => 'Inventory movement duplicated successfully',
                'message_ar' => 'تم تكرار حركة المخزون بنجاح'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate inventory movement: ' . $e->getMessage(),
                'message_ar' => 'فشل في تكرار حركة المخزون: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get trashed inventory movements (soft deleted).
     */
    public function trashed(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $query = InventoryMovement::onlyTrashed()
            ->with(['company', 'warehouse', 'deleter'])
            ->forCompany($companyId);

        // Apply search to trashed items
        if ($request->has('search')) {
            $search = strtolower($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(movement_number) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(movement_description) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(vendor_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(customer_name) LIKE ?', ["%{$search}%"]);
            });
        }

        $perPage = $request->get('per_page', 15);
        $movements = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movements,
            'message' => 'Trashed inventory movements retrieved successfully',
            'message_ar' => 'تم استرداد حركات المخزون المحذوفة بنجاح'
        ]);
    }

    /**
     * ✅ Restore a soft deleted inventory movement.
     */
    public function restore($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $movement = InventoryMovement::onlyTrashed()
            ->forCompany($companyId)
            ->findOrFail($id);

        $movement->restore();

        return response()->json([
            'success' => true,
            'data' => $movement->fresh(['warehouse', 'creator']),
            'message' => 'Inventory movement restored successfully',
            'message_ar' => 'تم استعادة حركة المخزون بنجاح'
        ]);
    }

    /**
     * ✅ Permanently delete an inventory movement.
     */
    public function forceDelete($id): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? request()->company_id;

        $movement = InventoryMovement::onlyTrashed()
            ->forCompany($companyId)
            ->findOrFail($id);

        $movement->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory movement permanently deleted',
            'message_ar' => 'تم حذف حركة المخزون نهائياً'
        ]);
    }

    /**
     * ✅ Get first inventory movement (First/Last sorting).
     */
    public function first(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $sortBy = $request->get('sort_by', 'movement_date');
        $sortDirection = 'asc'; // First = ascending

        $movement = InventoryMovement::with([
            'warehouse', 'movementData.item', 'movementData.unit',
            'vendor', 'customer', 'creator'
        ])
        ->forCompany($companyId)
        ->orderBy($sortBy, $sortDirection)
        ->first();

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'No inventory movements found',
                'message_ar' => 'لا توجد حركات مخزون'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => 'First inventory movement retrieved',
            'message_ar' => 'تم استرداد أول حركة مخزون'
        ]);
    }

    /**
     * ✅ Get last inventory movement (First/Last sorting).
     */
    public function last(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $sortBy = $request->get('sort_by', 'movement_date');
        $sortDirection = 'desc'; // Last = descending

        $movement = InventoryMovement::with([
            'warehouse', 'movementData.item', 'movementData.unit',
            'vendor', 'customer', 'creator'
        ])
        ->forCompany($companyId)
        ->orderBy($sortBy, $sortDirection)
        ->first();

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'No inventory movements found',
                'message_ar' => 'لا توجد حركات مخزون'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => 'Last inventory movement retrieved',
            'message_ar' => 'تم استرداد آخر حركة مخزون'
        ]);
    }

    /**
     * ✅ Get movement data with sorting (First/Last for movement data table).
     */
    public function getMovementData($id, Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $movement = InventoryMovement::forCompany($companyId)->findOrFail($id);

        $query = InventoryMovementData::with(['item', 'unit', 'warehouse'])
            ->where('inventory_movement_id', $id);

        // ✅ Apply sorting for movement data fields
        $sortBy = $request->get('sort_by', 'serial_number');
        $sortDirection = $request->get('sort_direction', 'asc');

        $sortableColumns = [
            'serial_number', 'item_number', 'item_name', 'unit_name',
            'warehouse_number', 'warehouse_name', 'inventory_count',
            'quantity', 'unit_price', 'total_cost', 'notes'
        ];

        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('serial_number', 'asc');
        }

        $movementData = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'movement' => $movement,
                'movement_data' => $movementData
            ],
            'message' => 'Movement data retrieved successfully',
            'message_ar' => 'تم استرداد بيانات الحركة بنجاح'
        ]);
    }

    /**
     * ✅ Get next sequential movement number for preview.
     */
    public function getNextMovementNumber(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;

        $request->validate([
            'warehouse_id' => 'required|integer',
            'movement_type' => 'required|string|in:outbound,inbound,transfer,manufacturing,inventory_count'
        ]);

        $nextNumber = $this->generateSequentialMovementNumber(
            $companyId,
            $request->warehouse_id,
            $request->movement_type
        );

        return response()->json([
            'success' => true,
            'data' => [
                'next_movement_number' => $nextNumber,
                'warehouse_id' => $request->warehouse_id,
                'movement_type' => $request->movement_type
            ],
            'message' => 'Next movement number generated',
            'message_ar' => 'تم توليد رقم الحركة التالي'
        ]);
    }
}
