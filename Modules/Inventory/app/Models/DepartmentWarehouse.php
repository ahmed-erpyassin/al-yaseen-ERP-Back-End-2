<?php

namespace Modules\Inventory\Models;

use Modules\Companies\Models\Company;
use Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentWarehouse extends Model
{
    protected $fillable = [
        'company_id',
        'department_number',
        'department_name_ar',
        'department_name_en',
        'description',
        'manager_name',
        'manager_phone',
        'manager_email',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the company that owns the department.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the department.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the department.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the warehouses for this department.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'department_warehouse_id');
    }

    /**
     * Scope to get active departments only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get departments for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
