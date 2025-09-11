<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\Company;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Users\Models\User;

class InvoiceTax extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'financial_year_id',
        'company_id',
        'branch_id',
        'invoice_id',
        'tax_id',
        'tax_amount',
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
            'tax_id',
            'tax_amount',
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
            'company_id' => null,
            'branch_id' => null,
            'invoice_id' => null,
            'tax_id' => null,
        ], $filters);

        if ($filters['company_id']) {
            $builder->where('company_id', $filters['company_id']);
        }

        if ($filters['branch_id']) {
            $builder->where('branch_id', $filters['branch_id']);
        }

        if ($filters['invoice_id']) {
            $builder->where('invoice_id', $filters['invoice_id']);
        }

        if ($filters['tax_id']) {
            $builder->where('tax_id', $filters['tax_id']);
        }

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('tax_amount', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $builder;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function tax()
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
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
