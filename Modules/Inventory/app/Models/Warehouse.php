<?php

namespace Modules\Inventory\Models;

use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Users\Models\User;
use Modules\HumanResources\Models\Employee;
use Modules\FinancialAccounts\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        // Warehouse Information
        'warehouse_number',
        'name',
        'address',
        'description',
        'warehouse_data',
        // Warehouse Keeper Information
        'warehouse_keeper_id',
        'warehouse_keeper_employee_number',
        'warehouse_keeper_employee_name',
        // Contact Information
        'phone_number',
        'fax_number',
        'mobile',
        // Account Information
        'sales_account_id',
        'purchase_account_id',
        // Legacy fields
        'location',
        'department_warehouse_id',
        // System fields
        'inventory_valuation_method',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'string',
        'warehouse_data' => 'array',
    ];

    const INVENTORY_VALUATION_METHODS = [
        'natural_division' => 'طبيعي - كما هو محدد لخطة التقسيم',
        'no_value' => 'بدون - ليس للبضاعة أي قيمة',
        'first_purchase_price' => 'حسب سعر الشراء الأول الموجود في كرت الصنف',
        'second_purchase_price' => 'حسب سعر الشراء الثاني الموجود في كرت الصنف',
        'third_purchase_price' => 'حسب سعر الشراء الثالث الموجود في كرت الصنف',
    ];

    const STATUS_OPTIONS = [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ];

    /**
     * Get the user who created the warehouse.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the warehouse.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the warehouse.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the warehouse keeper employee.
     */
    public function warehouseKeeper(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'warehouse_keeper_id');
    }

    /**
     * Get the sales account.
     */
    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_account_id');
    }

    /**
     * Get the purchase account.
     */
    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'purchase_account_id');
    }

    /**
     * Get the department that owns the warehouse.
     */
    public function departmentWarehouse(): BelongsTo
    {
        return $this->belongsTo(DepartmentWarehouse::class, 'department_warehouse_id');
    }

    /**
     * Get the user who created the warehouse.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the warehouse.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the warehouse.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the stock records for this warehouse.
     */
    public function stock(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Get the stock movements for this warehouse.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the warehouse keeper employee name from relationship or stored field.
     */
    public function getWarehouseKeeperNameAttribute()
    {
        if ($this->warehouseKeeper) {
            return $this->warehouseKeeper->name;
        }
        return $this->warehouse_keeper_employee_name;
    }

    /**
     * Get the warehouse keeper employee number from relationship or stored field.
     */
    public function getWarehouseKeeperNumberAttribute()
    {
        if ($this->warehouseKeeper) {
            return $this->warehouseKeeper->employee_number;
        }
        return $this->warehouse_keeper_employee_number;
    }

    /**
     * Get the sales account name.
     */
    public function getSalesAccountNameAttribute()
    {
        return $this->salesAccount ? $this->salesAccount->name : null;
    }

    /**
     * Get the purchase account name.
     */
    public function getPurchaseAccountNameAttribute()
    {
        return $this->purchaseAccount ? $this->purchaseAccount->name : null;
    }

    /**
     * Get the display name for the warehouse.
     */
    public function getDisplayNameAttribute()
    {
        return $this->warehouse_number . ' - ' . $this->name;
    }

    /**
     * Scope to get active warehouses only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get warehouses for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
