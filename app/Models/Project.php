<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'notebook',
        'project_number',
        'date',
        'time',
        'phone',
        'name',
        'email',
        'licensed_operator',
        'currency_id',
        'currency_price',
        'project_price',
        'project_name',
        'manager_name',
        'opportunity',
        'statement',
        'country_id',
        'start_date',
        'end_date',
        'status',
        'notes',
        'attachments',
    ];
}
