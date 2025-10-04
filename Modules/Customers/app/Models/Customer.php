<?php

namespace Modules\Customers\Models;

use App\Traits\HasUserStamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Region;
use Modules\Companies\Models\City;
use Modules\FinancialAccounts\Models\Currency;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;
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
        'customer_number',
        'company_name',
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
            'currency',
            'country',
            'region',
            'city',
            'creator',
            'updater',
            'deleter'
        ])->select('customers.*');
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->second_name);
    }

    public function scopeFilters(Builder $builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'status' => null,
            'company_id' => null,
        ], $filters);

        $builder->when($filters['search'] != '', function ($query) use ($filters) {
            $query->whereRaw("CONCAT(first_name, ' ', second_name) LIKE ?", ['%' . $filters['search'] . '%'])
                ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                ->orWhere('mobile', 'like', '%' . $filters['search'] . '%')
                ->orWhere('contact_name', 'like', '%' . $filters['search'] . '%');
        });

        $builder->when($filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $builder->when($filters['company_id'] !== null, function ($query) use ($filters) {
            $query->where('company_id', $filters['company_id']);
        });
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeStore(Builder $builder, array $data = [])
    {
        $customer = $builder->create($data);
        return $customer;
    }

    public function scopeUpdateModel(Builder $builder, $data, $id)
    {
        $customer = $builder->findOrFail($id);
        $customer->update($data);
        return $customer;
    }

    public function sccopeDeleteModel(Builder $builder, $id)
    {
        $customer = $builder->findOrFail($id);
        return $customer->delete();
    }

    /**
     * Generate the next sequential customer number.
     *
     * @return string The generated customer number in format CUS-XXXX
     */
    public static function generateCustomerNumber(): string
    {
        $lastCustomer = self::orderBy('id', 'desc')->first();

        if (!$lastCustomer || !$lastCustomer->customer_number) {
            return 'CUS-0001';
        }

        // Extract number from last customer number (assuming format CUS-XXXX)
        $lastNumber = (int) substr($lastCustomer->customer_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'CUS-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
