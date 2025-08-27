<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $table = 'credit_notes';

    protected $fillable = [
        'company_id',
        'user_id',
        'notbook',
        'invoice_number',
        'invoice_date',
        'invoice_time',
        'due_date',
        'client_id',
        'currency_id',
        'currency_rate',
        'account_id',
        'notice_amount',
        'amount',
        'tax_rate',
        'tax_amount',
        'total_notice_amount',
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
