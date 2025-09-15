<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use App\Models\User;

class ManufacturedFormulaRawMaterialModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'manufactured_formula_raw_materials';

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'manufactured_formula_id',

        // Item Information (Raw Material) - Using relationships only
        'item_id', // item_number, item_name removed - available via item relationship

        // Unit Information - Using relationships only
        'unit_id', // unit_name, unit_code removed - available via unit relationship

        // Warehouse Information - Using relationships only
        'warehouse_id', // warehouse_name removed - available via warehouse relationship

        // Quantity Information
        'consumed_quantity',
        'available_quantity',
        'required_quantity',
        'actual_consumed_quantity',
        'waste_quantity',

        // Cost Information
        'unit_cost',
        'total_cost',
        'actual_cost',

        // Pricing Information (from Suppliers table)
        'sale_price',
        'purchase_price',

        // Material Properties
        'material_type',
        'is_critical',
        'sequence_order',

        // Quality Control
        'quality_requirements',
        'requires_inspection',
        'inspection_passed',

        // Additional Information
        'notes',
        'preparation_instructions',
        'usage_instructions',

        // Audit Fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'consumed_quantity' => 'decimal:4',
        'available_quantity' => 'decimal:4',
        'required_quantity' => 'decimal:4',
        'actual_consumed_quantity' => 'decimal:4',
        'waste_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'sale_price' => 'decimal:4',
        'purchase_price' => 'decimal:4',
        'is_critical' => 'boolean',
        'requires_inspection' => 'boolean',
        'inspection_passed' => 'boolean',
    ];

    const MATERIAL_TYPES = [
        'raw_material' => 'مادة خام',
        'semi_finished' => 'نصف مصنع',
        'packaging' => 'تعبئة وتغليف',
        'consumable' => 'مواد استهلاكية',
    ];

    /**
     * Get the company that owns the raw material.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the raw material.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the raw material.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the manufactured formula this raw material belongs to.
     */
    public function manufacturedFormula(): BelongsTo
    {
        return $this->belongsTo(ManufacturedFormulaModel::class, 'manufactured_formula_id');
    }

    /**
     * Get the item (raw material).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the unit of measurement.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the warehouse where the raw material is stored.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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

    /**
     * Get the user who deleted the record.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include raw materials for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include critical raw materials.
     */
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    /**
     * Check if this raw material is available in sufficient quantity.
     */
    public function checkAvailability(): array
    {
        $currentStock = $this->getCurrentStock();
        $isAvailable = $currentStock >= $this->consumed_quantity;

        return [
            'item_id' => $this->item_id,
            'item_number' => $this->item?->item_number,
            'item_name' => $this->item?->name,
            'required_quantity' => $this->consumed_quantity,
            'available_quantity' => $currentStock,
            'shortage_quantity' => $isAvailable ? 0 : ($this->consumed_quantity - $currentStock),
            'is_available' => $isAvailable,
            'warehouse_name' => $this->warehouse?->name,
        ];
    }

    /**
     * Get current stock quantity for this item in the specified warehouse.
     */
    public function getCurrentStock(): float
    {
        // Get current stock from inventory_stock table
        $stock = InventoryStock::where('item_id', $this->item_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->first();

        return $stock ? $stock->quantity : 0;
    }

    /**
     * Calculate total cost for this raw material.
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->consumed_quantity * $this->unit_cost;
        $this->save();
    }

    /**
     * Get item number via relationship.
     */
    public function getItemNumberAttribute(): ?string
    {
        return $this->item?->item_number;
    }

    /**
     * Get item name via relationship.
     */
    public function getItemNameAttribute(): ?string
    {
        return $this->item?->name;
    }

    /**
     * Get unit name via relationship.
     */
    public function getUnitNameAttribute(): ?string
    {
        return $this->unit?->name;
    }

    /**
     * Get unit code via relationship.
     */
    public function getUnitCodeAttribute(): ?string
    {
        return $this->unit?->code;
    }

    /**
     * Get warehouse name via relationship.
     */
    public function getWarehouseNameAttribute(): ?string
    {
        return $this->warehouse?->name;
    }
}

// Create alias for easier usage
class_alias(ManufacturedFormulaRawMaterialModel::class, 'Modules\Inventory\Models\ManufacturedFormulaRawMaterial');
