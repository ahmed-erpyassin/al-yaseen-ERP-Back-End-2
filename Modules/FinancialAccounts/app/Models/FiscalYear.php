<?php

namespace Modules\FinancialAccounts\Models;

use App\Traits\HasUserStamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Users\Models\User;

class FiscalYear extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUserStamps;

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'user_id',
            'company_id',
            'name',
            'start_date',
            'end_date',
            'status',
            'created_by',
            'updated_by',
            'deleted_by',
            'created_at',
            'updated_at',
            'deleted_at'
        ]);
    }

    public function scopeCurrentYearId($companyId, $userId): ?int
    {
        $fiscalYear = self::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();
        return $fiscalYear ? $fiscalYear->id : null;
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
                    ->orWhere('start_date', 'like', "%{$filters['search']}%")
                    ->orWhere('end_date', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['status']) {
            $builder->where('status', $filters['status']);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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
