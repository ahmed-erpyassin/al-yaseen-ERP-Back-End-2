<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;

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
        'category',
        'manager_id',

        'employee_number',
        'code',

        'nickname',
        'first_name',
        'last_name',
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
        'students_count',

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
        'balance',

        'currency_id',
        'currency_rate',
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
        'balance'           => 'decimal:2',
        'currency_rate'     => 'decimal:4',
        'salary'            => 'decimal:2',
        'wives_count'       => 'integer',
        'children_count'    => 'integer',
        'students_count'    => 'integer',
    ];

    // Core relationships
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    // Audit relationships
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
    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeDrivers($query)
    {
        return $query->where('is_driver', true);
    }

    public function scopeSalesReps($query)
    {
        return $query->where('is_sales', true);
    }

    // Payroll relationships
    public function payrollRecords()
    {
        return $this->hasMany(\Modules\HumanResources\Models\PayrollRecord::class);
    }

    public function payrollData()
    {
        return $this->hasMany(\Modules\HumanResources\Models\PayrollData::class);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        $names = array_filter([
            $this->first_name,
            $this->second_name,
            $this->third_name,
            $this->last_name
        ]);

        return implode(' ', $names);
    }

    public function getEmployeeTypeAttribute()
    {
        if ($this->is_driver && $this->is_sales) {
            return 'driver_sales';
        } elseif ($this->is_driver) {
            return 'driver';
        } elseif ($this->is_sales) {
            return 'sales';
        }

        return 'employee';
    }

    public function getMaritalStatusAttribute()
    {
        // Calculate marital status based on wives_count
        if ($this->wives_count > 0) {
            return 'married';
        }
        return 'single';
    }

    /**
     * Generate next employee number
     */
    public static function generateEmployeeNumber($companyId = null)
    {
        $companyId = $companyId ?: (auth()->user()->company->id ?? 1);

        $lastEmployee = static::where('company_id', $companyId)
            ->orderBy('employee_number', 'desc')
            ->first();

        if (!$lastEmployee) {
            return 'EMP-0001';
        }

        // Extract number from employee_number (e.g., EMP-0001 -> 1)
        $lastNumber = (int) substr($lastEmployee->employee_number, 4);
        $nextNumber = $lastNumber + 1;

        return 'EMP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
