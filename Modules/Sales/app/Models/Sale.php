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
        'date' => 'date',
        'time' => 'datetime:H:i:s',
        'due_date' => 'date',
        'cash_paid' => 'decimal:2',
        'checks_paid' => 'decimal:2',
        'allowed_discount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'total_without_tax' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'total_foreign' => 'decimal:4',
        'total_local' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'is_tax_inclusive' => 'boolean',
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
    public function employee(): BelongsTo
    {
        return $this->belongsTo(\Modules\HumanResources\Models\Employee::class, 'employee_id');
    }

    /**
     * Get the company for this sale
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\Modules\Companies\Models\Company::class, 'company_id');
    }

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
     * Get the journal for this sale
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(\Modules\Billing\Models\Journal::class, 'journal_id');
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

    /**
     * Scope for incoming orders
     */
    public function scopeIncomingOrders($query)
    {
        return $query->where('type', 'incoming_order');
    }

    /**
     * Scope for services
     */
    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    /**
     * Generate the next sequential book code for incoming orders.
     */
    public static function generateBookCode($companyId): string
    {
        // Get the last book code for this company
        $lastSale = self::where('company_id', $companyId)
            ->where('type', 'incoming_order')
            ->whereNotNull('book_code')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastSale || !$lastSale->book_code) {
            return 'BOOK-001';
        }

        // Extract the number from the book code (e.g., BOOK-001 -> 001)
        $lastNumber = (int) substr($lastSale->book_code, -3);

        // Check if current book has reached 50 invoices
        $currentBookInvoicesCount = self::where('company_id', $companyId)
            ->where('type', 'incoming_order')
            ->where('book_code', $lastSale->book_code)
            ->count();

        if ($currentBookInvoicesCount >= 50) {
            // Start new book
            $newNumber = $lastNumber + 1;
            return 'BOOK-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        }

        // Continue with current book
        return $lastSale->book_code;
    }

    /**
     * Generate the next sequential invoice number for incoming orders.
     */
    public static function generateInvoiceNumber($companyId): string
    {
        // Get the last invoice number for this company
        $lastSale = self::where('company_id', $companyId)
            ->where('type', 'incoming_order')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastSale) {
            return 'INV-000001';
        }

        // Extract the number from the invoice number (e.g., INV-000001 -> 1)
        $lastNumber = (int) substr($lastSale->invoice_number, -6);
        $newNumber = $lastNumber + 1;

        return 'INV-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted book display
     */
    public function getBookDisplayAttribute(): string
    {
        return $this->book_code ?? 'N/A';
    }

    /**
     * Get formatted total with currency
     */
    public function getFormattedTotalAttribute(): string
    {
        $currency = $this->currency;
        $symbol = $currency ? $currency->symbol : '';
        return $symbol . ' ' . number_format($this->total_amount, 2);
    }
}
