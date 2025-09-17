<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Users\Models\User;

class StockTransfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_number',
        'transfer_date',
        'expected_date',
        'received_date',
        'status',
        'notes',
        'transfer_reason',
        'approved_by',
        'approved_at',
        'received_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'approved_at' => 'datetime',
    ];

    const STATUS_OPTIONS = [
        'draft' => 'مسودة',
        'sent' => 'مرسل',
        'in_transit' => 'في الطريق',
        'received' => 'مستلم',
        'cancelled' => 'ملغي',
    ];

    /**
     * Get the company that owns the transfer.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the transfer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source warehouse.
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the destination warehouse.
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the user who approved the transfer.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who received the transfer.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the transfer items.
     */
    public function transferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
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
