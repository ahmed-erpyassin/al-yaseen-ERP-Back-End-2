<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Company;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\JournalsFinancial;
use Modules\Users\Models\User;

class Journal extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'currency_id',
        'employee_id',
        'name',
        'type',
        'code',
        'max_documents',
        'current_number',
        'status',
        'notes',
        'financial_journal_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'company_id',
            'branch_id',
            'currency_id',
            'employee_id',
            'name',
            'type',
            'code',
            'max_documents',
            'current_number',
            'status',
            'notes',
            'financial_journal_id',
            'created_by',
            'updated_by',
            'deleted_by',
            'created_at',
            'updated_at',
            'deleted_at'
        ]);
    }

    public function scopeFilters(Builder $builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'type' => '',
            'status' => '',
            'currency_id' => '',
            'employee_id' => '',
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('notes', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['type']) {
            $builder->where('type', $filters['type']);
        }

        if ($filters['status']) {
            $builder->where('status', $filters['status']);
        }

        if ($filters['currency_id']) {
            $builder->where('currency_id', $filters['currency_id']);
        }

        if ($filters['employee_id']) {
            $builder->where('employee_id', $filters['employee_id']);
        }

        return $builder;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function employee()
    {
        // return $this->belongsTo(\Modules\HR\Models\Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function financialJournal()
    {
        return $this->belongsTo(JournalsFinancial::class, 'financial_journal_id');
    }

    // المنشئ
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // المحدث
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // المحذف
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeStore(Builder $builder, array $data = [])
    {
        return $builder->create($data);
    }

    public function scopeUpdateModel(Builder $builder, $data, $id)
    {
        return $builder->where('id', $id)->update($data);
    }
}
