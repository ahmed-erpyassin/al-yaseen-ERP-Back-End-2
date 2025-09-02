<?php

namespace Modules\Inventory\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'unit_id',
        'parent_id',
        'item_number',
        'code',
        'catalog_number',
        'name',
        'description',
        'model',
        'unit_name',
        'type',
        'quantity',
        'balance',
        'minimum_limit',
        'maximum_limit',
        'reorder_limit',
        'max_reorder_limit',
        // Purchase Prices (أسعار الشراء)
        'first_purchase_price',
        'second_purchase_price',
        'third_purchase_price',
        'purchase_discount_rate',
        'purchase_prices_include_vat',

        // Sale Prices (أسعار البيع)
        'first_sale_price',
        'second_sale_price',
        'third_sale_price',
        'sale_discount_rate',
        'maximum_sale_discount_rate',
        'minimum_allowed_sale_price',
        'sale_prices_include_vat',

        // VAT Information (معلومات الضريبة)
        'item_subject_to_vat',
        
        'notes',

        // Barcode Information (معلومات الباركود)
        'barcode',
        'barcode_type',

        // Product Information (معلومات المنتج)
        'expiry_date',
        'image',
        'color',
        'item_type',

        'active',
        'stock_tracking',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'balance' => 'decimal:2',
        'minimum_limit' => 'decimal:2',
        'maximum_limit' => 'decimal:2',
        'reorder_limit' => 'decimal:2',
        'max_reorder_limit' => 'decimal:2',
        // Purchase Prices
        'first_purchase_price' => 'decimal:2',
        'second_purchase_price' => 'decimal:2',
        'third_purchase_price' => 'decimal:2',
        'purchase_discount_rate' => 'decimal:2',
        'purchase_prices_include_vat' => 'boolean',

        // Sale Prices
        'first_sale_price' => 'decimal:2',
        'second_sale_price' => 'decimal:2',
        'third_sale_price' => 'decimal:2',
        'sale_discount_rate' => 'decimal:2',
        'maximum_sale_discount_rate' => 'decimal:2',
        'minimum_allowed_sale_price' => 'decimal:2',
        'sale_prices_include_vat' => 'boolean',

        // VAT Information
        'item_subject_to_vat' => 'boolean',

        // Product Information
        'expiry_date' => 'date',

        'active' => 'boolean',
        'stock_tracking' => 'boolean',
        'type' => 'string',
        'item_type' => 'string',
    ];

    const TYPE_OPTIONS = [
        'product' => 'منتج',
        'service' => 'خدمة',
        'material' => 'مادة',
        'raw_material' => 'مادة خام',
    ];

    const ITEM_TYPE_OPTIONS = [
        'service' => 'خدمة',
        'goods' => 'بضائع',
        'work' => 'عمل',
        'asset' => 'أصل',
        'transfer' => 'تحويل',
        'minimum' => 'حد أدنى',
    ];

    const BARCODE_TYPE_OPTIONS = [
        'C128' => 'Code 128',
        'EAN13' => 'EAN-13',
        'C39' => 'Code 39',
        'UPCA' => 'UPC-A',
        'ITF' => 'Interleaved 2 of 5',
    ];

    const DEFAULT_BARCODE_TYPE = 'C128';

    /**
     * Get the user who created the item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the item.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the item.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the unit for this item.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the parent item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_id');
    }

    /**
     * Get the child items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Item::class, 'parent_id');
    }

    /**
     * Get the user who created the item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the item.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Alias for creator relationship.
     */
    public function createdBy(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Alias for updater relationship.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->updater();
    }

    /**
     * Alias for deleter relationship.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->deleter();
    }

    /**
     * Get the item units for this item.
     */
    public function itemUnits(): HasMany
    {
        return $this->hasMany(ItemUnit::class);
    }



    /**
     * Get the BOM items for this item (components needed to make this item).
     */
    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class, 'item_id');
    }

    /**
     * Get the BOM items where this item is used as a component.
     */
    public function usedInBoms(): HasMany
    {
        return $this->hasMany(BomItem::class, 'component_id');
    }

    /**
     * Get the default unit for this item.
     */
    public function defaultUnit()
    {
        return $this->itemUnits()->where('is_default', true)->first();
    }

    /**
     * Scope to get items for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get items for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the last sale price from invoice_lines table.
     */
    public function getLastSalePriceFromInvoices(): ?float
    {
        // This would fetch from invoice_lines table when available
        // For now, return the stored first_sale_price as fallback
        return $this->first_sale_price;
    }

    /**
     * Get the first sale price from invoice_lines table.
     */
    public function getFirstSalePriceFromInvoices(): ?float
    {
        // This would fetch from invoice_lines table when available
        // For now, return the stored third_sale_price as fallback
        return $this->third_sale_price;
    }

    /**
     * Get the last purchase price from purchase_items table.
     */
    public function getLastPurchasePriceFromPurchases(): ?float
    {
        // This would fetch from purchase_items table when available
        // For now, return the stored first_purchase_price as fallback
        return $this->first_purchase_price;
    }

    /**
     * Get the first purchase price from purchase_items table.
     */
    public function getFirstPurchasePriceFromPurchases(): ?float
    {
        // This would fetch from purchase_items table when available
        // For now, return the stored third_purchase_price as fallback
        return $this->third_purchase_price;
    }

    /**
     * Calculate sale price with discount.
     */
    public function calculateSalePriceWithDiscount($basePrice, $discountRate = null): float
    {
        $discount = $discountRate ?? $this->sale_discount_rate ?? 0;
        return $basePrice * (1 - ($discount / 100));
    }

    /**
     * Calculate purchase price with discount.
     */
    public function calculatePurchasePriceWithDiscount($basePrice, $discountRate = null): float
    {
        $discount = $discountRate ?? $this->purchase_discount_rate ?? 0;
        return $basePrice * (1 - ($discount / 100));
    }

    /**
     * Calculate price with VAT.
     */
    public function calculatePriceWithVAT($basePrice, $vatRate = 15): float
    {
        if (!$this->item_subject_to_vat) {
            return $basePrice;
        }
        return $basePrice * (1 + ($vatRate / 100));
    }

    /**
     * Calculate price without VAT.
     */
    public function calculatePriceWithoutVAT($priceWithVAT, $vatRate = 15): float
    {
        if (!$this->item_subject_to_vat) {
            return $priceWithVAT;
        }
        return $priceWithVAT / (1 + ($vatRate / 100));
    }

    /**
     * Get VAT rate from tax_rates table when item is subject to VAT.
     */
    public function getVATRateFromTaxRatesTable(): ?float
    {
        if (!$this->item_subject_to_vat) {
            return null;
        }

        // This would fetch from tax_rates table when available
        // For now, return default VAT rate
        return 15.0; // Default Saudi VAT rate
    }

    /**
     * Get tax rate from sales_items table when sale prices include VAT.
     */
    public function getTaxRateFromSalesItemsTable(): ?float
    {
        if (!$this->sale_prices_include_vat) {
            return null;
        }

        // This would fetch from sales_items table when available
        // For now, return default tax rate
        return 15.0; // Default tax rate
    }

    /**
     * Get tax rate from purchase_items table when purchase prices include VAT.
     */
    public function getTaxRateFromPurchaseItemsTable(): ?float
    {
        if (!$this->purchase_prices_include_vat) {
            return null;
        }

        // This would fetch from purchase_items table when available
        // For now, return default tax rate
        return 15.0; // Default tax rate
    }

    /**
     * Apply VAT handling based on toggle states.
     */
    public function applyVATHandling($price, $context = 'sale'): array
    {
        $result = [
            'original_price' => $price,
            'vat_applied' => false,
            'vat_rate' => 0,
            'vat_amount' => 0,
            'final_price' => $price,
            'context' => $context
        ];

        // Item Subject to VAT Toggle Logic
        if ($this->item_subject_to_vat) {
            $vatRate = $this->getVATRateFromTaxRatesTable();
            if ($vatRate) {
                $result['vat_applied'] = true;
                $result['vat_rate'] = $vatRate;
                $result['vat_amount'] = $price * ($vatRate / 100);
                $result['final_price'] = $price + $result['vat_amount'];
            }
        }

        // Sale Prices Include VAT Toggle Logic
        if ($context === 'sale' && $this->sale_prices_include_vat) {
            $taxRate = $this->getTaxRateFromSalesItemsTable();
            if ($taxRate) {
                $result['vat_applied'] = true;
                $result['vat_rate'] = $taxRate;
                $result['vat_amount'] = $price * ($taxRate / 100);
                $result['final_price'] = $price + $result['vat_amount'];
            }
        }

        // Purchase Prices Include VAT Toggle Logic
        if ($context === 'purchase' && $this->purchase_prices_include_vat) {
            $taxRate = $this->getTaxRateFromPurchaseItemsTable();
            if ($taxRate) {
                $result['vat_applied'] = true;
                $result['vat_rate'] = $taxRate;
                $result['vat_amount'] = $price * ($taxRate / 100);
                $result['final_price'] = $price + $result['vat_amount'];
            }
        }

        return $result;
    }

    /**
     * Format percentage with % symbol.
     */
    public function formatPercentage($value): string
    {
        if (is_null($value)) {
            return 'غير محدد';
        }
        return number_format($value, 2) . '%';
    }

    /**
     * Get formatted discount rates with % symbol.
     */
    public function getFormattedDiscountRatesAttribute(): array
    {
        return [
            'sale_discount_rate' => $this->formatPercentage($this->sale_discount_rate),
            'maximum_sale_discount_rate' => $this->formatPercentage($this->maximum_sale_discount_rate),
            'purchase_discount_rate' => $this->formatPercentage($this->purchase_discount_rate),
        ];
    }

    /**
     * Get comprehensive pricing information.
     */
    public function getPricingInfoAttribute(): array
    {
        return [
            'sale_prices' => [
                'first_sale_price' => $this->first_sale_price,
                'second_sale_price' => $this->second_sale_price,
                'third_sale_price' => $this->third_sale_price,
                'last_sale_price_from_invoices' => $this->getLastSalePriceFromInvoices(),
                'first_sale_price_from_invoices' => $this->getFirstSalePriceFromInvoices(),
                'sale_discount_rate' => $this->sale_discount_rate,
                'maximum_sale_discount_rate' => $this->maximum_sale_discount_rate,
                'minimum_allowed_sale_price' => $this->minimum_allowed_sale_price,
                'sale_prices_include_vat' => $this->sale_prices_include_vat,
            ],
            'purchase_prices' => [
                'first_purchase_price' => $this->first_purchase_price,
                'second_purchase_price' => $this->second_purchase_price,
                'third_purchase_price' => $this->third_purchase_price,
                'last_purchase_price_from_purchases' => $this->getLastPurchasePriceFromPurchases(),
                'first_purchase_price_from_purchases' => $this->getFirstPurchasePriceFromPurchases(),
                'purchase_discount_rate' => $this->purchase_discount_rate,
                'purchase_prices_include_vat' => $this->purchase_prices_include_vat,
            ],
            'vat_info' => [
                'item_subject_to_vat' => $this->item_subject_to_vat,
            ],
        ];
    }

    /**
     * Validate minimum sale price.
     */
    public function validateMinimumSalePrice($proposedPrice): bool
    {
        if (!$this->minimum_allowed_sale_price) {
            return true;
        }
        return $proposedPrice >= $this->minimum_allowed_sale_price;
    }

    /**
     * Validate maximum discount rate.
     */
    public function validateMaximumDiscountRate($proposedDiscountRate): bool
    {
        if (!$this->maximum_sale_discount_rate) {
            return true;
        }
        return $proposedDiscountRate <= $this->maximum_sale_discount_rate;
    }

    /**
     * Complete VAT Toggle Logic Implementation
     *
     * This method demonstrates the complete logic for all three VAT toggles:
     * 1. Purchase Prices Include VAT → activate tax handling using tax_rate from purchase_items table
     * 2. Sale Prices Include VAT → activate tax handling using tax_rate from sales_items table
     * 3. Item Subject to VAT → activate tax handling using rate from tax_rates table
     */
    public function getCompleteVATHandling(): array
    {
        $vatHandling = [
            'purchase_prices_include_vat' => [
                'enabled' => $this->purchase_prices_include_vat,
                'description' => 'أسعار الشراء المذكورة تشمل الضريبة المضافة',
                'logic' => $this->purchase_prices_include_vat
                    ? 'تم تفعيل معالجة الضريبة باستخدام حقل tax_rate من جدول purchase_items'
                    : 'لا يتم تطبيق ضريبة',
                'tax_source_table' => $this->purchase_prices_include_vat ? 'purchase_items' : null,
                'tax_source_field' => $this->purchase_prices_include_vat ? 'tax_rate' : null,
                'tax_rate' => $this->purchase_prices_include_vat ? $this->getTaxRateFromPurchaseItemsTable() : 0,
            ],

            'sale_prices_include_vat' => [
                'enabled' => $this->sale_prices_include_vat,
                'description' => 'أسعار البيع المذكورة تشمل الضريبة المضافة',
                'logic' => $this->sale_prices_include_vat
                    ? 'تم تفعيل معالجة الضريبة باستخدام حقل tax_rate من جدول sales_items'
                    : 'لا يتم تطبيق ضريبة',
                'tax_source_table' => $this->sale_prices_include_vat ? 'sales_items' : null,
                'tax_source_field' => $this->sale_prices_include_vat ? 'tax_rate' : null,
                'tax_rate' => $this->sale_prices_include_vat ? $this->getTaxRateFromSalesItemsTable() : 0,
            ],

            'item_subject_to_vat' => [
                'enabled' => $this->item_subject_to_vat,
                'description' => 'يخضع الصنف لضريبة المضافة',
                'logic' => $this->item_subject_to_vat
                    ? 'تم تفعيل معالجة الضريبة باستخدام حقل rate من جدول tax_rates'
                    : 'لا يتم تطبيق ضريبة',
                'tax_source_table' => $this->item_subject_to_vat ? 'tax_rates' : null,
                'tax_source_field' => $this->item_subject_to_vat ? 'rate' : null,
                'tax_rate' => $this->item_subject_to_vat ? $this->getVATRateFromTaxRatesTable() : 0,
            ]
        ];

        return $vatHandling;
    }

    /**
     * Get percentage formatting rules and examples.
     */
    public function getPercentageFormattingRules(): array
    {
        return [
            'format_requirement' => 'يجب إدخال النسبة بالرمز (%)',
            'examples' => [
                'valid' => ['10%', '15.5%', '0%', '100%'],
                'invalid' => ['10', '15.5', 'عشرة بالمئة', '10 percent']
            ],
            'validation_rules' => [
                'min' => '0%',
                'max' => '100%',
                'decimal_places' => 'مسموح بالأرقام العشرية (مثل: 15.75%)'
            ],
            'current_values' => [
                'sale_discount_rate' => $this->formatPercentage($this->sale_discount_rate),
                'maximum_sale_discount_rate' => $this->formatPercentage($this->maximum_sale_discount_rate),
                'purchase_discount_rate' => $this->formatPercentage($this->purchase_discount_rate),
            ]
        ];
    }

    /**
     * Generate barcode for this item using Milon library.
     */
    public function generateBarcode(array $options = []): string
    {
        if (!$this->barcode || !$this->barcode_type) {
            throw new \Exception('الباركود أو نوع الباركود غير محدد');
        }

        $generator = new \Milon\Barcode\DNS1D();

        $defaultOptions = [
            'w' => 2, // Width
            'h' => 30, // Height
            'color' => [0, 0, 0], // Black color
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            return $generator->getBarcodePNG(
                $this->barcode,
                $this->barcode_type,
                $options['w'],
                $options['h'],
                $options['color']
            );
        } catch (\Exception $e) {
            throw new \Exception("فشل في إنشاء الباركود: " . $e->getMessage());
        }
    }

    /**
     * Validate the item's barcode format.
     */
    public function validateBarcode(): array
    {
        if (!$this->barcode || !$this->barcode_type) {
            return [
                'valid' => false,
                'errors' => ['الباركود أو نوع الباركود غير محدد']
            ];
        }

        $errors = [];

        // Validate based on barcode type
        switch ($this->barcode_type) {
            case 'EAN13':
                if (strlen($this->barcode) !== 13 || !ctype_digit($this->barcode)) {
                    $errors[] = 'باركود EAN13 يجب أن يكون 13 رقم';
                }
                break;
            case 'UPCA':
                if (strlen($this->barcode) !== 12 || !ctype_digit($this->barcode)) {
                    $errors[] = 'باركود UPC-A يجب أن يكون 12 رقم';
                }
                break;
            case 'C39':
                if (!preg_match('/^[0-9A-Z\-\.\$\/\+\%\s]+$/', $this->barcode)) {
                    $errors[] = 'باركود Code 39 يحتوي على أحرف غير مدعومة';
                }
                break;
            case 'ITF':
                if (strlen($this->barcode) % 2 !== 0 || !ctype_digit($this->barcode)) {
                    $errors[] = 'باركود ITF يجب أن يكون أرقام بعدد زوجي';
                }
                break;
            case 'C128':
                // Code 128 supports all ASCII characters
                if (!mb_check_encoding($this->barcode, 'ASCII')) {
                    $errors[] = 'باركود Code 128 يجب أن يحتوي على أحرف ASCII فقط';
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get the item type display name.
     */
    public function getItemTypeDisplayAttribute(): string
    {
        return self::ITEM_TYPE_OPTIONS[$this->item_type] ?? $this->item_type;
    }

    /**
     * Check if item is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Get days until expiry.
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get expiry status.
     */
    public function getExpiryStatusAttribute(): string
    {
        if (!$this->expiry_date) {
            return 'no_expiry';
        }

        $daysUntilExpiry = $this->days_until_expiry;

        if ($daysUntilExpiry < 0) {
            return 'expired';
        } elseif ($daysUntilExpiry <= 30) {
            return 'expiring_soon';
        } else {
            return 'valid';
        }
    }

    /**
     * Get expiry status in Arabic.
     */
    public function getExpiryStatusArabicAttribute(): string
    {
        $statusMap = [
            'no_expiry' => 'لا ينتهي',
            'expired' => 'منتهي الصلاحية',
            'expiring_soon' => 'ينتهي قريباً',
            'valid' => 'صالح',
        ];

        return $statusMap[$this->expiry_status] ?? 'غير محدد';
    }

    /**
     * Get the barcode type display name.
     */
    public function getBarcodeTypeDisplayAttribute(): string
    {
        return self::BARCODE_TYPE_OPTIONS[$this->barcode_type] ?? $this->barcode_type ?? 'غير محدد';
    }

    /**
     * Get all available barcode types.
     */
    public static function getAvailableBarcodeTypes(): array
    {
        return self::BARCODE_TYPE_OPTIONS;
    }

    /**
     * Get the default barcode type.
     */
    public static function getDefaultBarcodeType(): string
    {
        return self::DEFAULT_BARCODE_TYPE;
    }

    /**
     * Scope to get items by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get items with stock tracking.
     */
    public function scopeWithStockTracking($query)
    {
        return $query->where('stock_tracking', true);
    }

    /**
     * Scope to get parent items only.
     */
    public function scopeParentsOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get child items only.
     */
    public function scopeChildrenOnly($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope to get active items only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get items with low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'minimum_limit');
    }

    /**
     * Scope to get items that need reordering.
     */
    public function scopeNeedReorder($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_limit');
    }

    /**
     * Check if item is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_limit;
    }

    /**
     * Check if item needs reordering.
     */
    public function needsReorder(): bool
    {
        return $this->quantity <= $this->reorder_limit;
    }

    /**
     * Check if item exceeds maximum limit.
     */
    public function exceedsMaximum(): bool
    {
        return $this->quantity >= $this->maximum_limit;
    }

    /**
     * Scope to get items that exceed maximum limit.
     */
    public function scopeExceedsMaximum($query)
    {
        return $query->whereColumn('quantity', '>=', 'maximum_limit');
    }
}
