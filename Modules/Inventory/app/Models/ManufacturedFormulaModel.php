<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use App\Models\User;

class ManufacturedFormulaModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'manufactured_formulas';

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',

        // Formula Information
        'formula_number',
        'formula_name',
        'formula_description',

        // Item Information (Final Product) - Using relationships only
        'item_id', // item_number, item_name removed - available via item relationship

        // Manufacturing Details
        'manufacturing_duration',
        'manufacturing_duration_unit',
        'consumed_quantity',
        'produced_quantity',

        // Warehouse Information - Using relationships only
        'raw_materials_warehouse_id', // warehouse_name removed - available via warehouse relationship
        'finished_product_warehouse_id',

        // Date and Time Information
        'formula_date',
        'formula_time',
        'formula_datetime',

        // Cost Information
        'total_raw_material_cost',
        'labor_cost',
        'operating_cost',
        'overhead_cost',
        'waste_cost',
        'total_manufacturing_cost',
        'cost_per_unit',

        // Pricing Information (from Suppliers table)
        'sale_price',
        'purchase_price',

        // Quality Control
        'quality_requirements',
        'quality_check_passed',
        'quality_notes',

        // Status Management
        'status',
        'is_active',
        'completion_percentage',

        // Additional Information
        'notes',
        'batch_number',
        'production_order_number',

        // Audit Fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'formula_date' => 'date',
        'formula_time' => 'datetime:H:i:s',
        'formula_datetime' => 'datetime',
        'consumed_quantity' => 'decimal:4',
        'produced_quantity' => 'decimal:4',
        'total_raw_material_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'operating_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'waste_cost' => 'decimal:2',
        'total_manufacturing_cost' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'sale_price' => 'decimal:4',
        'purchase_price' => 'decimal:4',
        'quality_check_passed' => 'boolean',
        'is_active' => 'boolean',
        'completion_percentage' => 'decimal:2',
    ];

    const STATUS_OPTIONS = [
        'draft' => 'مسودة',
        'active' => 'نشط',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
    ];

    const DURATION_UNITS = [
        'minutes' => 'دقائق',
        'hours' => 'ساعات',
        'days' => 'أيام',
        'weeks' => 'أسابيع',
        'months' => 'أشهر',
    ];

    /**
     * Get the company that owns the manufactured formula.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the manufactured formula.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the manufactured formula.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item being manufactured.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the raw materials warehouse.
     */
    public function rawMaterialsWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'raw_materials_warehouse_id');
    }

    /**
     * Get the finished product warehouse.
     */
    public function finishedProductWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'finished_product_warehouse_id');
    }

    /**
     * Get the raw materials for this formula.
     */
    public function rawMaterials(): HasMany
    {
        return $this->hasMany(ManufacturedFormulaRawMaterialModel::class, 'manufactured_formula_id');
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
     * Scope a query to only include formulas for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include active formulas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include formulas with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Generate next formula number.
     */
    public static function generateFormulaNumber($companyId): string
    {
        $lastFormula = static::where('company_id', $companyId)
            ->whereNotNull('formula_number')
            ->orderBy('formula_number', 'desc')
            ->first();

        if (!$lastFormula) {
            return 'MF-000001';
        }

        $lastNumber = (int) substr($lastFormula->formula_number, 3);
        $newNumber = $lastNumber + 1;

        return 'MF-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total manufacturing cost.
     */
    public function calculateTotalCost(): void
    {
        $this->total_manufacturing_cost =
            $this->total_raw_material_cost +
            $this->labor_cost +
            $this->operating_cost +
            $this->overhead_cost +
            $this->waste_cost;

        if ($this->produced_quantity > 0) {
            $this->cost_per_unit = $this->total_manufacturing_cost / $this->produced_quantity;
        }

        $this->save();
    }

    /**
     * Check if all raw materials are available in sufficient quantities.
     */
    public function checkMaterialsAvailability(): array
    {
        $availability = [
            'all_available' => true,
            'materials' => [],
            'missing_materials' => [],
        ];

        foreach ($this->rawMaterials as $material) {
            $available = $material->checkAvailability();
            $availability['materials'][] = $available;

            if (!$available['is_available']) {
                $availability['all_available'] = false;
                $availability['missing_materials'][] = $available;
            }
        }

        return $availability;
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
     * Get raw materials warehouse name via relationship.
     */
    public function getRawMaterialsWarehouseNameAttribute(): ?string
    {
        return $this->rawMaterialsWarehouse?->name;
    }

    /**
     * Get finished product warehouse name via relationship.
     */
    public function getFinishedProductWarehouseNameAttribute(): ?string
    {
        return $this->finishedProductWarehouse?->name;
    }
}

// Create alias for easier usage
class_alias(ManufacturedFormulaModel::class, 'Modules\Inventory\Models\ManufacturedFormula');
