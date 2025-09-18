<?php

namespace Modules\Companies\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

class BusinessType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'industry_id',
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'industry_id',
            'name',
            'description',
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
            'industry_id' => null,
            'created_by' => null,
            'updated_by' => null,
        ], $filters);

        $builder->when($filters['search'] != '', function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        });

        $builder->when($filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $builder->when($filters['industry_id'] !== null, function ($query) use ($filters) {
            $query->where('industry_id', $filters['industry_id']);
        });

        $builder->when($filters['created_by'] !== null, function ($query) use ($filters) {
            $query->where('created_by', $filters['created_by']);
        });

        $builder->when($filters['updated_by'] !== null, function ($query) use ($filters) {
            $query->where('updated_by', $filters['updated_by']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    // الصناعة المرتبطة
    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id');
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
