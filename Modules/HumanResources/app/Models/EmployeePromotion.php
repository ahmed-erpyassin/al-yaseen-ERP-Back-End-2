<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;

class EmployeePromotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'fiscal_year_id',
        'employee_id',
        'promotion_date',
        'old_job_title_id',
        'new_job_title_id',
        'old_salary',
        'new_salary',
        'promotion_reason',
        'effective_date',
        'status',
        'approved_by',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'promotion_date' => 'date',
        'effective_date' => 'date',
        'old_salary' => 'decimal:2',
        'new_salary' => 'decimal:2',
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

    public function oldJobTitle()
    {
        return $this->belongsTo(JobTitle::class, 'old_job_title_id');
    }

    public function newJobTitle()
    {
        return $this->belongsTo(JobTitle::class, 'new_job_title_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
