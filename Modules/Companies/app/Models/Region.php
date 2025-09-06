<?php

namespace Modules\Companies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

// use Modules\Companies\Database\Factories\RegionFactory;

class Region extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'country_id',
        'name',
        'name_en',
        'created_by',
        'updated_by'
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'company_id',
            'country_id',
            'name',
            'name_en',
            'created_by',
            'updated_by'
        ]);
    }

    public function scopeFilters($builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'user_id' => null,
            'company_id' => null,
            'country_id' => null,
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

        if ($filters['country_id'] !== null) {
            $builder->where('country_id', $filters['country_id']);
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

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

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
