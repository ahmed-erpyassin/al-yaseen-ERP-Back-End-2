<?php

namespace Modules\Purchases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchases';

    protected $fillable = [
        // Basic Information
        'user_id',
        'company_id',
        'branch_id',
        'currency_id',
        'employee_id',
        'supplier_id',
        'customer_id',

        // Quotation Information
        'quotation_number',
        'invoice_number',
        'purchase_invoice_number',
        'entry_number',
        'date',
        'time',
        'due_date',

        // Customer Information
        'customer_number',
        'customer_name',
        'customer_email',
        'customer_mobile',

        // Supplier Information
        'supplier_name',
        'supplier_number',
        'supplier_email',
        'supplier_mobile',
        'licensed_operator',

        // Ledger System
        'journal_id',
        'journal_number',
        'ledger_code',
        'ledger_number',
        'ledger_invoice_count',

        // Type and Status
        'type',
        'status',

        // Financial Information
        'cash_paid',
        'checks_paid',
        'allowed_discount',
        'discount_percentage',
        'discount_amount',
        'total_without_tax',
        'tax_percentage',
        'tax_amount',
        'remaining_balance',
        'exchange_rate',
        'currency_rate',
        'currency_rate_with_tax',
        'tax_rate_id',
        'is_tax_applied_to_currency',
        'total_foreign',
        'total_local',
        'total_amount',
        'grand_total',

        // Additional Information
        'notes',

        // Audit Fields
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
        'due_date' => 'date',
        'cash_paid' => 'decimal:2',
        'checks_paid' => 'decimal:2',
        'allowed_discount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_without_tax' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'currency_rate' => 'decimal:4',
        'currency_rate_with_tax' => 'decimal:4',
        'total_foreign' => 'decimal:4',
        'total_local' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'grand_total' => 'decimal:2',
        'is_tax_applied_to_currency' => 'boolean',
        'ledger_invoice_count' => 'integer',
    ];

    // Constants for purchase types
    const TYPE_OPTIONS = [
        'quotation' => 'Quotation',
        'order' => 'Order',
        'shipment' => 'Shipment',
        'invoice' => 'Invoice',
        'expense' => 'Expense',
        'return_invoice' => 'Return Invoice',
    ];

    // Constants for status
    const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'approved' => 'Approved',
        'sent' => 'Sent',
        'invoiced' => 'Invoiced',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Get the user who created the purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the company that owns the purchase
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the branch for the purchase
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Companies\Models\Branch::class, 'branch_id');
    }

    /**
     * Get the currency for the purchase
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(\Modules\FinancialAccounts\Models\Currency::class, 'currency_id');
    }

    /**
     * Get the supplier for the purchase
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\Modules\Suppliers\Models\Supplier::class, 'supplier_id');
    }

    /**
     * Get the customer for the purchase (for incoming quotations)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Modules\Customers\Models\Customer::class, 'customer_id');
    }

    /**
     * Get the tax rate for currency conversion
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(\Modules\FinancialAccounts\Models\TaxRate::class, 'tax_rate_id');
    }

    /**
     * Get the purchase items
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    /**
     * Get the user who created the record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the record
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the record
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Generate the next sequential quotation number
     */
    public static function generateQuotationNumber(): string
    {
        $lastPurchase = self::where('type', 'quotation')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastPurchase || !$lastPurchase->quotation_number) {
            return 'QUO-0001';
        }

        // Extract number from last quotation number (assuming format QUO-XXXX)
        $lastNumber = (int) substr($lastPurchase->quotation_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'QUO-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next sequential invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $lastPurchase = self::where('type', 'quotation')
            ->whereNotNull('invoice_number')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastPurchase || !$lastPurchase->invoice_number) {
            return 'INV-0001';
        }

        // Extract number from last invoice number (assuming format INV-XXXX)
        $lastNumber = (int) substr($lastPurchase->invoice_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate or get ledger code with invoice counting system
     */
    public static function generateLedgerCode($companyId): array
    {
        return self::generateLedgerInfo($companyId, 'quotation');
    }

    /**
     * Generate ledger information for any purchase type
     */
    public static function generateLedgerInfo($companyId, $type): array
    {
        // Find the current active ledger for this type
        $currentLedger = self::where('company_id', $companyId)
            ->where('type', $type)
            ->whereNotNull('ledger_code')
            ->orderBy('ledger_number', 'desc')
            ->first();

        if (!$currentLedger || $currentLedger->ledger_invoice_count >= 50) {
            // Create new ledger
            $newLedgerNumber = $currentLedger ? $currentLedger->ledger_number + 1 : 1;
            $ledgerPrefix = self::getLedgerPrefix($type);
            $ledgerCode = $ledgerPrefix . '-' . str_pad($newLedgerNumber, 3, '0', STR_PAD_LEFT);

            return [
                'ledger_code' => $ledgerCode,
                'ledger_number' => $newLedgerNumber,
                'ledger_invoice_count' => 1
            ];
        } else {
            // Use existing ledger and increment count
            return [
                'ledger_code' => $currentLedger->ledger_code,
                'ledger_number' => $currentLedger->ledger_number,
                'ledger_invoice_count' => $currentLedger->ledger_invoice_count + 1
            ];
        }
    }

    /**
     * Get ledger prefix based on purchase type
     */
    private static function getLedgerPrefix($type): string
    {
        $prefixes = [
            'quotation' => 'LED',
            'incoming_shipment' => 'SHIP-LED',
            'outgoing_order' => 'ORD-LED',
            'invoice' => 'INV-LED',
            'service' => 'SRV-LED',
            'return_invoice' => 'RET-LED',
        ];

        return $prefixes[$type] ?? 'LED';
    }

    /**
     * Scope to get purchases by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get purchases for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get quotations only
     */
    public function scopeQuotations($query)
    {
        return $query->where('type', 'quotation');
    }
}
