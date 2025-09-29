<?php

namespace Modules\Purchases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_id',
        'serial_number',
        'shipment_number',
        'item_id',
        'item_number',
        'item_name',
        'unit_id',
        'unit_name',
        'warehouse_number',
        'warehouse_id',
        'description',
        'quantity',
        'unit_price',
        'discount_rate',
        'discount_percentage',
        'discount_amount',
        'net_unit_price',
        'line_total_before_tax',
        'tax_rate',
        'tax_amount',
        'line_total_after_tax',
        'total_foreign',
        'total_local',
        'total',
        'notes',
    ];

    protected $casts = [
        'serial_number' => 'integer',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_rate' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_unit_price' => 'decimal:4',
        'line_total_before_tax' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'line_total_after_tax' => 'decimal:4',
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
        return $this->belongsTo(\Modules\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Get the unit details
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Unit::class, 'unit_id');
    }

    /**
     * Get the warehouse details
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Warehouse::class, 'warehouse_id');
    }

    /**
     * Calculate net unit price after discount
     */
    public function calculateNetUnitPrice(): float
    {
        $discountAmount = 0;

        if ($this->discount_percentage > 0) {
            $discountAmount = ($this->unit_price * $this->discount_percentage) / 100;
        } elseif ($this->discount_amount > 0) {
            $discountAmount = $this->discount_amount;
        }

        return $this->unit_price - $discountAmount;
    }

    /**
     * Calculate line total before tax
     */
    public function calculateLineTotalBeforeTax(): float
    {
        return $this->quantity * $this->calculateNetUnitPrice();
    }

    /**
     * Calculate tax amount for this line
     */
    public function calculateTaxAmount(): float
    {
        if ($this->tax_rate > 0) {
            return ($this->calculateLineTotalBeforeTax() * $this->tax_rate) / 100;
        }

        return 0;
    }

    /**
     * Calculate line total after tax
     */
    public function calculateLineTotalAfterTax(): float
    {
        return $this->calculateLineTotalBeforeTax() + $this->calculateTaxAmount();
    }

    /**
     * Auto-calculate all totals when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->net_unit_price = $item->calculateNetUnitPrice();
            $item->line_total_before_tax = $item->calculateLineTotalBeforeTax();
            $item->tax_amount = $item->calculateTaxAmount();
            $item->line_total_after_tax = $item->calculateLineTotalAfterTax();
            $item->total = $item->line_total_after_tax;
        });
    }
}
