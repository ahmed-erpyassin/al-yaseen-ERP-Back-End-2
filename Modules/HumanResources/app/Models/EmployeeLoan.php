<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;

class EmployeeLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'fiscal_year_id',
        'employee_id',
        'loan_number',
        'loan_type',
        'loan_amount',
        'interest_rate',
        'loan_date',
        'repayment_period',
        'monthly_deduction',
        'total_paid',
        'remaining_balance',
        'status',
        'purpose',
        'guarantor_name',
        'guarantor_phone',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'repayment_period' => 'integer',
    ];

    // Relationships
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
