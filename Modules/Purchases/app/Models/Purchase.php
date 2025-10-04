<?php

namespace Modules\Purchases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;
use Modules\Companies\Models\Branch;
use Modules\Customers\Models\Customer;
use Modules\Suppliers\Models\Supplier;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Billing\Models\Journal;
use Illuminate\Support\Facades\DB;

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
        'journal_id',
        'journal_number',

        // Quotation Information
        'quotation_number',
        'invoice_number',
        'outgoing_order_number',
        'expense_number',
        'purchase_reference_invoice_number',
        'ledger_code',
        'affects_inventory',
        'is_tax_applied_to_currency_rate',
        'currency_rate_with_tax',
        'ledger_invoice_count',
        'journal_code',
        'journal_invoice_count',
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
        'supplier_email',
        'licensed_operator',

        // Ledger System
        'ledger_code',
        'ledger_number',
        'ledger_invoice_count',
        'journal_code',
        'journal_invoice_count',

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
        'journal_number' => 'integer',
        'journal_invoice_count' => 'integer',
        'ledger_invoice_count' => 'integer',
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
        'is_tax_applied_to_currency_rate' => 'boolean',
        'affects_inventory' => 'boolean',
        'ledger_invoice_count' => 'integer',
    ];

    // Constants for purchase types
    const TYPE_OPTIONS = [
        'quotation' => 'Quotation',
        'order' => 'Order',
        'outgoing_order' => 'Outgoing Order',
        'shipment' => 'Shipment',
        'invoice' => 'Invoice',
        'expense' => 'Expense',
        'return_invoice' => 'Return Invoice',
        'purchase_reference_invoice' => 'Purchase Reference Invoice',
    ];

    // Constants for status
    const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'approved' => 'Approved',
        'sent' => 'Sent',
        'invoiced' => 'Invoiced',
        'cancelled' => 'Cancelled',
    ];

    const INVOICES_PER_JOURNAL = 50;

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
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the currency for the purchase
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Get the supplier for the purchase
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the customer for the purchase (for outgoing orders and quotations)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the journal for this purchase
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    /**
     * Get the tax rate for currency conversion
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
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
     * Generate the next sequential outgoing order number
     */
    public static function generateOutgoingOrderNumber(): string
    {
        $lastOrder = self::where('type', 'outgoing_order')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastOrder) {
            return 'OUT-0001';
        }

        // Extract number from last order number (assuming format OUT-XXXX)
        $lastNumber = (int) substr($lastOrder->outgoing_order_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'OUT-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
     * Generate the next sequential expense number
     */
    public static function generateExpenseNumber(): string
    {
        $lastExpense = self::where('type', 'expense')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastExpense || !$lastExpense->expense_number) {
            return 'EXP-0001';
        }

        // Extract number from last expense number (assuming format EXP-XXXX)
        $lastNumber = (int) substr($lastExpense->expense_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'EXP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next sequential invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::whereNotNull('invoice_number')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastInvoice || !$lastInvoice->invoice_number) {
            return 'INV-0001';
        }

        // Extract number from last invoice number (assuming format INV-XXXX)
        $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate purchase reference invoice number
     */
    public static function generatePurchaseReferenceInvoiceNumber(): string
    {
        $lastInvoice = self::where('type', 'purchase_reference_invoice')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastInvoice || !$lastInvoice->purchase_reference_invoice_number) {
            return 'PRI-0001';
        }

        $lastNumber = (int) substr($lastInvoice->purchase_reference_invoice_number, -4);
        $nextNumber = $lastNumber + 1;

        return 'PRI-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate journal code and invoice number for outgoing orders
     */
    public static function generateJournalAndInvoiceNumber($companyId): array
    {
        return DB::transaction(function () use ($companyId) {
            // Get the current journal and invoice numbers
            $currentJournal = self::getCurrentJournal($companyId);
            $nextInvoiceNumber = self::getNextInvoiceNumber($companyId);

            // Check if we need to create a new journal
            if (self::shouldCreateNewJournal($companyId, $currentJournal)) {
                $currentJournal = self::createNewJournal($companyId);
            }

            return [
                'journal_code' => $currentJournal,
                'invoice_number' => $nextInvoiceNumber,
                'journal_number' => self::getJournalNumber($companyId)
            ];
        });
    }

    /**
     * Generate or get ledger code with invoice counting system
     */
    public static function generateLedgerCode($companyId): array
    {
        // Find the current active ledger
        $currentLedger = self::where('company_id', $companyId)
            ->where('type', 'quotation')
            ->whereNotNull('ledger_code')
            ->orderBy('ledger_number', 'desc')
            ->first();

        if (!$currentLedger || $currentLedger->ledger_invoice_count >= 50) {
            // Create new ledger
            $newLedgerNumber = $currentLedger ? $currentLedger->ledger_number + 1 : 1;
            $ledgerCode = 'LED-' . str_pad($newLedgerNumber, 3, '0', STR_PAD_LEFT);

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
     * Get current journal for outgoing orders
     */
    private static function getCurrentJournal($companyId): string
    {
        $lastOrder = self::where('type', 'outgoing_order')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastOrder || !$lastOrder->journal_code) {
            return 'JOU-001';
        }

        return $lastOrder->journal_code;
    }

    /**
     * Get next invoice number
     */
    private static function getNextInvoiceNumber($companyId): int
    {
        $lastOrder = self::where('type', 'outgoing_order')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        return $lastOrder ? ($lastOrder->journal_number + 1) : 1;
    }

    /**
     * Check if we should create a new journal
     */
    private static function shouldCreateNewJournal($companyId, $currentJournal): bool
    {
        $invoicesInCurrentJournal = self::where('type', 'outgoing_order')
            ->where('company_id', $companyId)
            ->where('journal_code', $currentJournal)
            ->count();

        return $invoicesInCurrentJournal >= self::INVOICES_PER_JOURNAL;
    }

    /**
     * Create new journal
     */
    private static function createNewJournal($companyId): string
    {
        $lastJournal = self::where('type', 'outgoing_order')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastJournal || !$lastJournal->journal_code) {
            return 'JOU-001';
        }

        // Extract number from last journal code (assuming format JOU-XXX)
        $lastNumber = (int) substr($lastJournal->journal_code, -3);
        $nextNumber = $lastNumber + 1;

        return 'JOU-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get journal number
     */
    private static function getJournalNumber($companyId): int
    {
        $lastOrder = self::where('type', 'outgoing_order')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        return $lastOrder ? ($lastOrder->journal_number + 1) : 1;
    }

    /**
     * Scope to get outgoing orders only
     */
    public function scopeOutgoingOrders($query)
    {
        return $query->where('type', 'outgoing_order');
    }

    /**
     * Scope to get quotations only
     */
    public function scopeQuotations($query)
    {
        return $query->where('type', 'quotation');
    }

    /**
     * Scope for purchase reference invoices only
     */
    public function scopePurchaseReferenceInvoices($query)
    {
        return $query->where('type', 'purchase_reference_invoice');
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
     * Scope to get purchases by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get expenses only
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }
}
