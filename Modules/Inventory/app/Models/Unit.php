<?php

namespace Modules\Inventory\Models;

use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'name',
        'code',
        'symbol',
        'description',
        'decimal_places',
        'balance_unit',
        'custom_balance_unit',
        'length',
        'width',
        'height',
        'quantity_factor',
        'second_unit',
        'custom_second_unit',
        'second_unit_contains',
        'custom_second_unit_contains',
        'second_unit_content',
        'second_unit_item_number',
        'third_unit',
        'custom_third_unit',
        'third_unit_contains',
        'custom_third_unit_contains',
        'third_unit_content',
        'third_unit_item_number',
        'default_handling_unit_id',
        'default_warehouse_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'quantity_factor' => 'decimal:4',
        'balance_unit' => 'string',
        'second_unit' => 'string',
        'third_unit' => 'string',
        'status' => 'string',
    ];

    const STATUS_OPTIONS = [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ];

    const UNIT_OPTIONS = [
        'piece' => 'قطعة',
        'liter' => 'لتر',
        'kilo' => 'كيلو',
        'ton' => 'طن',
        'carton' => 'كرتون',
    ];

    const CONTAINS_OPTIONS = [
        'all' => 'الكل',
    ];

    /**
     * Get the user who created the unit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the unit.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the unit.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the unit.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the unit.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the unit.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the items that use this unit.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the item units for this unit.
     */
    public function itemUnits(): HasMany
    {
        return $this->hasMany(ItemUnit::class);
    }

    /**
     * Get the default handling unit.
     */
    public function defaultHandlingUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_handling_unit_id');
    }

    /**
     * Get the default warehouse.
     */
    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    /**
     * Get units that use this unit as default handling unit.
     */
    public function unitsUsingAsDefault(): HasMany
    {
        return $this->hasMany(Unit::class, 'default_handling_unit_id');
    }

    /**
     * Scope to get active units only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get units for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get units for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the display name for balance unit.
     */
    public function getBalanceUnitDisplayAttribute(): string
    {
        if ($this->custom_balance_unit) {
            return $this->custom_balance_unit;
        }
        return self::UNIT_OPTIONS[$this->balance_unit] ?? $this->balance_unit;
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
        return self::UNIT_OPTIONS[$this->second_unit] ?? $this->second_unit;
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
        return self::UNIT_OPTIONS[$this->third_unit] ?? $this->third_unit;
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
     * Get all available unit options including custom ones.
     */
    public static function getAllUnitOptions($companyId): array
    {
        $predefinedOptions = self::UNIT_OPTIONS;

        // Get custom units from database
        $customUnits = self::forCompany($companyId)
            ->whereNotNull('custom_balance_unit')
            ->pluck('custom_balance_unit')
            ->unique()
            ->filter()
            ->mapWithKeys(function ($unit) {
                return [$unit => $unit];
            })
            ->toArray();

        return array_merge($predefinedOptions, $customUnits);
    }

    /**
     * Get all available contains options including custom ones.
     */
    public static function getAllContainsOptions($companyId): array
    {
        $predefinedOptions = self::CONTAINS_OPTIONS;

        // Get custom contains options from database
        $customContains = self::forCompany($companyId)
            ->where(function ($query) {
                $query->whereNotNull('custom_second_unit_contains')
                      ->orWhereNotNull('custom_third_unit_contains');
            })
            ->get()
            ->flatMap(function ($unit) {
                return array_filter([
                    $unit->custom_second_unit_contains,
                    $unit->custom_third_unit_contains
                ]);
            })
            ->unique()
            ->mapWithKeys(function ($contains) {
                return [$contains => $contains];
            })
            ->toArray();

        return array_merge($predefinedOptions, $customContains);
    }
}
