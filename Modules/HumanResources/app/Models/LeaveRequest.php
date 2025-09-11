<?php

namespace Modules\HumanResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

// use Modules\HumanResources\Database\Factories\LeaveRequestFactory;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'previous_balance',
        'deducted',
        'remaining_balance',
        'notes',
        'status',
        'approved_at',
        'approved_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'approved_at'  => 'datetime',
    ];

    public function employee()
    {

        return $this->belongsTo(Employee::class, 'employee_id');
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
