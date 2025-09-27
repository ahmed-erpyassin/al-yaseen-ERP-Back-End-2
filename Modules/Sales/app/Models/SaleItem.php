<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_items';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total_foreign' => 'decimal:4',
        'total_local' => 'decimal:4',
        'total' => 'decimal:4',
    ];

    /**
     * Get the sale that owns this item
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * Get the item details
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Get the unit for this item
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Unit::class, 'unit_id');
    }

    /**
     * Calculate total amount for this item
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discount = $subtotal * ($this->discount_rate / 100);
        $afterDiscount = $subtotal - $discount;
        $tax = $afterDiscount * ($this->tax_rate / 100);

        return $afterDiscount + $tax;
    }
}
