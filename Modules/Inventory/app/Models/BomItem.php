<?php

namespace Modules\Inventory\Models;

use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Users\Models\User;
use Modules\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BomItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // ✅ Basic Information
        'user_id', 'company_id', 'branch_id',

        // ✅ Main Item Information (Parent/Finished Product) - Removed redundant fields
        'item_id', // item_number, item_name removed - available via relationship

        // ✅ Component Item Information (Raw Material/Sub-assembly) - Removed redundant fields
        'component_id', // component_item_number, component_item_name, component_item_description removed - available via relationship

        // ✅ Unit Information - Removed redundant fields
        'unit_id', // unit_name, unit_code removed - available via relationship

        // ✅ BOM Formula Information
        'formula_number', 'formula_name', 'formula_description',

        // ✅ Item Details (from Items table)
        'balance', 'minimum_limit', 'maximum_limit', 'minimum_reorder_level',

        // ✅ Date and Time
        'formula_date', 'formula_time', 'formula_datetime',

        // ✅ Component Quantities and Requirements
        'quantity', 'required_quantity', 'available_quantity', 'consumed_quantity',
        'produced_quantity', 'waste_quantity', 'yield_percentage',

        // ✅ Pricing Information
        'selling_price', 'purchase_price',
        'first_purchase_price', 'second_purchase_price', 'third_purchase_price',
        'first_selling_price', 'second_selling_price', 'third_selling_price',

        // ✅ Component Costs
        'unit_cost', 'total_cost', 'actual_cost', 'labor_cost', 'operating_cost',
        'waste_cost', 'final_cost', 'material_cost', 'overhead_cost',
        'total_production_cost', 'cost_per_unit',

        // ✅ Component Details
        'component_balance', 'component_minimum_limit', 'component_maximum_limit', 'reorder_level',

        // ✅ Component Type and Properties
        'component_type', 'is_critical', 'is_optional', 'sequence_order',

        // ✅ Formula Status and Control
        'status', 'is_active', 'effective_from', 'effective_to',

        // ✅ Production Information
        'batch_size', 'production_time_minutes', 'preparation_time_minutes',
        'production_notes', 'preparation_notes', 'usage_instructions',

        // ✅ Quality Control
        'tolerance_percentage', 'quality_requirements', 'requires_inspection',

        // ✅ Supplier Information
        'preferred_supplier_id', 'supplier_item_code', 'supplier_unit_price', 'lead_time_days',

        // ✅ System Fields
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        // ✅ Date and Time fields
        'formula_date' => 'date',
        'formula_time' => 'datetime:H:i:s',
        'formula_datetime' => 'datetime',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'is_critical' => 'boolean',
        'is_optional' => 'boolean',
        'requires_inspection' => 'boolean',

        // ✅ Decimal fields
        'quantity' => 'decimal:4',
        'required_quantity' => 'decimal:4',
        'available_quantity' => 'decimal:4',
        'consumed_quantity' => 'decimal:4',
        'produced_quantity' => 'decimal:4',
        'waste_quantity' => 'decimal:4',
        'yield_percentage' => 'decimal:2',
        'balance' => 'decimal:4',
        'minimum_limit' => 'decimal:4',
        'maximum_limit' => 'decimal:4',
        'minimum_reorder_level' => 'decimal:4',
        'selling_price' => 'decimal:4',
        'purchase_price' => 'decimal:4',
        'first_purchase_price' => 'decimal:4',
        'second_purchase_price' => 'decimal:4',
        'third_purchase_price' => 'decimal:4',
        'first_selling_price' => 'decimal:4',
        'second_selling_price' => 'decimal:4',
        'third_selling_price' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'actual_cost' => 'decimal:4',
        'labor_cost' => 'decimal:4',
        'operating_cost' => 'decimal:4',
        'waste_cost' => 'decimal:4',
        'final_cost' => 'decimal:4',
        'material_cost' => 'decimal:4',
        'overhead_cost' => 'decimal:4',
        'total_production_cost' => 'decimal:4',
        'cost_per_unit' => 'decimal:4',
        'component_balance' => 'decimal:4',
        'component_minimum_limit' => 'decimal:4',
        'component_maximum_limit' => 'decimal:4',
        'reorder_level' => 'decimal:4',
        'tolerance_percentage' => 'decimal:2',
        'supplier_unit_price' => 'decimal:4',
        'batch_size' => 'decimal:4',
    ];

    /**
     * Get the user who created the BOM item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the BOM item.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the BOM item.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the parent item (finished product).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the component item (raw material/sub-assembly).
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'component_id');
    }

    /**
     * Get the unit for this BOM item.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who created the BOM item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the BOM item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the BOM item.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * ✅ Get the preferred supplier for this component.
     */
    public function preferredSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    // ✅ Accessors for redundant fields - Get data from relationships instead of stored fields

    /**
     * Get item number from relationship.
     */
    public function getItemNumberAttribute(): ?string
    {
        return $this->item?->item_number;
    }

    /**
     * Get item name from relationship.
     */
    public function getItemNameAttribute(): ?string
    {
        return $this->item?->name;
    }

    /**
     * Get component item number from relationship.
     */
    public function getComponentItemNumberAttribute(): ?string
    {
        return $this->component?->item_number;
    }

    /**
     * Get component item name from relationship.
     */
    public function getComponentItemNameAttribute(): ?string
    {
        return $this->component?->name;
    }

    /**
     * Get component item description from relationship.
     */
    public function getComponentItemDescriptionAttribute(): ?string
    {
        return $this->component?->description;
    }

    /**
     * Get unit name from relationship.
     */
    public function getUnitNameAttribute(): ?string
    {
        return $this->unit?->name;
    }

    /**
     * Get unit code from relationship.
     */
    public function getUnitCodeAttribute(): ?string
    {
        return $this->unit?->code;
    }

    /**
     * ✅ Status options for BOM items.
     */
    const STATUS_OPTIONS = [
        'draft' => 'مسودة',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'archived' => 'مؤرشف'
    ];

    /**
     * ✅ Component types.
     */
    const COMPONENT_TYPES = [
        'raw_material' => 'مواد خام',
        'semi_finished' => 'منتج نصف مصنع',
        'packaging' => 'تعبئة وتغليف',
        'consumable' => 'مواد استهلاكية'
    ];

    /**
     * ✅ Calculate total quantity needed for a given production quantity.
     */
    public function calculateTotalQuantity($productionQuantity)
    {
        return $this->quantity * $productionQuantity;
    }

    /**
     * ✅ Calculate total cost based on required quantity and unit cost.
     */
    public function calculateTotalCost(): float
    {
        return $this->required_quantity * $this->unit_cost;
    }

    /**
     * ✅ Calculate actual cost based on consumed quantity.
     */
    public function calculateActualCost(): float
    {
        return $this->consumed_quantity * $this->unit_cost;
    }

    /**
     * ✅ Calculate waste cost.
     */
    public function calculateWasteCost(): float
    {
        return $this->waste_quantity * $this->unit_cost;
    }

    /**
     * ✅ Check if component is available in sufficient quantity.
     */
    public function isAvailable(): bool
    {
        return $this->available_quantity >= $this->required_quantity;
    }

    /**
     * ✅ Check if component needs reordering.
     */
    public function needsReorder(): bool
    {
        return $this->component_balance <= $this->reorder_level;
    }

    /**
     * ✅ Get shortage quantity if not available.
     */
    public function getShortageQuantity(): float
    {
        $shortage = $this->required_quantity - $this->available_quantity;
        return max(0, $shortage);
    }

    /**
     * ✅ Calculate efficiency percentage.
     */
    public function getEfficiencyPercentage(): float
    {
        if ($this->required_quantity > 0) {
            $actualUsed = $this->consumed_quantity + $this->waste_quantity;
            if ($actualUsed > 0) {
                return ($this->required_quantity / $actualUsed) * 100;
            }
        }
        return 100;
    }

    /**
     * ✅ Get component type label in Arabic.
     */
    public function getComponentTypeLabel(): string
    {
        return self::COMPONENT_TYPES[$this->component_type] ?? $this->component_type;
    }

    /**
     * ✅ Get status label in Arabic.
     */
    public function getStatusLabel(): string
    {
        return self::STATUS_OPTIONS[$this->status] ?? $this->status;
    }

    /**
     * ✅ Check if BOM item is currently effective.
     */
    public function isEffective(): bool
    {
        $now = now()->toDateString();

        $effectiveFrom = $this->effective_from ? $this->effective_from->toDateString() : null;
        $effectiveTo = $this->effective_to ? $this->effective_to->toDateString() : null;

        if ($effectiveFrom && $now < $effectiveFrom) {
            return false;
        }

        if ($effectiveTo && $now > $effectiveTo) {
            return false;
        }

        return $this->is_active && $this->status === 'active';
    }

    /**
     * ✅ Update component costs automatically.
     */
    public function updateCosts(): void
    {
        $this->total_cost = $this->calculateTotalCost();
        $this->actual_cost = $this->calculateActualCost();
        $this->save();
    }

    /**
     * ✅ Check if component is within tolerance.
     */
    public function isWithinTolerance($actualQuantity): bool
    {
        if ($this->tolerance_percentage <= 0) {
            return true;
        }

        $tolerance = ($this->required_quantity * $this->tolerance_percentage) / 100;
        $minAcceptable = $this->required_quantity - $tolerance;
        $maxAcceptable = $this->required_quantity + $tolerance;

        return $actualQuantity >= $minAcceptable && $actualQuantity <= $maxAcceptable;
    }

    /**
     * Scope to get BOM items for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get BOM items for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get BOM items for a specific parent item.
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope to get BOM items that use a specific component.
     */
    public function scopeForComponent($query, $componentId)
    {
        return $query->where('component_id', $componentId);
    }

    /**
     * ✅ Scope for active BOM items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * ✅ Scope for critical components.
     */
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    /**
     * ✅ Scope for components by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('component_type', $type);
    }

    /**
     * ✅ Scope for components ordered by sequence.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order')->orderBy('id');
    }

    /**
     * ✅ Scope for components that need reordering.
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('component_balance <= reorder_level');
    }

    /**
     * ✅ Scope for components with shortage.
     */
    public function scopeWithShortage($query)
    {
        return $query->whereRaw('available_quantity < required_quantity');
    }

    /**
     * ✅ Scope for effective BOM items (within date range).
     */
    public function scopeEffective($query, $date = null)
    {
        $checkDate = $date ?? now()->toDateString();

        return $query->where('is_active', true)
            ->where('status', 'active')
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', $checkDate);
            })
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $checkDate);
            });
    }

    /**
     * ✅ Calculate total BOM cost for all components of an item.
     */
    public static function calculateItemTotalCost($itemId): float
    {
        return self::where('item_id', $itemId)
            ->where('is_active', true)
            ->sum('total_cost');
    }

    /**
     * ✅ Get BOM items with low stock components.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('component_balance <= component_minimum_limit');
    }

    /**
     * ✅ Get BOM items that require inspection.
     */
    public function scopeRequiresInspection($query)
    {
        return $query->where('requires_inspection', true);
    }
}
