<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Company;
use Modules\FinancialAccounts\Models\Budget;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;


class Department extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'fiscal_year_id',
        'name',
        'number',
        'manager_id',
        'address',
        'work_phone',
        'home_phone',
        'fax',
        'statement',
        'statement_en',
        'parent_id',
        'funder_id',
        'project_status',
        'status',
        'proposed_start_date',
        'proposed_end_date',
        'actual_start_date',
        'actual_end_date',
        'budget_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'proposed_start_date' => 'date',
        'proposed_end_date'   => 'date',
        'actual_start_date'   => 'date',
        'actual_end_date'     => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function funder()
    {
        return $this->belongsTo(\App\Models\Funder::class, 'funder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Reverse relationships - models that belong to this department
    public function employees()
    {
        return $this->hasMany(\Modules\HumanResources\Models\Employee::class);
    }

    public function jobTitles()
    {
        return $this->hasMany(\Modules\HumanResources\Models\JobTitle::class);
    }

    public function childDepartments()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Generate next department number
     */
    public static function generateDepartmentNumber($companyId = null)
    {
        $companyId = $companyId ?: request()->company_id;

        $lastDepartment = static::where('company_id', $companyId)
            ->orderBy('number', 'desc')
            ->first();

        if (!$lastDepartment) {
            return 1;
        }

        return $lastDepartment->number + 1;
    }
}
