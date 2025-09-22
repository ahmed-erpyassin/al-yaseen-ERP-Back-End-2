<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Users\Models\User;

class Employee extends Model
{

    protected $table = 'employees';

    protected $fillable = [
        'company_id',
        'user_id',
        'employee_number',
        'nickname',
        'first_name',
        'second_name',
        'third_name',
        'phone1',
        'phone2',
        'email',
        'birth_date',
        'address',
        'national_id',
        'id_number',
        'gender',
        'wives_count',
        'children_count',
        'dependents_count',
        'car_number',
        'is_driver',
        'is_sales',
        'job_title',
        'hiring_date',
        'employee_code',
        'employee_identifier',
        'job_address',
        'department_id',
        'salary',
        'billing_rate',
        'monthly_discount',
        'currency_id',
        'notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'related');
    }
}
