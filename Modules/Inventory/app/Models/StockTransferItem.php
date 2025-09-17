<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;

class StockTransferItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'stock_transfer_id',
        'inventory_item_id',
        'quantity_sent',
        'quantity_received',
        'quantity_damaged',
        'unit',
        'unit_cost',
        'total_cost',
        'notes',
        'batch_number',
        'expiry_date',
        'condition',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'quantity_sent' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'quantity_damaged' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    const CONDITION_OPTIONS = [
        'good' => 'جيد',
        'damaged' => 'تالف',
        'expired' => 'منتهي الصلاحية',
    ];

    /**
     * Get the stock transfer that owns the item.
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
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
