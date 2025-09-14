<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'inventory_item_id',
        'item_description',
        'unit',
        'quantity_ordered',
        'quantity_received',
        'quantity_remaining',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'net_unit_price',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    const STATUS_OPTIONS = [
        'pending' => 'في الانتظار',
        'partially_received' => 'مستلم جزئياً',
        'received' => 'مستلم',
        'cancelled' => 'ملغي',
    ];

    /**
     * Get the purchase order that owns the item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Calculate remaining quantity.
     */
    public function calculateRemainingQuantity(): void
    {
        $this->quantity_remaining = $this->quantity_ordered - $this->quantity_received;
        $this->save();
    }

    /**
     * Update status based on received quantity.
     */
    public function updateStatus(): void
    {
        if ($this->quantity_received == 0) {
            $this->status = 'pending';
        } elseif ($this->quantity_received < $this->quantity_ordered) {
            $this->status = 'partially_received';
        } else {
            $this->status = 'received';
        }
        $this->save();
    }
}
