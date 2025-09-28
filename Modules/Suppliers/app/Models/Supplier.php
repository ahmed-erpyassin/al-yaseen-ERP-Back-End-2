<?php

namespace Modules\Suppliers\Models;

use App\Traits\HasUserStamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Branch;
use Modules\Companies\Models\City;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Region;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;
    use HasUserStamps;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'currency_id',
        'employee_id',
        'country_id',
        'region_id',
        'city_id',
        'first_name',
        'second_name',
        'contact_name',
        'email',
        'phone',
        'mobile',
        'address_one',
        'address_two',
        'postal_code',
        'tax_number',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function scopeData($builder)
    {
        return $builder->with([
            'user',
            'company',
            'branch',
            'currency',
            'employee',
            'country',
            'region',
            'city',
            'creator',
            'editor',
            'destroyer',
        ])->select('suppliers.*');
    }

    public function scopeFilters(Builder $builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'status' => null,
            'company_id' => null,
        ], $filters);

        if ($filters['search']) {
            $builder->where(function ($query) use ($filters) {
                $query->where('first_name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('second_name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('contact_name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('mobile', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('tax_number', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['status']) {
            $builder->where('status', $filters['status']);
        }

        if ($filters['company_id']) {
            $builder->where('company_id', $filters['company_id']);
        }
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->second_name);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function destroyer()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeStore(Builder $builder, array $data = [])
    {
        return $builder->create($data);
    }

    public function scopeUpdateModel(Builder $builder, $data, $id)
    {
        $supplier = $builder->findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }

    public function scopeDeleteModel(Builder $builder, $id)
    {
        $supplier = $builder->findOrFail($id);
        return $supplier->delete();
    }
}
