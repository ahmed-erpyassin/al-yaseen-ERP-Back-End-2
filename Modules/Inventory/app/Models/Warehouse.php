<?php

namespace Modules\Inventory\Models;

use App\Models\Company;
use App\Models\User;
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
        'name',
        'code',
        'location',
        'warehouse_keeper_employee_number',
        'warehouse_keeper_name',
        'mobile',
        'fax_number',
        'phone_number',
        'department_warehouse_id',
        'purchase_account',
        'sale_account',
        'inventory_valuation_method',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'string',
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
