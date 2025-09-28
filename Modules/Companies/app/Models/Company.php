<?php

namespace Modules\Companies\Models;

use App\Traits\HasFileAttributes;
use App\Traits\HasUserStamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;
use Modules\Users\Models\User;

class Company extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasFileAttributes;
    use HasUserStamps;

    protected $fillable = [
        'user_id',
        'currency_id',
        'financial_year_id',
        'industry_id',
        'business_type_id',
        'country_id',
        'region_id',
        'city_id',
        'title',
        'commercial_registeration_number',
        'address',
        'logo',
        'email',
        'landline',
        'mobile',
        'income_tax_rate',
        'vat_rate',
        'status',
        'created_by',
        'updated_by',
    ];

    // مهم جدًا! لو ناقصة رح يرجع null
    protected array $fileAttributes = [
        'logo' => 'companies/logos',
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'currency_id',
            'financial_year_id',
            'industry_id',
            'business_type_id',
            'country_id',
            'region_id',
            'city_id',
            'title',
            'commercial_registeration_number',
            'address',
            'logo',
            'email',
            'landline',
            'mobile',
            'income_tax_rate',
            'vat_rate',
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
                $query->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['status'] !== null) {
            $builder->where('status', $filters['status']);
        }

        if ($filters['user_id'] !== null) {
            $builder->where('user_id', $filters['user_id']);
        }

        if ($filters['currency_id'] !== null) {
            $builder->where('currency_id', $filters['currency_id']);
        }

        if ($filters['financial_year_id'] !== null) {
            $builder->where('financial_year_id', $filters['financial_year_id']);
        }

        if ($filters['industry_id'] !== null) {
            $builder->where('industry_id', $filters['industry_id']);
        }

        if ($filters['business_type_id'] !== null) {
            $builder->where('business_type_id', $filters['business_type_id']);
        }

        if ($filters['country_id'] !== null) {
            $builder->where('country_id', $filters['country_id']);
        }

        if ($filters['region_id'] !== null) {
            $builder->where('region_id', $filters['region_id']);
        }

        if ($filters['city_id'] !== null) {
            $builder->where('city_id', $filters['city_id']);
        }

        if ($filters['created_by'] !== null) {
            $builder->where('created_by', $filters['created_by']);
        }

        if ($filters['updated_by'] !== null) {
            $builder->where('updated_by', $filters['updated_by']);
        }

        return $builder;
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */


    // الشركة يملكها مستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العملة
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    // السنة المالية
    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    // المجال (Industry)
    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    // نوع العمل (BusinessType)
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    // الدولة
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // المنطقة
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    // المدينة
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // فروع الشركة
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    // الصناعات الخاصة بالشركة
    public function industries()
    {
        return $this->hasMany(Industry::class);
    }

    // أنواع الأعمال الخاصة بالشركة
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


    public function scopeStore(Builder $builder, array $data = [])
    {
        return $builder->create($data);
    }

    public function scopeUpdateModel(Builder $builder, $data, $id)
    {
        $company = $builder->findOrFail($id);
        $company->update($data);
        return $company;
    }
}
