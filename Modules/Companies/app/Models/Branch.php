<?php

namespace Modules\Companies\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

// use Modules\Companies\Database\Factories\BranchFactory;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'currency_id',
        'manager_id',
        'financial_year_id',
        'country_id',
        'region_id',
        'city_id',
        'code',
        'name',
        'address',
        'landline',
        'mobile',
        'email',
        'logo',
        'tax_number',
        'timezone',
        'status',
        'created_by',
        'updated_by',
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'company_id',
            'currency_id',
            'manager_id',
            'financial_year_id',
            'country_id',
            'region_id',
            'city_id',
            'code',
            'name',
            'address',
            'landline',
            'mobile',
            'email',
            'logo',
            'tax_number',
            'timezone',
            'status',
            'created_by',
            'updated_by',
        ]);
    }

    public function scopeFilters(Builder $builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'status' => null,
            'user_id' => null,
            'company_id' => null,
            'currency_id' => null,
            'financial_year_id' => null,
            'industry_id' => null,
            'business_type_id' => null,
            'country_id' => null,
            'region_id' => null,
            'city_id' => null,
            'created_by' => null,
            'updated_by' => null,
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['user_id']) {
            $builder->where('user_id', $filters['user_id']);
        }

        if ($filters['company_id']) {
            $builder->where('company_id', $filters['company_id']);
        }

        if ($filters['currency_id']) {
            $builder->where('currency_id', $filters['currency_id']);
        }

        if ($filters['financial_year_id']) {
            $builder->where('financial_year_id', $filters['financial_year_id']);
        }

        if ($filters['industry_id']) {
            $builder->where('industry_id', $filters['industry_id']);
        }

        if ($filters['business_type_id']) {
            $builder->where('business_type_id', $filters['business_type_id']);
        }

        if ($filters['country_id']) {
            $builder->where('country_id', $filters['country_id']);
        }

        if ($filters['region_id']) {
            $builder->where('region_id', $filters['region_id']);
        }

        if ($filters['city_id']) {
            $builder->where('city_id', $filters['city_id']);
        }

        if ($filters['status']) {
            $builder->where('status', $filters['status']);
        }

        if ($filters['created_by']) {
            $builder->where('created_by', $filters['created_by']);
        }

        if ($filters['updated_by']) {
            $builder->where('updated_by', $filters['updated_by']);
        }

        return $builder;
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // public function currency()
    // {
    //     return $this->belongsTo(Currency::class);
    // }

    // public function manager()
    // {
    //     return $this->belongsTo(Employee::class, 'manager_id');
    // }

    // public function financialYear()
    // {
    //     return $this->belongsTo(FinancialYear::class);
    // }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // الصناعات المرتبطة بالفرع
    public function industries()
    {
        return $this->hasMany(Industry::class);
    }

    // أنواع الأعمال المرتبطة بالفرع
    public function businessTypes()
    {
        return $this->hasMany(BusinessType::class);
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
}
