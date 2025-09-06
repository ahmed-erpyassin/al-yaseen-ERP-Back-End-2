<?php

namespace Modules\Companies\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

// use Modules\Companies\Database\Factories\IndustryFactory;

class Industry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'name',
        'name_en',
        'description',
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
            'name',
            'name_en',
            'description',
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
            'created_by' => null,
            'updated_by' => null,
        ], $filters);

        $builder->when($filters['search'] != '', function ($query) use ($filters) {
            $query->whereRaw("CONCAT(name, ' ', name_en) LIKE ?", ['%' . $filters['search'] . '%'])
                ->orWhere('description', 'like', '%' . $filters['search'] . '%');
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

    // مَن أنشأ الصناعة
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الشركة المالكة
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // الفرع المرتبط
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // أنواع الأعمال التابعة لهذه الصناعة
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
