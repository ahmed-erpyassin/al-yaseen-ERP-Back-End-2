<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

class InventoryAdjustmentItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_adjustment_id',
        'inventory_item_id',
        'system_quantity',
        'physical_quantity',
        'difference_quantity',
        'unit_cost',
        'total_cost_impact',
        'notes',
        'batch_number',
        'expiry_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'physical_quantity' => 'decimal:2',
        'difference_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost_impact' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Get the inventory adjustment that owns the item.
     */
    public function inventoryAdjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class);
    }

    /**
     * Get the inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the user who created the record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
