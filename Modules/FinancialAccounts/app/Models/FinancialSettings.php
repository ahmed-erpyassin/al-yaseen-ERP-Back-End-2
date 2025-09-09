<?php

namespace Modules\FinancialAccounts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Users\Models\User;

class FinancialSettings extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'default_currency_id',
        'vat_account_id',
        'retained_earnings_account_id',
        'rounding_account_id',
        'default_sales_account_id',
        'default_purchase_account_id',
        'fiscal_year_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'company_id',
            'branch_id',
            'default_currency_id',
            'vat_account_id',
            'retained_earnings_account_id',
            'rounding_account_id',
            'default_sales_account_id',
            'default_purchase_account_id',
            'fiscal_year_id',
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
                $query->where('id', 'like', '%' . $filters['search'] . '%');
            });
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
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    public function vatAccount()
    {
        return $this->belongsTo(Account::class, 'vat_account_id');
    }

    public function retainedEarningsAccount()
    {
        return $this->belongsTo(Account::class, 'retained_earnings_account_id');
    }

    public function roundingAccount()
    {
        return $this->belongsTo(Account::class, 'rounding_account_id');
    }

    public function defaultSalesAccount()
    {
        return $this->belongsTo(Account::class, 'default_sales_account_id');
    }

    public function defaultPurchaseAccount()
    {
        return $this->belongsTo(Account::class, 'default_purchase_account_id');
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
