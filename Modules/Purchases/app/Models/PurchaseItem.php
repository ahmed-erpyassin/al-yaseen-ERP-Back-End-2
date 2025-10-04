<?php

namespace Modules\Purchases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\FinancialAccounts\Models\Account;

class PurchaseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_items';
    protected $fillable = [
        'purchase_id',
        'serial_number',
        'shipment_number',
        'item_id',
        'item_number',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'discount_rate',
        'discount_percentage',
        'discount_amount',
        'net_unit_price',
        'line_total_before_tax',
        'tax_rate',
        'tax_amount',
        'line_total_after_tax',
        'notes',
        'total_foreign',
        'total_local',
        'total',
        'unit_id',
        'unit_name',
        'warehouse_number',
        'warehouse_id',
    ];

    protected $casts = [
        'serial_number' => 'integer',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_rate' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_unit_price' => 'decimal:4',
        'line_total_before_tax' => 'decimal:4',
        'total_without_tax' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'line_total_after_tax' => 'decimal:4',
        'total_foreign' => 'decimal:4',
        'total_local' => 'decimal:4',
        'total' => 'decimal:4',
    ];

    /**
     * Get the purchase that owns this item
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Get the item details
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the unit details
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the account details (for expense items)
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Calculate net unit price after discount
     */
    public function calculateNetUnitPrice(): float
    {
        $discountAmount = 0;

        if ($this->discount_percentage > 0) {
            $discountAmount = ($this->unit_price * $this->discount_percentage) / 100;
        } elseif ($this->discount_amount > 0) {
            $discountAmount = $this->discount_amount;
        }

        return $this->unit_price - $discountAmount;
    }

    /**
     * Calculate line total before tax
     */
    public function calculateLineTotalBeforeTax(): float
    {
        return $this->quantity * $this->calculateNetUnitPrice();
    }

    /**
     * Calculate tax amount for this line
     */
    public function calculateTaxAmount(): float
    {
        if ($this->tax_rate > 0) {
            return ($this->calculateLineTotalBeforeTax() * $this->tax_rate) / 100;
        }

        return 0;
    }

    /**
     * Calculate line total after tax
     */
    public function calculateLineTotalAfterTax(): float
    {
        return $this->calculateLineTotalBeforeTax() + $this->calculateTaxAmount();
    }

    /**
     * Calculate total amount for this item (backward compatibility)
     */
    public function calculateTotal(): float
    {
        return $this->calculateLineTotalAfterTax();
    }

    /**
     * Calculate total without tax (backward compatibility)
     */
    public function calculateTotalWithoutTax(): float
    {
        return $this->calculateLineTotalBeforeTax();
    }

    /**
     * Auto-calculate all totals when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Calculate all values
            $item->net_unit_price = $item->calculateNetUnitPrice();
            $item->line_total_before_tax = $item->calculateLineTotalBeforeTax();
            $item->tax_amount = $item->calculateTaxAmount();
            $item->line_total_after_tax = $item->calculateLineTotalAfterTax();

            // Set total field
            $item->total = $item->calculateTotal();
        });
    }
}
