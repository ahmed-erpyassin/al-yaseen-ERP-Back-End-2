<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingOrder extends Model
{

    protected $fillable = [
        'company_id',
        'user_id',
        'notebook',
        'invoice_number',
        'invoice_date',
        'invoice_time',
        'due_date',
        'client_id',
        'currency',
        'currency_price',
        'include_tax',
        'allowed_discount',
        'total_without_tax',
        'tax_precentage',
        'tax_value',
        'total',
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

    public function currencyRelation()
    {
        return $this->belongsTo(Currency::class, 'currency');
    }

    public function items()
    {
        return $this->hasMany(IncomingOrderItem::class);
    }
}
