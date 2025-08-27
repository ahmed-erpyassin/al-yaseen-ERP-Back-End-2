<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnInvoiceItem extends Model
{
    protected $fillable = [
        'sales_return_invoice_id',
        'item_code',
        'item_name',
        'unit',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
    ];



    public function returnInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_return_invoice_id');
    }
}
