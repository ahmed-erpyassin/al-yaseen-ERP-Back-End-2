<?php

namespace Modules\Purchases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Item;

class PurchaseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'serial_number',
        'item_id',
        'item_number',
        'item_name',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'discount_rate',
        'discount_percentage',
        'discount_amount',
        'total_without_tax',
        'tax_rate',
        'total_foreign',
        'total_local',
        'total',
    ];

    protected $casts = [
        'serial_number' => 'integer',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_rate' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_without_tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total_foreign' => 'decimal:4',
        'total_local' => 'decimal:4',
        'total' => 'decimal:4',
    ];

    /**
     * Get the purchase that owns this item
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Get the item details
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Calculate total amount for this item
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountAmount = $this->discount_amount ?: ($subtotal * $this->discount_percentage / 100);
        $totalAfterDiscount = $subtotal - $discountAmount;
        $taxAmount = $totalAfterDiscount * $this->tax_rate / 100;
        
        return $totalAfterDiscount + $taxAmount;
    }

    /**
     * Calculate total without tax
     */
    public function calculateTotalWithoutTax(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountAmount = $this->discount_amount ?: ($subtotal * $this->discount_percentage / 100);
        
        return $subtotal - $discountAmount;
    }

    /**
     * Auto-calculate totals before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total_without_tax = $item->calculateTotalWithoutTax();
            $item->total = $item->calculateTotal();
        });
    }
}
