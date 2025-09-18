<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funder extends Model
{
    protected $table = 'funders';

    protected $fillable = [
        'company_id',
        'user_id',
        'number',
        'manager',
        'address',
        'work_phone',
        'fax_number',
        'home_phone',
        'statement',
        'statement_en',
        'file_open_start',
        'is_active',
        'notes'
    ];

    public function documents()
    {
        return $this->morphMany(Document::class, 'related');
    }
}
