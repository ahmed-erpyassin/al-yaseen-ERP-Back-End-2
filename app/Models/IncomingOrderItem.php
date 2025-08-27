<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingOrderItem extends Model
{
    protected $fillable = [
        'incoming_order_id',
        'item_number',
        'item_name',
        'unit',
        'quantity',
        'unit_price',
        'total',
    ];

    public function incomingOrder()
    {
        return $this->belongsTo(IncomingOrder::class);
    }
}
