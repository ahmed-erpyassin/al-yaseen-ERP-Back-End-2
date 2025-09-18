<?php

namespace Modules\Users\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Modules\Companies\Models\Company;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'first_name',
        'second_name',
        'email',
        'phone',
        'password',
        'status',
        'type',
        'otp_code',
        'otp_expires_at',
        'email_verified_at',
        'phone_verified_at',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    // لتعيين دور لمستخدم API
    public function assignRoleApi($roleName)
    {
        $role = Role::findByName($roleName, 'api'); // تحديد guard api
        $this->assignRole($role);
    }

    // لتعيين دور لمستخدم Web
    public function assignRoleWeb($roleName)
    {
        $role = Role::findByName($roleName, 'web'); // تحديد guard web
        $this->assignRole($role);
    }

    // Relationships

    public function company()
    {
        return $this->hasOne(Company::class, 'user_id', 'id');
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

    public function scopeData($builder)
    {
        return $builder->select([
            'id',
            'first_name',
            'second_name',
            'email',
            'phone',
            'status',
            'type',
            'email_verified_at',
            'phone_verified_at',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->second_name;
    }

    public function scopeFilters(Builder $builder, array $filters = [])
    {
        $filters = array_merge([
            'search' => '',
            'status' => null,
            'type' => null
        ], $filters);

        $builder->when($filters['search'] != '', function ($query) use ($filters) {
            $query->whereRaw("CONCAT(first_name, ' ', second_name) LIKE ?", ['%' . $filters['search'] . '%'])
                ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
        });

        $builder->when($filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $builder->when($filters['type'] !== null, function ($query) use ($filters) {
            $query->where('type', $filters['type']);
        });
    }

    public function scopeStore(Builder $builder, array $data = [])
    {
        $user = $builder->create($data);
        return $user ? true : false;
    }

    public function scopeUpdateModel(Builder $builder, $data, $id)
    {
        if ($data["password"]) {
            $data["password"] = Hash::make($data["password"]);
        } else {
            unset($data["password"]);
        }

        $user = $builder->find($id);

        if ($user) {
            return $user->update($data);
        }
        return false;
    }

    // public function getRoleAttribute()
    // {
    //     return $this->getRoleNames()->first();
    // }
}
