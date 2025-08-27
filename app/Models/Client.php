<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    protected $fillable = [
        'company_id',
        'user_id',
        'client_type',
        'client_number',
        'company_name',
        'first_name',
        'second_name',
        'phone',
        'mobile',
        'address1',
        'address2',
        'city',
        'region',
        'postal_code',
        'licensed_operator',
        'code_number',
        'invoice_method',
        'department_id',
        'project_id',
        'funder_id',
        'currency_id',
        'employee_id',
        'email',
        'category',
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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function funder()
    {
        return $this->belongsTo(Funder::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
