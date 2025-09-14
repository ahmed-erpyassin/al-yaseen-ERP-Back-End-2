<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Company;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;

class InvoicePayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'financial_year_id',
        'company_id',
        'branch_id',
        'invoice_id',
        'currency_id',
        'payment_date',
        'payment_method', // cash, bank_transfer, credit_card, check, other
        'amount',
        'exchange_rate',
        'reference',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'financial_year_id',
            'company_id',
            'branch_id',
            'invoice_id',
            'currency_id',
            'payment_date',
            'payment_method',
            'amount',
            'exchange_rate',
            'reference',
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
            'invoice_id' => null,
            'payment_method' => null,
            'date_from' => null,
            'date_to' => null,
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('reference', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['invoice_id']) {
            $builder->where('invoice_id', $filters['invoice_id']);
        }

        if ($filters['payment_method']) {
            $builder->where('payment_method', $filters['payment_method']);
        }

        if ($filters['date_from']) {
            $builder->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $builder->whereDate('payment_date', '<=', $filters['date_to']);
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
        return $this->belongsTo(Branch::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function financialYear()
    {
        return $this->belongsTo(FiscalYear::class, 'financial_year_id');
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
