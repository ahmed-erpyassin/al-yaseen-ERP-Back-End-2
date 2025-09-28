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
        'serial_number' => 'integer',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_rate' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
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
     * Get the unit details
     * Get the unit for this item
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Unit::class, 'unit_id');
    }

    /**
     * Get the warehouse for this item
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the account for this service item
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(\Modules\FinancialAccounts\Models\Account::class, 'account_id');
    }

    /**
     * Get the tax rate for this item
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(\Modules\FinancialAccounts\Models\TaxRate::class, 'tax_rate_id');
    }

    /**
     * Calculate total amount for this item
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;

        // Calculate discount (can be percentage or amount)
        $discount = 0;
        if ($this->discount_percentage > 0) {
            $discount = $subtotal * ($this->discount_percentage / 100);
        } elseif ($this->discount_amount > 0) {
            $discount = $this->discount_amount;
        }

        $afterDiscount = $subtotal - $discount;
        $tax = $afterDiscount * ($this->tax_rate / 100);

        return $afterDiscount + $tax;
    }

    /**
     * Get formatted unit price with currency
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total with currency
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    /**
     * Get item display name
     */
    public function getItemDisplayNameAttribute(): string
    {
        return $this->item_name ?? ($this->item ? $this->item->name : 'N/A');
    }

    /**
     * Get unit display name
     */
    public function getUnitDisplayNameAttribute(): string
    {
        return $this->unit_name ?? ($this->unit ? $this->unit->name : 'N/A');
    }
}
