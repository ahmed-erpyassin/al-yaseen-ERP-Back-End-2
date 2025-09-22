<?php

namespace Modules\Inventory\Models;

use Modules\Companies\Models\Company;
use Modules\Users\Models\User;
use Modules\FinancialAccounts\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'supplier_id',
        'warehouse_id',
        'order_number',
        'order_date',
        'delivery_date',
        'received_date',
        'currency_id',
        'currency_rate',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'status',
        'notes',
        'terms_conditions',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'received_date' => 'date',
        'currency_rate' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    const STATUS_OPTIONS = [
        'draft' => 'مسودة',
        'sent' => 'مرسل',
        'confirmed' => 'مؤكد',
        'partially_received' => 'مستلم جزئياً',
        'received' => 'مستلم',
        'cancelled' => 'ملغي',
    ];

    /**
     * Get the company that owns the purchase order.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the purchase order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier for this purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse for this purchase order.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the currency for this purchase order.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the items for this purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Scope to get orders for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get orders by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
