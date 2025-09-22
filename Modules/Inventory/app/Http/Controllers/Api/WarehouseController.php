<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Http\Requests\StoreWarehouseRequest;
use Modules\Inventory\Http\Requests\UpdateWarehouseRequest;
use Illuminate\Support\Facades\DB;

/**
 * @group Inventory Management / Warehouses
 *
 * APIs for managing warehouses, including creation, updates, search, and warehouse operations.
 */
class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = Warehouse::with([
            'company', 'branch', 'user', 'departmentWarehouse',
            'warehouseKeeper', 'salesAccount', 'purchaseAccount'  // ✅ Include all relationships
        ])->forCompany($companyId);

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

        // ✅ Enhanced Search Functionality - Case-insensitive with partial matches
        if ($request->has('search')) {
            $search = strtolower($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(warehouse_number) LIKE ?', ["%{$search}%"])     // ✅ Warehouse Number
                  ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"])               // ✅ Warehouse Name
                  ->orWhereRaw('LOWER(address) LIKE ?', ["%{$search}%"])            // ✅ Address
                  ->orWhereRaw('LOWER(warehouse_keeper_employee_name) LIKE ?', ["%{$search}%"]) // ✅ Warehouse Keeper Name
                  ->orWhereRaw('LOWER(warehouse_keeper_employee_number) LIKE ?', ["%{$search}%"]) // ✅ Warehouse Keeper Number
                  ->orWhereRaw('LOWER(phone_number) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        // ✅ Specific Field Searches - Case-insensitive with partial matches
        if ($request->has('warehouse_number')) {
            $warehouseNumber = strtolower($request->get('warehouse_number'));
            $query->whereRaw('LOWER(warehouse_number) LIKE ?', ["%{$warehouseNumber}%"]);
        }

        if ($request->has('warehouse_name')) {
            $warehouseName = strtolower($request->get('warehouse_name'));
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$warehouseName}%"]);
        }

        if ($request->has('warehouse_keeper')) {
            $keeper = strtolower($request->get('warehouse_keeper'));
            $query->where(function ($q) use ($keeper) {
                $q->whereRaw('LOWER(warehouse_keeper_employee_name) LIKE ?', ["%{$keeper}%"])
                  ->orWhereRaw('LOWER(warehouse_keeper_employee_number) LIKE ?', ["%{$keeper}%"]);
            });
        }

        if ($request->has('address_search')) {
            $address = strtolower($request->get('address_search'));
            $query->whereRaw('LOWER(address) LIKE ?', ["%{$address}%"]);
        }

        // ✅ Enhanced Sorting - Support for all warehouse table columns
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        // ✅ Validate sortable columns
        $sortableColumns = [
            'id', 'warehouse_number', 'name', 'address', 'description',
            'warehouse_keeper_employee_name', 'warehouse_keeper_employee_number',
            'phone_number', 'fax_number', 'mobile', 'status',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc'); // Default fallback
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $warehouses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $warehouses,
            'message' => 'Warehouses retrieved successfully',
            'message_ar' => 'تم استرداد المخازن بنجاح'
        ]);
    }

    /**
     * ✅ Filter warehouses by specific field value (Selection-Driven Display).
     */
    public function filterByField(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;

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
            'warehouse_number', 'name', 'address', 'description',
            'warehouse_keeper_employee_name', 'warehouse_keeper_employee_number',
            'phone_number', 'fax_number', 'mobile', 'status'
        ];

        if (!in_array($field, $filterableFields)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid field for filtering',
                'message_ar' => 'حقل غير صالح للتصفية'
            ], 422);
        }

        $query = Warehouse::with([
            'company', 'branch', 'user', 'departmentWarehouse',
            'warehouseKeeper', 'salesAccount', 'purchaseAccount'
        ])->forCompany($companyId);

        // ✅ Apply field-specific filter (case-insensitive)
        $query->whereRaw("LOWER({$field}) LIKE ?", ['%' . strtolower($value) . '%']);

        // ✅ Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // ✅ Paginate results
        $perPage = $request->get('per_page', 15);
        $warehouses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $warehouses,
            'filter' => [
                'field' => $field,
                'value' => $value
            ],
            'message' => "Warehouses filtered by {$field}",
            'message_ar' => "تم تصفية المخازن حسب {$field}"
        ]);
    }

    /**
     * ✅ Store a newly created warehouse with all required fields.
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        // ✅ Get validated data
        $data = $request->validated();

        // ✅ Set system fields
        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;
        $data['created_by'] = $userId;

        // ✅ Handle JSON warehouse_data if provided as string
        if (isset($data['warehouse_data']) && is_string($data['warehouse_data'])) {
            $data['warehouse_data'] = json_decode($data['warehouse_data'], true);
        }

        // ✅ Auto-generate warehouse number if not provided
        if (empty($data['warehouse_number'])) {
            $data['warehouse_number'] = $this->generateWarehouseNumber($companyId);
        }

        // ✅ Create warehouse with all fields
        $warehouse = Warehouse::create($data);

        // ✅ Load all relationships for response
        $warehouse->load([
            'company', 'branch', 'user', 'departmentWarehouse',
            'warehouseKeeper', 'salesAccount', 'purchaseAccount'
        ]);

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'Warehouse created successfully',
            'message_ar' => 'تم إنشاء المخزن بنجاح'
        ], 201);
    }

    /**
     * ✅ Display the specified warehouse with comprehensive data (Review/Preview).
     */
    public function show($id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

        // ✅ Load warehouse with all relationships for comprehensive preview
        $warehouse = Warehouse::with([
            'company', 'branch', 'user', 'departmentWarehouse',
            'warehouseKeeper', 'salesAccount', 'purchaseAccount',
            'stock.inventoryItem', 'stockMovements', 'creator', 'updater'
        ])->forCompany($companyId)->findOrFail($id);

        // ✅ Add computed attributes for display
        $warehouseData = $warehouse->toArray();
        $warehouseData['display_name'] = $warehouse->display_name;
        $warehouseData['warehouse_keeper_name'] = $warehouse->warehouse_keeper_name;
        $warehouseData['warehouse_keeper_number'] = $warehouse->warehouse_keeper_number;
        $warehouseData['sales_account_name'] = $warehouse->sales_account_name;
        $warehouseData['purchase_account_name'] = $warehouse->purchase_account_name;

        // ✅ Add summary statistics
        $warehouseData['statistics'] = [
            'total_stock_items' => $warehouse->stock->count(),
            'total_stock_movements' => $warehouse->stockMovements->count(),
            'total_stock_value' => $warehouse->stock->sum('total_value'),
            'last_movement_date' => $warehouse->stockMovements->max('movement_date'),
        ];

        return response()->json([
            'success' => true,
            'data' => $warehouseData,
            'message' => 'Warehouse retrieved successfully',
            'message_ar' => 'تم استرداد بيانات المخزن بنجاح'
        ]);
    }

    /**
     * ✅ Update the specified warehouse with full validation and field updates.
     */
    public function update(UpdateWarehouseRequest $request, $id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        $warehouse = Warehouse::forCompany($companyId)->findOrFail($id);

        // ✅ Validate and prepare all editable fields
        $data = $request->validated();
        $data['updated_by'] = $userId;

        // ✅ Handle JSON warehouse_data if provided as string
        if (isset($data['warehouse_data']) && is_string($data['warehouse_data'])) {
            $data['warehouse_data'] = json_decode($data['warehouse_data'], true);
        }

        // ✅ Update all fields
        $warehouse->update($data);

        // ✅ Load updated warehouse with all relationships
        $warehouse->load([
            'company', 'branch', 'user', 'departmentWarehouse',
            'warehouseKeeper', 'salesAccount', 'purchaseAccount'
        ]);

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'Warehouse updated successfully',
            'message_ar' => 'تم تحديث المخزن بنجاح'
        ]);
    }

    /**
     * ✅ Remove the specified warehouse with soft delete.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;
        $userId = Auth::id() ?? request()->user_id;

        $warehouse = Warehouse::forCompany($companyId)->findOrFail($id);

        // Check if warehouse has stock
        if ($warehouse->stock()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete warehouse with existing stock',
                'message_ar' => 'لا يمكن حذف المخزن الذي يحتوي على مخزون'
            ], 422);
        }

        // ✅ Set deleted_by before soft delete
        $warehouse->update(['deleted_by' => $userId]);
        $warehouse->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully',
            'message_ar' => 'تم حذف المخزن بنجاح'
        ]);
    }

    /**
     * Get the first warehouse.
     */
    public function first(): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

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
        $companyId = Auth::user()->company_id ?? request()->company_id;

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

    /**
     * ✅ Get trashed warehouses (soft deleted).
     */
    public function trashed(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;

        $query = Warehouse::onlyTrashed()
            ->with(['company', 'branch', 'warehouseKeeper', 'deleter'])
            ->forCompany($companyId);

        // Apply search to trashed items
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('warehouse_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $warehouses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $warehouses,
            'message' => 'Trashed warehouses retrieved successfully',
            'message_ar' => 'تم استرداد المخازن المحذوفة بنجاح'
        ]);
    }

    /**
     * ✅ Restore a soft deleted warehouse.
     */
    public function restore($id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

        $warehouse = Warehouse::onlyTrashed()
            ->forCompany($companyId)
            ->findOrFail($id);

        $warehouse->restore();

        return response()->json([
            'success' => true,
            'data' => $warehouse->fresh(['company', 'branch', 'warehouseKeeper']),
            'message' => 'Warehouse restored successfully',
            'message_ar' => 'تم استعادة المخزن بنجاح'
        ]);
    }

    /**
     * ✅ Permanently delete a warehouse.
     */
    public function forceDelete($id): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? request()->company_id;

        $warehouse = Warehouse::onlyTrashed()
            ->forCompany($companyId)
            ->findOrFail($id);

        $warehouse->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse permanently deleted',
            'message_ar' => 'تم حذف المخزن نهائياً'
        ]);
    }

    /**
     * ✅ Get dropdown data for warehouse form.
     */
    public function getFormData(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;

        try {
            $data = [
                // ✅ Employees dropdown (for Warehouse Keeper)
                'employees' => $this->getEmployeesDropdown($companyId),

                // ✅ Accounts dropdown (for Sales and Purchase accounts)
                'accounts' => $this->getAccountsDropdown($companyId),

                // ✅ Branches dropdown
                'branches' => $this->getBranchesDropdown($companyId),

                // ✅ Department Warehouses dropdown
                'department_warehouses' => $this->getDepartmentWarehousesDropdown($companyId),

                // ✅ Inventory valuation methods
                'inventory_valuation_methods' => Warehouse::INVENTORY_VALUATION_METHODS,

                // ✅ Status options
                'status_options' => Warehouse::STATUS_OPTIONS,
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
                    'employees' => [],
                    'accounts' => [],
                    'branches' => [],
                    'department_warehouses' => [],
                    'inventory_valuation_methods' => Warehouse::INVENTORY_VALUATION_METHODS,
                    'status_options' => Warehouse::STATUS_OPTIONS,
                ],
                'message' => 'Form data retrieved (some dropdowns may be empty)',
                'message_ar' => 'تم استرداد بيانات النموذج (بعض القوائم قد تكون فارغة)'
            ]);
        }
    }

    /**
     * ✅ Get employees for dropdown (can be empty if employees table doesn't exist).
     */
    private function getEmployeesDropdown($companyId): array
    {
        try {
            if (!class_exists('\App\Models\Employee')) {
                return [];
            }

            return DB::table('employees')
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'name', 'employee_number')
                ->orderBy('name')
                ->get()
                ->map(function ($employee) {
                    return [
                        'value' => $employee->id,
                        'label' => $employee->name . ' (' . $employee->employee_number . ')',
                        'employee_number' => $employee->employee_number,
                        'name' => $employee->name,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ Get accounts for dropdown (can be empty if accounts table doesn't exist).
     */
    private function getAccountsDropdown($companyId): array
    {
        try {
            if (!class_exists('\App\Models\Account')) {
                return [];
            }

            return DB::table('accounts')
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'name', 'account_number')
                ->orderBy('name')
                ->get()
                ->map(function ($account) {
                    return [
                        'value' => $account->id,
                        'label' => $account->name . ' (' . $account->account_number . ')',
                        'account_number' => $account->account_number,
                        'name' => $account->name,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ Get branches for dropdown.
     */
    private function getBranchesDropdown($companyId): array
    {
        try {
            return DB::table('branches')
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get()
                ->map(function ($branch) {
                    return [
                        'value' => $branch->id,
                        'label' => $branch->name . ' (' . $branch->code . ')',
                        'code' => $branch->code,
                        'name' => $branch->name,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ Get department warehouses for dropdown.
     */
    private function getDepartmentWarehousesDropdown($companyId): array
    {
        try {
            return DB::table('department_warehouses')
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get()
                ->map(function ($dept) {
                    return [
                        'value' => $dept->id,
                        'label' => $dept->name . ' (' . $dept->code . ')',
                        'code' => $dept->code,
                        'name' => $dept->name,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ Generate unique warehouse number.
     */
    private function generateWarehouseNumber($companyId): string
    {
        $prefix = 'WH-';
        $year = date('Y');
        $month = date('m');

        // Get the last warehouse number for this company
        $lastWarehouse = Warehouse::where('company_id', $companyId)
            ->where('warehouse_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('warehouse_number', 'desc')
            ->first();

        if ($lastWarehouse) {
            // Extract the sequence number and increment
            $lastNumber = substr($lastWarehouse->warehouse_number, -4);
            $nextNumber = str_pad((int)$lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$year}{$month}-{$nextNumber}";
    }
}
