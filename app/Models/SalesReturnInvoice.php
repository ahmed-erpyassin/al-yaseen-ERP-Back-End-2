<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnInvoice extends Model
{
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
        'include_tax',
        'notes',
        'attachments',
        'cash_paid',
        'card_paid',
        'card_cash_currency',
        'allowed_discount',
        'subtotal_without_tax',
        'vat',
        'total_amount',
        'advance_paid',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'invoice_time' => 'datetime:H:i',
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
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function cardCurrency()
    {
        return $this->belongsTo(Currency::class, 'card_cash_currency');
    }

    public function items()
    {
        return $this->hasMany(SalesReturnInvoiceItem::class, 'sales_return_invoice_id');
    }
}
