<?php

namespace Modules\Inventory\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ManufacturingProcess extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        
        // Manufacturing Formula Information
        'manufacturing_formula_id',
        'manufacturing_formula_number',
        'manufacturing_formula_name',
        
        // Item Information (Final Product)
        'item_id',
        'item_number',
        'item_name',
        
        // Manufacturing Details
        'manufacturing_duration',
        'manufacturing_duration_unit', // days, hours, minutes
        'produced_quantity',
        'expected_quantity',
        'actual_quantity',
        
        // Warehouse Information
        'raw_materials_warehouse_id',
        'finished_product_warehouse_id',
        'raw_materials_warehouse_name',
        'finished_product_warehouse_name',
        
        // Process Status
        'status', // draft, in_progress, completed, cancelled
        'process_date',
        'start_date',
        'end_date',
        'completion_percentage',
        
        // Cost Information
        'total_raw_material_cost',
        'labor_cost',
        'overhead_cost',
        'total_manufacturing_cost',
        'cost_per_unit',
        
        // Additional Information
        'notes',
        'quality_check_passed',
        'batch_number',
        'production_order_number',
        
        // System Fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'process_date' => 'date',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'produced_quantity' => 'decimal:4',
        'expected_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:4',
        'total_raw_material_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'total_manufacturing_cost' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'quality_check_passed' => 'boolean',
    ];

    const STATUS_OPTIONS = [
        'draft' => 'مسودة',
        'in_progress' => 'قيد التنفيذ',
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
     * Get the company that owns the manufacturing process.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the manufacturing process.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the manufacturing formula for this process.
     */
    public function manufacturingFormula(): BelongsTo
    {
        return $this->belongsTo(BomItem::class, 'manufacturing_formula_id');
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
     * Get the raw materials for this manufacturing process.
     */
    public function rawMaterials(): HasMany
    {
        return $this->hasMany(ManufacturingProcessRawMaterial::class);
    }

    /**
     * Get the user who created the process.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the process.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the process.
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
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by manufacturing formula.
     */
    public function scopeByFormula(Builder $query, $formulaId): Builder
    {
        return $query->where('manufacturing_formula_id', $formulaId);
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
     * Get duration unit options for dropdown.
     */
    public static function getDurationUnitOptions(): array
    {
        return collect(self::DURATION_UNITS)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values()->toArray();
    }

    /**
     * Calculate total manufacturing cost.
     */
    public function calculateTotalCost(): float
    {
        $rawMaterialCost = $this->rawMaterials()->sum('total_cost');
        $laborCost = $this->labor_cost ?? 0;
        $overheadCost = $this->overhead_cost ?? 0;
        
        return $rawMaterialCost + $laborCost + $overheadCost;
    }

    /**
     * Calculate cost per unit.
     */
    public function calculateCostPerUnit(): float
    {
        $totalCost = $this->calculateTotalCost();
        $quantity = $this->actual_quantity ?? $this->produced_quantity ?? 1;
        
        return $quantity > 0 ? $totalCost / $quantity : 0;
    }

    /**
     * Update completion percentage based on progress.
     */
    public function updateCompletionPercentage(): void
    {
        if ($this->status === 'completed') {
            $this->completion_percentage = 100;
        } elseif ($this->status === 'in_progress') {
            // Calculate based on time elapsed or other factors
            if ($this->start_date && $this->end_date) {
                $totalDuration = $this->start_date->diffInMinutes($this->end_date);
                $elapsed = $this->start_date->diffInMinutes(now());
                $this->completion_percentage = min(99, ($elapsed / $totalDuration) * 100);
            }
        } else {
            $this->completion_percentage = 0;
        }
        
        $this->save();
    }

    /**
     * Check if manufacturing can be started.
     */
    public function canStart(): bool
    {
        return $this->status === 'draft' && 
               $this->rawMaterials()->count() > 0 &&
               $this->checkRawMaterialAvailability();
    }

    /**
     * Check if all raw materials are available in sufficient quantities.
     */
    public function checkRawMaterialAvailability(): bool
    {
        foreach ($this->rawMaterials as $rawMaterial) {
            if ($rawMaterial->available_quantity < $rawMaterial->consumed_quantity) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get missing raw materials.
     */
    public function getMissingRawMaterials(): array
    {
        $missing = [];
        
        foreach ($this->rawMaterials as $rawMaterial) {
            if ($rawMaterial->available_quantity < $rawMaterial->consumed_quantity) {
                $missing[] = [
                    'item_id' => $rawMaterial->item_id,
                    'item_number' => $rawMaterial->item_number,
                    'item_name' => $rawMaterial->item_name,
                    'required_quantity' => $rawMaterial->consumed_quantity,
                    'available_quantity' => $rawMaterial->available_quantity,
                    'shortage_quantity' => $rawMaterial->consumed_quantity - $rawMaterial->available_quantity,
                ];
            }
        }
        
        return $missing;
    }
}
