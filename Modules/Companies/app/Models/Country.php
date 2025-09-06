<?php

namespace Modules\Companies\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

class Country extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'code',
        'name',
        'name_en',
        'phone_code',
        'currency_code',
        'timezone',
        'created_by',
        'updated_by'
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'company_id',
            'code',
            'name',
            'name_en',
            'phone_code',
            'currency_code',
            'timezone',
            'created_by',
            'updated_by'
        ]);
    }


    public function scopeFilters(Builder $builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'status' => null,
            'user_id' => null,
            'company_id' => null,
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('name_en', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['user_id'] !== null) {
            $builder->where('user_id', $filters['user_id']);
        }

        if ($filters['company_id'] !== null) {
            $builder->where('company_id', $filters['company_id']);
        }

        if ($filters['status'] !== null) {
            $builder->where('status', $filters['status']);
        }

        return $builder;
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    // المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الشركة
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // المناطق
    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    // المدن
    public function cities()
    {
        return $this->hasMany(City::class);
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
