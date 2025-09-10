<?php

namespace Modules\Inventory\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        // Movement Information
        'movement_number',
        'movement_type',
        'movement_date',
        'movement_time',
        'movement_datetime',
        // Vendor/Customer References
        'vendor_id',
        'customer_id',
        'vendor_name',
        'customer_name',
        // Description
        'movement_description',
        // Invoice References
        'inbound_invoice_id',
        'outbound_invoice_id',
        'inbound_invoice_number',
        'outbound_invoice_number',
        // Additional Information
        'user_number',
        'shipment_number',
        'invoice_number',
        'reference',
        // Warehouse Reference
        'warehouse_id',
        'warehouse_number',
        'warehouse_name',
        // Status and Control
        'status',
        'is_confirmed',
        'confirmed_at',
        'confirmed_by',
        // Totals
        'total_quantity',
        'total_value',
        'total_items',
        // System Fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'movement_time' => 'datetime:H:i:s',
        'movement_datetime' => 'datetime',
        'confirmed_at' => 'datetime',
        'is_confirmed' => 'boolean',
        'total_quantity' => 'decimal:4',
        'total_value' => 'decimal:2',
        'total_items' => 'integer',
    ];

    /**
     * ✅ Movement Types Constants
     */
    const MOVEMENT_TYPES = [
        'outbound' => 'صادر',
        'inbound' => 'وارد',
        'transfer' => 'تحويل',
        'manufacturing' => 'تصنيع',
        'inventory_count' => 'جرد'
    ];

    /**
     * ✅ Status Constants
     */
    const STATUS_OPTIONS = [
        'draft' => 'مسودة',
        'confirmed' => 'مؤكد',
        'cancelled' => 'ملغي'
    ];

    /**
     * ✅ Scope for company filtering
     */
    public function scopeForCompany(Builder $query, $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * ✅ Get the company that owns the movement.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ✅ Get the user who created the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ✅ Get the warehouse for this movement.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * ✅ Get the vendor (prepare code without creating table).
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vendor::class, 'vendor_id');
    }

    /**
     * ✅ Get the customer (prepare code without creating table).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    /**
     * ✅ Get the inbound invoice (prepare code without creating table).
     */
    public function inboundInvoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PurchaseInvoice::class, 'inbound_invoice_id');
    }

    /**
     * ✅ Get the outbound invoice (prepare code without creating table).
     */
    public function outboundInvoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SalesInvoice::class, 'outbound_invoice_id');
    }

    /**
     * ✅ Get the movement data (details).
     */
    public function movementData(): HasMany
    {
        return $this->hasMany(InventoryMovementData::class);
    }

    /**
     * ✅ Get the user who created the movement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ✅ Get the user who last updated the movement.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * ✅ Get the user who confirmed the movement.
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * ✅ Get the user who deleted the movement.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * ✅ Get movement type in Arabic.
     */
    public function getMovementTypeNameAttribute(): string
    {
        return self::MOVEMENT_TYPES[$this->movement_type] ?? $this->movement_type;
    }

    /**
     * ✅ Get status in Arabic.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUS_OPTIONS[$this->status] ?? $this->status;
    }

    /**
     * ✅ Get display name for movement.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->movement_number . ' - ' . $this->movement_type_name;
    }

    /**
     * ✅ Boot method to set automatic values.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set automatic date and time on insert
            if (!$model->movement_date) {
                $model->movement_date = now()->toDateString();
            }
            if (!$model->movement_time) {
                $model->movement_time = now()->toTimeString();
            }
            if (!$model->movement_datetime) {
                $model->movement_datetime = now();
            }
        });
    }
}
