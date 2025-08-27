<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'account_number',
        'name',
        'account_type',
        'account_nature',
        'level',
        'currency_id',
        'report_type',
        'allow_all_users',
        'allowed_user_id',
        'opening_date',
        'opened_by',
        'linked_account',
        'property_id',
        'depreciation_rate',
        'depreciation_classification',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }


}
