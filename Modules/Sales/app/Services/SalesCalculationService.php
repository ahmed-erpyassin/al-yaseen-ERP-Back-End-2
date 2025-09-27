<?php

namespace Modules\Sales\app\Services;

use Modules\Companies\Models\Company;
use Modules\FinancialAccounts\Models\TaxRate;

class SalesCalculationService
{
    /**
     * Calculate totals for a sale with items
     */
    public function calculateSaleTotals(array &$saleData, array $items): void
    {
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        // Calculate item totals
        foreach ($items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $itemDiscount = $this->calculateItemDiscount($itemSubtotal, $item['discount_rate'] ?? 0);
            $itemAfterDiscount = $itemSubtotal - $itemDiscount;
            $itemTax = $this->calculateItemTax($itemAfterDiscount, $item['tax_rate'] ?? 0);

            $subtotal += $itemSubtotal;
            $totalDiscount += $itemDiscount;
            $totalTax += $itemTax;
        }

        // Apply sale-level discount if provided
        $saleDiscount = $this->calculateSaleDiscount($subtotal, $saleData);
        $totalAfterDiscount = $subtotal - $saleDiscount;

        // Apply VAT/tax if enabled
        $vatAmount = $this->calculateVAT($totalAfterDiscount, $saleData);

        // Update sale data
        $saleData['total_without_tax'] = $totalAfterDiscount;
        $saleData['tax_amount'] = $totalTax + $vatAmount;
        $saleData['total_amount'] = $totalAfterDiscount + $totalTax + $vatAmount;
        
        // Calculate foreign and local amounts based on exchange rate
        $exchangeRate = $saleData['exchange_rate'] ?? 1.0;
        $saleData['total_foreign'] = $saleData['total_amount'];
        $saleData['total_local'] = $saleData['total_amount'] * $exchangeRate;
    }

    /**
     * Calculate discount for an item
     */
    public function calculateItemDiscount(float $subtotal, float $discountRate): float
    {
        return $subtotal * ($discountRate / 100);
    }

    /**
     * Calculate tax for an item
     */
    public function calculateItemTax(float $amount, float $taxRate): float
    {
        return $amount * ($taxRate / 100);
    }

    /**
     * Calculate sale-level discount (percentage or amount)
     */
    public function calculateSaleDiscount(float $subtotal, array $saleData): float
    {
        $allowedDiscount = $saleData['allowed_discount'] ?? 0;
        
        if ($allowedDiscount <= 0) {
            return 0;
        }

        // Check if discount is percentage (typically values <= 100) or fixed amount
        if ($allowedDiscount <= 100) {
            // Treat as percentage
            return $subtotal * ($allowedDiscount / 100);
        } else {
            // Treat as fixed amount
            return min($allowedDiscount, $subtotal); // Don't exceed subtotal
        }
    }

    /**
     * Calculate VAT based on company settings or tax rates
     */
    public function calculateVAT(float $amount, array $saleData): float
    {
        $companyId = $saleData['company_id'];
        $taxPercentage = $saleData['tax_percentage'] ?? null;

        // If tax percentage is explicitly provided, use it
        if ($taxPercentage !== null && $taxPercentage > 0) {
            return $amount * ($taxPercentage / 100);
        }

        // Otherwise, get from company VAT rate
        $company = Company::find($companyId);
        if ($company && $company->vat_rate > 0) {
            return $amount * ($company->vat_rate / 100);
        }

        return 0;
    }

    /**
     * Get tax rate from tax_rates table
     */
    public function getTaxRateFromTable(int $companyId, string $type = 'vat'): ?float
    {
        $taxRate = TaxRate::where('company_id', $companyId)
            ->where('type', $type)
            ->first();

        return $taxRate ? $taxRate->rate : null;
    }

    /**
     * Calculate discount with both percentage and amount options
     */
    public function calculateFlexibleDiscount(float $subtotal, $discountValue, string $discountType = 'auto'): array
    {
        $discountAmount = 0;
        $discountPercentage = 0;

        if ($discountValue <= 0) {
            return [
                'discount_amount' => 0,
                'discount_percentage' => 0,
                'amount_after_discount' => $subtotal
            ];
        }

        switch ($discountType) {
            case 'percentage':
                $discountPercentage = min($discountValue, 100); // Cap at 100%
                $discountAmount = $subtotal * ($discountPercentage / 100);
                break;
                
            case 'amount':
                $discountAmount = min($discountValue, $subtotal); // Don't exceed subtotal
                $discountPercentage = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;
                break;
                
            case 'auto':
            default:
                // Auto-detect: if value <= 100, treat as percentage, otherwise as amount
                if ($discountValue <= 100) {
                    $discountPercentage = $discountValue;
                    $discountAmount = $subtotal * ($discountPercentage / 100);
                } else {
                    $discountAmount = min($discountValue, $subtotal);
                    $discountPercentage = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;
                }
                break;
        }

        return [
            'discount_amount' => round($discountAmount, 2),
            'discount_percentage' => round($discountPercentage, 2),
            'amount_after_discount' => round($subtotal - $discountAmount, 2)
        ];
    }

    /**
     * Calculate comprehensive item totals
     */
    public function calculateItemTotals(array $itemData): array
    {
        $quantity = $itemData['quantity'] ?? 0;
        $unitPrice = $itemData['unit_price'] ?? 0;
        $discountRate = $itemData['discount_rate'] ?? 0;
        $taxRate = $itemData['tax_rate'] ?? 0;

        // Base calculations
        $subtotal = $quantity * $unitPrice;
        $discountAmount = $this->calculateItemDiscount($subtotal, $discountRate);
        $amountAfterDiscount = $subtotal - $discountAmount;
        $taxAmount = $this->calculateItemTax($amountAfterDiscount, $taxRate);
        $total = $amountAfterDiscount + $taxAmount;

        return [
            'subtotal' => round($subtotal, 4),
            'discount_amount' => round($discountAmount, 4),
            'amount_after_discount' => round($amountAfterDiscount, 4),
            'tax_amount' => round($taxAmount, 4),
            'total' => round($total, 4)
        ];
    }

    /**
     * Validate calculation data
     */
    public function validateCalculationData(array $data): array
    {
        $errors = [];

        // Validate required fields
        if (!isset($data['quantity']) || $data['quantity'] <= 0) {
            $errors[] = 'Quantity must be greater than 0';
        }

        if (!isset($data['unit_price']) || $data['unit_price'] < 0) {
            $errors[] = 'Unit price must be 0 or greater';
        }

        // Validate rates
        if (isset($data['discount_rate']) && ($data['discount_rate'] < 0 || $data['discount_rate'] > 100)) {
            $errors[] = 'Discount rate must be between 0 and 100';
        }

        if (isset($data['tax_rate']) && ($data['tax_rate'] < 0 || $data['tax_rate'] > 100)) {
            $errors[] = 'Tax rate must be between 0 and 100';
        }

        return $errors;
    }

    /**
     * Format currency amounts
     */
    public function formatCurrency(float $amount, int $decimals = 2): string
    {
        return number_format($amount, $decimals);
    }

    /**
     * Calculate remaining balance after payments
     */
    public function calculateRemainingBalance(float $totalAmount, float $cashPaid = 0, float $checksPaid = 0): float
    {
        $totalPaid = $cashPaid + $checksPaid;
        return max(0, $totalAmount - $totalPaid);
    }
}
