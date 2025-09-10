<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemType extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the company that owns the item type.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get items that use this item type.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'item_type_id');
    }

    /**
     * Scope to get active item types only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get system item types only.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to get custom item types only.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to get item types for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get the display name based on locale.
     */
    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
    }

    /**
     * Get the display description based on locale.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description;
    }

    /**
     * Get all system item types with their Arabic names.
     */
    public static function getSystemTypes(): array
    {
        return [
            'service' => 'خدمة',
            'goods' => 'بضائع',
            'work' => 'عمل',
            'asset' => 'أصل',
            'transfer' => 'تحويل',
            'minimum' => 'حد أدنى',
        ];
    }

    /**
     * Create a new custom item type for a company.
     */
    public static function createCustomType(int $companyId, string $name, string $nameAr = null): self
    {
        $code = strtolower(str_replace(' ', '_', $name));
        
        // Ensure unique code for the company
        $originalCode = $code;
        $counter = 1;
        while (self::forCompany($companyId)->where('code', $code)->exists()) {
            $code = $originalCode . '_' . $counter;
            $counter++;
        }

        return self::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'name_ar' => $nameAr ?? $name,
            'description' => "Custom item type: {$name}",
            'description_ar' => "نوع صنف مخصص: " . ($nameAr ?? $name),
            'is_system' => false,
            'is_active' => true,
            'sort_order' => self::forCompany($companyId)->max('sort_order') + 1,
        ]);
    }
}
