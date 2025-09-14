<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkType extends Model
{

    protected $table = 'work_types';

    protected $fillable = [
        'title_ar',
        'title_en',
        'status'
    ];

}
