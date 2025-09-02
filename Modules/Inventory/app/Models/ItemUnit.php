<?php

namespace Modules\Inventory\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemUnit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'item_id',
        'unit_id',
        'conversion_rate',
        'is_default',
        'unit_type',
        'quantity_factor',

        // Balance Unit (وحدة الرصيد)
        'balance_unit',
        'custom_balance_unit',

        // Dimensions (الأبعاد)
        'length',
        'width',
        'height',

        // Second Unit (الوحدة الثانية)
        'second_unit',
        'custom_second_unit',
        'second_unit_contains',
        'custom_second_unit_contains',
        'second_unit_content',
        'second_unit_item_number',

        // Third Unit (الوحدة الثالثة)
        'third_unit',
        'custom_third_unit',
        'third_unit_contains',
        'custom_third_unit_contains',
        'third_unit_content',
        'third_unit_item_number',

        // Default Units (الوحدات الافتراضية)
        'default_handling_unit_id',
        'default_warehouse_id',

        // Legacy Contains Information
        'contains',
        'custom_contains',
        'unit_content',
        'unit_item_number',
        'unit_purchase_price',
        'unit_sale_price',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'conversion_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'quantity_factor' => 'decimal:4',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'unit_purchase_price' => 'decimal:2',
        'unit_sale_price' => 'decimal:2',
        'unit_type' => 'string',
        'balance_unit' => 'string',
        'second_unit' => 'string',
        'third_unit' => 'string',
        'status' => 'string',
    ];

    const STATUS_OPTIONS = [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ];

    const UNIT_TYPE_OPTIONS = [
        'balance' => 'وحدة الرصيد',
        'second' => 'الوحدة الثانية',
        'third' => 'الوحدة الثالثة',
    ];

    const CONTAINS_OPTIONS = [
        'all' => 'الكل',
    ];

    /**
     * Get the user who created the item unit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the item unit.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the item unit.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the item for this item unit.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the unit for this item unit.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who created the item unit.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the item unit.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the item unit.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the default handling unit.
     */
    public function defaultHandlingUnit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Unit::class, 'default_handling_unit_id');
    }

    /**
     * Get the default warehouse.
     */
    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Warehouse::class, 'default_warehouse_id');
    }

    /**
     * Convert quantity from this unit to base unit.
     */
    public function convertToBaseUnit($quantity)
    {
        return $quantity * $this->conversion_rate;
    }

    /**
     * Convert quantity from base unit to this unit.
     */
    public function convertFromBaseUnit($quantity)
    {
        return $quantity / $this->conversion_rate;
    }

    /**
     * Scope to get active item units only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get item units for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get item units for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get item units for a specific item.
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope to get default item units only.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get item units by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('unit_type', $type);
    }

    /**
     * Get the display name for contains.
     */
    public function getContainsDisplayAttribute(): string
    {
        if ($this->custom_contains) {
            return $this->custom_contains;
        }
        return self::CONTAINS_OPTIONS[$this->contains] ?? $this->contains;
    }

    /**
     * Get the display name for balance unit.
     */
    public function getBalanceUnitDisplayAttribute(): string
    {
        if ($this->custom_balance_unit) {
            return $this->custom_balance_unit;
        }
        return \Modules\Inventory\Models\Unit::UNIT_OPTIONS[$this->balance_unit] ?? $this->balance_unit;
    }

    /**
     * Get the display name for second unit.
     */
    public function getSecondUnitDisplayAttribute(): ?string
    {
        if (!$this->second_unit) return null;

        if ($this->custom_second_unit) {
            return $this->custom_second_unit;
        }
        return \Modules\Inventory\Models\Unit::UNIT_OPTIONS[$this->second_unit] ?? $this->second_unit;
    }

    /**
     * Get the display name for third unit.
     */
    public function getThirdUnitDisplayAttribute(): ?string
    {
        if (!$this->third_unit) return null;

        if ($this->custom_third_unit) {
            return $this->custom_third_unit;
        }
        return \Modules\Inventory\Models\Unit::UNIT_OPTIONS[$this->third_unit] ?? $this->third_unit;
    }

    /**
     * Get the display name for second unit contains.
     */
    public function getSecondUnitContainsDisplayAttribute(): string
    {
        if ($this->custom_second_unit_contains) {
            return $this->custom_second_unit_contains;
        }
        return self::CONTAINS_OPTIONS[$this->second_unit_contains] ?? $this->second_unit_contains;
    }

    /**
     * Get the display name for third unit contains.
     */
    public function getThirdUnitContainsDisplayAttribute(): string
    {
        if ($this->custom_third_unit_contains) {
            return $this->custom_third_unit_contains;
        }
        return self::CONTAINS_OPTIONS[$this->third_unit_contains] ?? $this->third_unit_contains;
    }

    /**
     * Get the unit type display name.
     */
    public function getUnitTypeDisplayAttribute(): string
    {
        return self::UNIT_TYPE_OPTIONS[$this->unit_type] ?? $this->unit_type;
    }

    /**
     * Calculate total volume.
     */
    public function getTotalVolumeAttribute(): ?float
    {
        if ($this->length && $this->width && $this->height) {
            return $this->length * $this->width * $this->height;
        }
        return null;
    }

    /**
     * Get unit with full details including unit information.
     */
    public function getFullDetailsAttribute(): array
    {
        return [
            'id' => $this->id,
            'unit_name' => $this->unit->name ?? null,
            'unit_symbol' => $this->unit->symbol ?? null,
            'unit_type' => $this->unit_type,
            'unit_type_display' => $this->unit_type_display,
            'conversion_rate' => $this->conversion_rate,
            'quantity_factor' => $this->quantity_factor,

            // Balance Unit Information
            'balance_unit' => [
                'type' => $this->balance_unit,
                'display' => $this->balance_unit_display,
                'custom' => $this->custom_balance_unit,
            ],

            // Dimensions
            'dimensions' => [
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
                'total_volume' => $this->total_volume,
            ],

            // Second Unit Information
            'second_unit' => [
                'type' => $this->second_unit,
                'display' => $this->second_unit_display,
                'custom' => $this->custom_second_unit,
                'contains' => $this->second_unit_contains_display,
                'custom_contains' => $this->custom_second_unit_contains,
                'content' => $this->second_unit_content,
                'item_number' => $this->second_unit_item_number,
            ],

            // Third Unit Information
            'third_unit' => [
                'type' => $this->third_unit,
                'display' => $this->third_unit_display,
                'custom' => $this->custom_third_unit,
                'contains' => $this->third_unit_contains_display,
                'custom_contains' => $this->custom_third_unit_contains,
                'content' => $this->third_unit_content,
                'item_number' => $this->third_unit_item_number,
            ],

            // Default Units
            'default_units' => [
                'handling_unit' => $this->defaultHandlingUnit ? [
                    'id' => $this->defaultHandlingUnit->id,
                    'name' => $this->defaultHandlingUnit->name,
                    'symbol' => $this->defaultHandlingUnit->symbol,
                ] : null,
                'warehouse' => $this->defaultWarehouse ? [
                    'id' => $this->defaultWarehouse->id,
                    'name' => $this->defaultWarehouse->name,
                    'code' => $this->defaultWarehouse->code,
                ] : null,
            ],

            // Legacy Contains Information
            'contains' => $this->contains_display,
            'unit_content' => $this->unit_content,
            'unit_item_number' => $this->unit_item_number,

            // Pricing
            'pricing' => [
                'purchase_price' => $this->unit_purchase_price,
                'sale_price' => $this->unit_sale_price,
            ],

            'is_default' => $this->is_default,
            'status' => $this->status,
        ];
    }

    /**
     * Convert quantity considering the quantity factor.
     */
    public function convertQuantityWithFactor($quantity, $toBaseUnit = true)
    {
        if ($toBaseUnit) {
            return ($quantity * $this->conversion_rate) * $this->quantity_factor;
        } else {
            return ($quantity / $this->conversion_rate) / $this->quantity_factor;
        }
    }

    /**
     * Get all contains options including custom ones for company.
     */
    public static function getAllContainsOptions($companyId): array
    {
        $predefinedOptions = self::CONTAINS_OPTIONS;

        // Get custom contains options from database
        $customContains = self::forCompany($companyId)
            ->whereNotNull('custom_contains')
            ->pluck('custom_contains')
            ->unique()
            ->filter()
            ->mapWithKeys(function ($contains) {
                return [$contains => $contains];
            })
            ->toArray();

        return array_merge($predefinedOptions, $customContains);
    }
}
