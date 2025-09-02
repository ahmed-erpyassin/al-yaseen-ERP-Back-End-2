<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarcodeType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'is_default',
        'is_active',
        'validation_rules',
        'min_length',
        'max_length',
        'pattern',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'validation_rules' => 'array',
        'min_length' => 'integer',
        'max_length' => 'integer',
    ];

    /**
     * Get items that use this barcode type.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'barcode_type_id');
    }

    /**
     * Scope to get active barcode types only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get the default barcode type.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the display name based on locale.
     */
    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
    }

    /**
     * Get the display description based on locale.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description;
    }

    /**
     * Validate a barcode value against this type's rules.
     */
    public function validateBarcode(string $barcode): array
    {
        $errors = [];

        // Check length
        $length = strlen($barcode);
        if ($this->min_length && $length < $this->min_length) {
            $errors[] = "الباركود يجب أن يكون على الأقل {$this->min_length} أحرف";
        }
        if ($this->max_length && $length > $this->max_length) {
            $errors[] = "الباركود يجب أن يكون أقل من {$this->max_length} أحرف";
        }

        // Check pattern
        if ($this->pattern && !preg_match($this->pattern, $barcode)) {
            $errors[] = "تنسيق الباركود غير صحيح لنوع {$this->display_name}";
        }

        // Additional validation based on type
        if ($this->code === 'EAN13' && strlen($barcode) === 13) {
            if (!$this->validateEAN13CheckDigit($barcode)) {
                $errors[] = "رقم التحقق في الباركود EAN13 غير صحيح";
            }
        }

        if ($this->code === 'UPCA' && strlen($barcode) === 12) {
            if (!$this->validateUPCACheckDigit($barcode)) {
                $errors[] = "رقم التحقق في الباركود UPC-A غير صحيح";
            }
        }

        if ($this->code === 'ITF' && strlen($barcode) % 2 !== 0) {
            $errors[] = "باركود ITF يجب أن يكون عدد أحرفه زوجي";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate EAN13 check digit.
     */
    private function validateEAN13CheckDigit(string $barcode): bool
    {
        $digits = str_split($barcode);
        $checkDigit = array_pop($digits);
        
        $sum = 0;
        foreach ($digits as $index => $digit) {
            $sum += $digit * (($index % 2 === 0) ? 1 : 3);
        }
        
        $calculatedCheckDigit = (10 - ($sum % 10)) % 10;
        return $calculatedCheckDigit == $checkDigit;
    }

    /**
     * Validate UPC-A check digit.
     */
    private function validateUPCACheckDigit(string $barcode): bool
    {
        $digits = str_split($barcode);
        $checkDigit = array_pop($digits);
        
        $sum = 0;
        foreach ($digits as $index => $digit) {
            $sum += $digit * (($index % 2 === 0) ? 3 : 1);
        }
        
        $calculatedCheckDigit = (10 - ($sum % 10)) % 10;
        return $calculatedCheckDigit == $checkDigit;
    }

    /**
     * Generate a barcode image using Milon library.
     */
    public function generateBarcodeImage(string $barcode, array $options = []): string
    {
        $generator = new \Milon\Barcode\DNS1D();
        
        $defaultOptions = [
            'w' => 2, // Width
            'h' => 30, // Height
            'color' => [0, 0, 0], // Black color
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        try {
            return $generator->getBarcodePNG(
                $barcode,
                $this->code,
                $options['w'],
                $options['h'],
                $options['color']
            );
        } catch (\Exception $e) {
            throw new \Exception("فشل في إنشاء الباركود: " . $e->getMessage());
        }
    }

    /**
     * Get all supported barcode types for Milon library.
     */
    public static function getSupportedTypes(): array
    {
        return [
            'C128' => 'Code 128',
            'EAN13' => 'EAN-13',
            'C39' => 'Code 39',
            'UPCA' => 'UPC-A',
            'ITF' => 'Interleaved 2 of 5',
        ];
    }
}
