<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Users\Models\User;

class InventoryAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'warehouse_id',
        'adjustment_number',
        'adjustment_date',
        'adjustment_type',
        'reason',
        'status',
        'notes',
        'reason_description',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    const ADJUSTMENT_TYPES = [
        'increase' => 'زيادة',
        'decrease' => 'نقص',
        'recount' => 'إعادة عد',
    ];

    const REASONS = [
        'damaged' => 'تالف',
        'expired' => 'منتهي الصلاحية',
        'lost' => 'مفقود',
        'found' => 'موجود',
        'recount' => 'إعادة عد',
        'other' => 'أخرى',
    ];

    const STATUS_OPTIONS = [
        'draft' => 'مسودة',
        'approved' => 'معتمد',
        'cancelled' => 'ملغي',
    ];

    /**
     * Get the company that owns the adjustment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the adjustment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse for the adjustment.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who approved the adjustment.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the adjustment items.
     */
    public function adjustmentItems(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
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
     * Scope for company filtering.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for status filtering.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
