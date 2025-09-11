<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;

// use Modules\HumanResources\Database\Factories\EmployeeFactory;

class Employee extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'employees';

    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'fiscal_year_id',
        'department_id',
        'job_title_id',
        'manager_id',

        'employee_number',
        'code',

        'nickname',
        'first_name',
        'second_name',
        'third_name',
        'phone1',
        'phone2',
        'email',

        'birth_date',
        'address',
        'national_id',
        'id_number',
        'gender',

        'wives_count',
        'children_count',
        'dependents_count',

        'car_number',
        'is_driver',
        'is_sales',

        'hire_date',
        'employee_code',
        'employee_identifier',
        'job_address',

        'salary',
        'billing_rate',
        'monthly_discount',

        'currency_id',
        'notes',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'birth_date'        => 'date',
        'hire_date'         => 'date',
        'is_driver'         => 'boolean',
        'is_sales'          => 'boolean',
        'billing_rate'      => 'decimal:2',
        'monthly_discount'  => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // public function jobTitle()
    // {
    //     return $this->belongsTo(jobTitle::class);
    // }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
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
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
