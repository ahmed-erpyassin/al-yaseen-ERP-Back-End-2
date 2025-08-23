<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    protected $table = 'companies';
    protected $fillable = [
        'user_id',
        'company_name',
        'commercial_registration_number',
        'company_type',
        'work_type',
        'company_address',
        'company_logo',
        'email',
        'country_code',
        'phone',
        'allow_emails',
        'income_tax_rate',
        'vat_rate',
        'fiscal_year',
        'from',
        'to',
        'currency_id',
    ];
}
