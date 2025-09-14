<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'company_id',
        'user_id',
        'manager',
        'address',
        'work_phone',
        'home_phone',
        'fax',
        'description',
        'description_en',
        'funder_id',
        'parent_id',
        'status',
        'expected_start_date',
        'expected_end_date',
        'actual_start_date',
        'actual_end_date',
        'budget_id',
        'notes',
    ];
}
