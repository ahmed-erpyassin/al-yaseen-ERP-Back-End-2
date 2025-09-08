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
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('name_en', 'like', "%{$filters['search']}%");
            });
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
