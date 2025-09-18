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

class InvoiceLine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'financial_year_id',
        'company_id',
        'branch_id',
        'cost_center_id',
        'project_id',
        'invoice_id',
        'item_id',
        'description',
        'quantity',
        'unit_id',
        'unit_price',
        'discount',
        'tax_id',
        'total_foregin',
        'total_local',
        'total',
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
            'cost_center_id',
            'project_id',
            'invoice_id',
            'item_id',
            'description',
            'quantity',
            'unit_id',
            'unit_price',
            'discount',
            'tax_id',
            'total_foregin',
            'total_local',
            'total',
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
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('description', 'like', "%{$filters['search']}%")
                    ->orWhere('quantity', 'like', "%{$filters['search']}%")
                    ->orWhere('unit_price', 'like', "%{$filters['search']}%")
                    ->orWhere('discount', 'like', "%{$filters['search']}%")
                    ->orWhere('total_foregin', 'like', "%{$filters['search']}%")
                    ->orWhere('total_local', 'like', "%{$filters['search']}%")
                    ->orWhere('total', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['invoice_id']) {
            $builder->where('invoice_id', $filters['invoice_id']);
        }
    }

    public function financialYear()
    {
        return $this->belongsTo(FiscalYear::class, 'financial_year_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function costCenter()
    {
        // return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function project()
    {
        // return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item()
    {
        // return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        // return $this->belongsTo(Unit::class);
    }

    public function tax()
    {
        return $this->belongsTo(TaxRate::class);
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
