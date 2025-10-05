<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\Account;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Carbon\Carbon;

class PayrollRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payroll_records';

    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'fiscal_year_id',
        'payroll_number',
        'date',
        'second_date',
        'currency_id',
        'currency_rate',
        'account_number',
        'account_name',
        'account_id',
        'payment_account',
        'salaries_wages_period',
        'total_salaries',
        'total_income_tax_deductions',
        'total_payable_amount',
        'total_salaries_paid_cash',
        'status',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'second_date' => 'date',
        'currency_rate' => 'decimal:4',
        'total_salaries' => 'decimal:2',
        'total_income_tax_deductions' => 'decimal:2',
        'total_payable_amount' => 'decimal:2',
        'total_salaries_paid_cash' => 'decimal:2',
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

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Payroll relationships
    public function payrollData()
    {
        return $this->hasMany(PayrollData::class);
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Helper methods
    public function generatePayrollNumber()
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        
        $lastRecord = static::where('company_id', $this->company_id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('payroll_number', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->payroll_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PAY{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function generateSalariesWagesPeriod()
    {
        if ($this->date) {
            $monthName = $this->date->format('F');
            $year = $this->date->format('Y');
            return "Salaries and wages for {$monthName} / {$year}";
        }
        return null;
    }

    public function calculateTotals()
    {
        $payrollData = $this->payrollData;
        
        $totalSalaries = $payrollData->sum('basic_salary');
        $totalIncomeTax = $payrollData->sum('income_tax');
        $totalPayableAmount = $payrollData->sum('salary_for_payment');
        $totalPaidCash = $payrollData->sum('paid_in_cash');

        $this->update([
            'total_salaries' => $totalSalaries,
            'total_income_tax_deductions' => $totalIncomeTax,
            'total_payable_amount' => $totalPayableAmount,
            'total_salaries_paid_cash' => $totalPaidCash,
        ]);

        return $this;
    }

    // Boot method for automatic calculations
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payrollRecord) {
            if (empty($payrollRecord->payroll_number)) {
                $payrollRecord->payroll_number = $payrollRecord->generatePayrollNumber();
            }
            
            if (empty($payrollRecord->salaries_wages_period) && $payrollRecord->date) {
                $payrollRecord->salaries_wages_period = $payrollRecord->generateSalariesWagesPeriod();
            }
        });

        static::updating(function ($payrollRecord) {
            if ($payrollRecord->isDirty('date') && $payrollRecord->date) {
                $payrollRecord->salaries_wages_period = $payrollRecord->generateSalariesWagesPeriod();
            }
        });
    }
}
