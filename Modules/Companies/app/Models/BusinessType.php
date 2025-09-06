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
        'user_id',
        'company_id',
        'branch_id',
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
            'user_id',
            'company_id',
            'branch_id',
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
            'user_id' => null,
            'company_id' => null,
            'branch_id' => null,
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

        $builder->when($filters['user_id'] !== null, function ($query) use ($filters) {
            $query->where('user_id', $filters['user_id']);
        });

        $builder->when($filters['company_id'] !== null, function ($query) use ($filters) {
            $query->where('company_id', $filters['company_id']);
        });

        $builder->when($filters['branch_id'] !== null, function ($query) use ($filters) {
            $query->where('branch_id', $filters['branch_id']);
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

    // المالك (user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الشركة المرتبطة
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // الفرع المرتبط
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // الصناعة المرتبطة
    public function industry()
    {
        return $this->belongsTo(Industry::class);
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
