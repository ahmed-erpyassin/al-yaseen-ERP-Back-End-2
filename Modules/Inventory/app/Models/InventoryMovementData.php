<?php

namespace Modules\Inventory\Models;

use Modules\Companies\Models\Company;
use Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovementData extends Model
{
    protected $table = 'inventory_movement_data';

    protected $fillable = [
        'company_id',
        'inventory_movement_id',
        // Item Information (redundant fields removed - available via relationships)
        'item_id',
        // Unit Information (redundant fields removed - available via relationships)
        'unit_id',
        // Warehouse Information (redundant fields removed - available via relationships)
        'warehouse_id',
        // Quantity Information
        'inventory_count',
        'quantity',
        'previous_quantity',
        'new_quantity',
        // Pricing Information
        'unit_cost',
        'unit_price',
        'total_cost',
        'total_price',
        // Additional Information
        'notes',
        'batch_number',
        'expiry_date',
        'serial_number',
        // Location Information
        'location_code',
        'shelf_number',
        'bin_number',
        // System Fields
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'inventory_count' => 'decimal:4',
        'quantity' => 'decimal:4',
        'previous_quantity' => 'decimal:4',
        'new_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * ✅ Scope for company filtering
     */
    public function scopeForCompany(Builder $query, $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * ✅ Get the company that owns the movement data.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ✅ Get the inventory movement header.
     */
    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class);
    }

    /**
     * ✅ Get the item (from Items table).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * ✅ Get the unit (from Units table).
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * ✅ Get the warehouse (from Warehouses table).
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * ✅ Get the user who created the movement data.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ✅ Get the user who last updated the movement data.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * ✅ Get item display name from relationship.
     */
    public function getItemDisplayNameAttribute(): string
    {
        $item = $this->item;
        return ($item?->item_number ? $item->item_number . ' - ' : '') . ($item?->name ?? 'Unknown Item');
    }

    /**
     * ✅ Get warehouse display name from relationship.
     */
    public function getWarehouseDisplayNameAttribute(): string
    {
        $warehouse = $this->warehouse;
        return ($warehouse?->warehouse_number ? $warehouse->warehouse_number . ' - ' : '') . ($warehouse?->name ?? 'Unknown Warehouse');
    }

    /**
     * ✅ Get unit display name.
     */
    public function getUnitDisplayNameAttribute(): string
    {
        return $this->unit_name ?? $this->unit_code ?? 'Unknown Unit';
    }

    /**
     * ✅ Calculate total cost automatically.
     */
    public function calculateTotalCost(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    /**
     * ✅ Calculate total price automatically.
     */
    public function calculateTotalPrice(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * ✅ Boot method to calculate totals automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Calculate totals automatically
            $model->total_cost = $model->calculateTotalCost();
            $model->total_price = $model->calculateTotalPrice();

            // Calculate new quantity based on movement type
            if ($model->inventoryMovement) {
                $movementType = $model->inventoryMovement->movement_type;

                switch ($movementType) {
                    case 'inbound':
                    case 'manufacturing':
                        $model->new_quantity = $model->previous_quantity + $model->quantity;
                        break;
                    case 'outbound':
                        $model->new_quantity = $model->previous_quantity - $model->quantity;
                        break;
                    case 'transfer':
                        // For transfers, quantity can be positive or negative
                        $model->new_quantity = $model->previous_quantity + $model->quantity;
                        break;
                    case 'inventory_count':
                        // For inventory count, new quantity is the counted quantity
                        $model->new_quantity = $model->inventory_count;
                        break;
                    default:
                        $model->new_quantity = $model->previous_quantity + $model->quantity;
                }
            }
        });
    }
}
