<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Modules\Sales\Database\Factories\SaleItemFactory;

class SaleItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sale_items';

    protected $guarded = ['id'];


}
