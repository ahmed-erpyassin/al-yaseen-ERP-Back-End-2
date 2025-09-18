<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Users\Models\User;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales';

    protected $guarded = ['id'];

    protected $casts = [
        'time' => 'datetime:H:i:s',
        'due_date' => 'date',
        'cash_paid' => 'decimal:2',
        'checks_paid' => 'decimal:2',
        'allowed_discount' => 'decimal:2',
        'total_without_tax' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'total_foreign' => 'decimal:4',
        'total_local' => 'decimal:4',
        'total_amount' => 'decimal:4',
    ];

    /**
     * Get the user who created the record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created the record
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the record
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the record
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the customer for this sale
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Modules\Customers\Models\Customer::class, 'customer_id');
    }

    /**
     * Get the employee for this sale
     */
    // public function employee(): BelongsTo
    // {
    //     return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    // }

    /**
     * Get the currency for this sale
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(\Modules\FinancialAccounts\Models\Currency::class, 'currency_id');
    }

    /**
     * Get the branch for this sale
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Companies\Models\Branch::class, 'branch_id');
    }

    /**
     * Get the sale items
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    /**
     * Scope for quotations (outgoing offers)
     */
    public function scopeQuotations($query)
    {
        return $query->where('type', 'quotation');
    }

    /**
     * Scope for outgoing shipments
     */
    public function scopeOutgoingShipments($query)
    {
        return $query->where('type', 'outgoing_shipment');
    }

    /**
     * Scope for invoices
     */
    public function scopeInvoices($query)
    {
        return $query->where('type', 'invoice');
    }
}
