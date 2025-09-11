<?php

namespace Modules\Inventory\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ManufacturingProcessRawMaterial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'manufacturing_process_id',
        'company_id',
        
        // Item Information
        'item_id',
        'item_number',
        'item_name',
        'item_description',
        
        // Unit Information
        'unit_id',
        'unit_name',
        
        // Warehouse Information
        'warehouse_id',
        'warehouse_name',
        
        // Quantity Information
        'consumed_quantity',
        'available_quantity',
        'reserved_quantity',
        'actual_consumed_quantity',
        
        // Cost Information
        'unit_cost',
        'total_cost',
        'actual_unit_cost',
        'actual_total_cost',
        
        // Status Information
        'status', // available, insufficient, reserved, consumed
        'is_available',
        'is_critical',
        'shortage_quantity',
        
        // Additional Information
        'batch_number',
        'expiry_date',
        'notes',
        
        // System Fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'consumed_quantity' => 'decimal:4',
        'available_quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'actual_consumed_quantity' => 'decimal:4',
        'shortage_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'actual_unit_cost' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',
        'is_available' => 'boolean',
        'is_critical' => 'boolean',
        'expiry_date' => 'date',
    ];

    const STATUS_OPTIONS = [
        'available' => 'متوفر',
        'insufficient' => 'غير كافي',
        'reserved' => 'محجوز',
        'consumed' => 'مستهلك',
    ];

    /**
     * Get the manufacturing process that owns this raw material.
     */
    public function manufacturingProcess(): BelongsTo
    {
        return $this->belongsTo(ManufacturingProcess::class);
    }

    /**
     * Get the company that owns this raw material.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the item for this raw material.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the unit for this raw material.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the warehouse for this raw material.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created this raw material entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this raw material entry.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this raw material entry.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany(Builder $query, $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by manufacturing process.
     */
    public function scopeForProcess(Builder $query, $processId): Builder
    {
        return $query->where('manufacturing_process_id', $processId);
    }

    /**
     * Scope to filter by availability status.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to filter by insufficient materials.
     */
    public function scopeInsufficient(Builder $query): Builder
    {
        return $query->where('is_available', false);
    }

    /**
     * Scope to filter by critical materials.
     */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('is_critical', true);
    }

    /**
     * Get status options for dropdown.
     */
    public static function getStatusOptions(): array
    {
        return collect(self::STATUS_OPTIONS)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values()->toArray();
    }

    /**
     * Check if the material is available in sufficient quantity.
     */
    public function checkAvailability(): bool
    {
        return $this->available_quantity >= $this->consumed_quantity;
    }

    /**
     * Calculate shortage quantity.
     */
    public function calculateShortage(): float
    {
        $shortage = $this->consumed_quantity - $this->available_quantity;
        return max(0, $shortage);
    }

    /**
     * Update availability status.
     */
    public function updateAvailabilityStatus(): void
    {
        $this->is_available = $this->checkAvailability();
        $this->shortage_quantity = $this->calculateShortage();
        
        if ($this->is_available) {
            $this->status = 'available';
        } else {
            $this->status = 'insufficient';
        }
        
        $this->save();
    }

    /**
     * Calculate total cost based on consumed quantity.
     */
    public function calculateTotalCost(): float
    {
        return $this->consumed_quantity * $this->unit_cost;
    }

    /**
     * Reserve the required quantity.
     */
    public function reserve(): bool
    {
        if (!$this->checkAvailability()) {
            return false;
        }
        
        $this->reserved_quantity = $this->consumed_quantity;
        $this->status = 'reserved';
        $this->save();
        
        return true;
    }

    /**
     * Consume the material (deduct from inventory).
     */
    public function consume(): bool
    {
        if (!$this->checkAvailability()) {
            return false;
        }
        
        $this->actual_consumed_quantity = $this->consumed_quantity;
        $this->actual_unit_cost = $this->unit_cost;
        $this->actual_total_cost = $this->calculateTotalCost();
        $this->status = 'consumed';
        $this->save();
        
        return true;
    }

    /**
     * Get the current stock level for this item in the warehouse.
     */
    public function getCurrentStock(): float
    {
        if (!$this->item_id || !$this->warehouse_id) {
            return 0;
        }
        
        // Get current stock from inventory stock table
        $stock = InventoryStock::where('inventory_item_id', $this->item_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->first();
            
        return $stock ? $stock->available_quantity : 0;
    }

    /**
     * Update available quantity from current stock.
     */
    public function updateAvailableQuantity(): void
    {
        $this->available_quantity = $this->getCurrentStock();
        $this->updateAvailabilityStatus();
    }

    /**
     * Get formatted shortage information.
     */
    public function getShortageInfo(): array
    {
        return [
            'item_id' => $this->item_id,
            'item_number' => $this->item_number,
            'item_name' => $this->item_name,
            'required_quantity' => $this->consumed_quantity,
            'available_quantity' => $this->available_quantity,
            'shortage_quantity' => $this->shortage_quantity,
            'unit_name' => $this->unit_name,
            'is_critical' => $this->is_critical,
        ];
    }

    /**
     * Boot method to set up model events.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->updateAvailableQuantity();
            $model->total_cost = $model->calculateTotalCost();
        });
        
        static::updating(function ($model) {
            if ($model->isDirty(['consumed_quantity', 'unit_cost'])) {
                $model->total_cost = $model->calculateTotalCost();
            }
        });
    }
}
