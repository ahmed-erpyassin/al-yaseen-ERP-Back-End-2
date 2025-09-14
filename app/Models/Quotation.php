<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{

    protected $table = 'quotations';

    protected $fillable = [
        'company_id',
        'user_id',
        'quotation_number',
        'quotation_date',
        'expiry_date',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'license_number',
        'currency_id',
        'exchange_rate',
        'allowed_discount',
        'subtotal_without_tax',
        'precentage',
        'vat',
        'total',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }


    public function documents()
    {
        return $this->morphMany(Document::class, 'related');
    }
}
