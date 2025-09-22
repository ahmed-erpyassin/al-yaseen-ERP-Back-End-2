<?php

namespace Modules\Inventory\Models;

use Modules\Companies\Models\Company;
use Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'company_id',
        'item_number',
        'item_name_ar',
        'item_name_en',
        'barcode',
        'model',
        'unit',
        'category_id',
        'supplier_id',
        'quantity',
        'minimum_limit',
        'reorder_limit',
        'unit_price',
        'first_purchase_price',
        'second_purchase_price',
        'third_purchase_price',
        'first_sale_price',
        'second_sale_price',
        'third_sale_price',
        'notes',
        'active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'minimum_limit' => 'decimal:2',
        'reorder_limit' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'first_purchase_price' => 'decimal:2',
        'second_purchase_price' => 'decimal:2',
        'third_purchase_price' => 'decimal:2',
        'first_sale_price' => 'decimal:2',
        'second_sale_price' => 'decimal:2',
        'third_sale_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Get the company that owns the inventory item.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the category that owns the inventory item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    /**
     * Get the supplier that owns the inventory item.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the stock records for this item.
     */
    public function stock(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Get the stock movements for this item.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the user who created the item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the item.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get total available quantity across all warehouses.
     */
    public function getTotalAvailableQuantityAttribute(): float
    {
        return $this->stock()->sum('available_quantity');
    }

    /**
     * Get total reserved quantity across all warehouses.
     */
    public function getTotalReservedQuantityAttribute(): float
    {
        return $this->stock()->sum('reserved_quantity');
    }

    /**
     * Check if item is below minimum limit.
     */
    public function isBelowMinimumLimit(): bool
    {
        return $this->total_available_quantity < $this->minimum_limit;
    }

    /**
     * Check if item needs reordering.
     */
    public function needsReorder(): bool
    {
        return $this->total_available_quantity <= $this->reorder_limit;
    }

    /**
     * Scope to get active items only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get items for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
