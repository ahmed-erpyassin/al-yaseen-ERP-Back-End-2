<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingShipmentItem extends Model
{
    protected $fillable = [
        'outgoing_shipment_id',
        'item_number',
        'item_name',
        'item_statement',
        'unit',
        'quantity',
        'warehouse_id',
    ];
    public function outgoingShipment()
    {
        return $this->belongsTo(OutgoingShipment::class);
    }
}
