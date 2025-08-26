<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{

    protected $table = 'quotation_items';

    protected $fillable = [
        'quotation_id',
        'number',
        'item_name',
        'unit',
        'quantity',
        'unit_price',
        'total',
    ];


    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
