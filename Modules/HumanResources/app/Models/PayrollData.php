<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;

class PayrollData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payroll_data';

    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'fiscal_year_id',
        'payroll_record_id',
        'employee_id',
        'employee_number',
        'employee_name',
        'national_id',
        'marital_status',
        'job_title',
        'duration',
        'basic_salary',
        'income_tax',
        'salary_for_payment',
        'paid_in_cash',
        'allowances',
        'deductions',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'status',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'salary_for_payment' => 'decimal:2',
        'paid_in_cash' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
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

    // Payroll relationships
    public function payrollRecord()
    {
        return $this->belongsTo(PayrollRecord::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
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

    public function scopeForPayrollRecord($query, $payrollRecordId)
    {
        return $query->where('payroll_record_id', $payrollRecordId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function calculateSalaryForPayment()
    {
        $totalSalary = $this->basic_salary + $this->allowances + $this->overtime_amount;
        $totalDeductions = $this->income_tax + $this->deductions;
        
        return $totalSalary - $totalDeductions;
    }

    public function calculateOvertimeAmount()
    {
        return $this->overtime_hours * $this->overtime_rate;
    }

    public function populateFromEmployee(Employee $employee)
    {
        $this->employee_number = $employee->employee_number;
        $this->employee_name = $employee->full_name;
        $this->national_id = $employee->national_id;
        $this->marital_status = $employee->marital_status;
        $this->job_title = $employee->jobTitle ? $employee->jobTitle->name : null;
        $this->basic_salary = $employee->salary ?? 0;
        
        // Calculate duration (years of service)
        if ($employee->hire_date) {
            $years = $employee->hire_date->diffInYears(now());
            $months = $employee->hire_date->diffInMonths(now()) % 12;
            $this->duration = "{$years} years, {$months} months";
        }

        return $this;
    }

    public function recalculateAmounts()
    {
        // Calculate overtime amount
        $this->overtime_amount = $this->calculateOvertimeAmount();
        
        // Calculate salary for payment
        $this->salary_for_payment = $this->calculateSalaryForPayment();
        
        return $this;
    }

    // Boot method for automatic calculations
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($payrollData) {
            // Auto-calculate overtime amount
            $payrollData->overtime_amount = $payrollData->calculateOvertimeAmount();
            
            // Auto-calculate salary for payment
            $payrollData->salary_for_payment = $payrollData->calculateSalaryForPayment();
        });

        static::saved(function ($payrollData) {
            // Update payroll record totals when payroll data is saved
            if ($payrollData->payrollRecord) {
                $payrollData->payrollRecord->calculateTotals();
            }
        });

        static::deleted(function ($payrollData) {
            // Update payroll record totals when payroll data is deleted
            if ($payrollData->payrollRecord) {
                $payrollData->payrollRecord->calculateTotals();
            }
        });
    }
}
