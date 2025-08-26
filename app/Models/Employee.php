<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    protected $table = 'employees';

    protected $fillable = [
        'employee_number',
        'last_name',
        'first_name',
        'second_name',
        'third_name',
        'email',
        'phone1',
        'phone2',
        'birth_date',
        'address',
        'bank_account',
        'iban',
        'car_number',
        'children_count',
        'wives_count',
        'family_count',
        'dependents_count',
        'gender',
        'id_number',

        'job_title',
        'hire_date',
        'employee_manager',
        'employee_status',
        'department',
        'work_title',
        'salary',
        'deductions',
        'allowances',
        'currency',

        'notes',
    ];

    /**
     * علاقة بالمرفقات (لو هتخليها خاصة بالموظف فقط)
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'related');
    }
}
