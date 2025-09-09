<?php

namespace Modules\FinancialAccounts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

class JournalsEntryLine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'fiscal_year_id',
        'user_id',
        'company_id',
        'branch_id',
        'journal_entry_id',
        'currency_id',
        'account_id',
        'cost_center_id',
        'project_id',
        'debit',
        'credit',
        'exchange_rate',
        'description',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'fiscal_year_id',
            'user_id',
            'company_id',
            'branch_id',
            'journal_entry_id',
            'currency_id',
            'account_id',
            'cost_center_id',
            'project_id',
            'debit',
            'credit',
            'exchange_rate',
            'description',
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
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $builder;
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalsEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
}
