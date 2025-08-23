<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    protected $table = 'currencies';

    protected $fillable = [
        'name_en',
        'name_ar',
        'code',
        'status'
    ];

}
