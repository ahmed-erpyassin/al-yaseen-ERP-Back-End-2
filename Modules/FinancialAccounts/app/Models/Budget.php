<?php

namespace Modules\FinancialAccounts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Company;
use Modules\Users\Models\User;

class Budget extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'fiscal_year_id',
        'account_id',
        'cost_center_id',
        'project_id',
        'amount',
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
            'fiscal_year_id',
            'account_id',
            'cost_center_id',
            'project_id',
            'amount',
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
                $query->where('amount', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['fiscal_year_id']) && $filters['fiscal_year_id']) {
            $builder->where('fiscal_year_id', $filters['fiscal_year_id']);
        }

        if (isset($filters['account_id']) && $filters['account_id']) {
            $builder->where('account_id', $filters['account_id']);
        }

        if (isset($filters['cost_center_id']) && $filters['cost_center_id']) {
            $builder->where('cost_center_id', $filters['cost_center_id']);
        }

        if (isset($filters['project_id']) && $filters['project_id']) {
            $builder->where('project_id', $filters['project_id']);
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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function project()
    {
        // return $this->belongsTo(Project::class, 'project_id');
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
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
