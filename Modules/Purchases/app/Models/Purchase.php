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
use Modules\Billing\Models\Journal;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchases';

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'currency_id',
        'employee_id',
        'supplier_id',
        'customer_id',
        'journal_id',
        'journal_number',
        'outgoing_order_number',
        'customer_number',
        'customer_name',
        'customer_email',
        'customer_mobile',
        'licensed_operator',
        'type',
        'status',
        'date',
        'time',
        'due_date',
        'journal_code',
        'journal_invoice_count',
        'cash_paid',
        'checks_paid',
        'allowed_discount',
        'discount_percentage',
        'discount_amount',
        'total_without_tax',
        'tax_percentage',
        'tax_amount',
        'is_tax_inclusive',
        'remaining_balance',
        'exchange_rate',
        'total_foreign',
        'total_local',
        'total_amount',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
        'due_date' => 'date',
        'journal_number' => 'integer',
        'journal_invoice_count' => 'integer',
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
        'total_foreign' => 'decimal:4',
        'total_local' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'is_tax_inclusive' => 'boolean',
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
     * Get the user that owns the purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the purchase.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the purchase.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the currency for this purchase.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the supplier for this purchase.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the customer for this purchase (for outgoing orders).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the journal for this purchase.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get the items for this purchase.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the user who created the purchase.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the purchase.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate the next sequential outgoing order number.
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
     * Scope to get outgoing orders only.
     */
    public function scopeOutgoingOrders($query)
    {
        return $query->where('type', 'outgoing_order');
    }

    /**
     * Scope to get purchases for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get purchases by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get purchases by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
