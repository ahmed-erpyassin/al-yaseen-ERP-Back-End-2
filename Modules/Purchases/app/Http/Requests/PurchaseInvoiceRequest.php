<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'supplier_id' => 'required|exists:suppliers,id',
            'currency_id' => 'required|exists:currencies,id',
            'employee_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            
            // Purchase Invoice Information
            'due_date' => 'required|date|after_or_equal:today',
            'supplier_email' => 'nullable|email|max:150',
            'supplier_mobile' => 'nullable|string|max:20',
            'licensed_operator' => 'nullable|string|max:255',
            
            // Financial Information
            'exchange_rate' => 'nullable|numeric|min:0.0001',
            'currency_rate' => 'nullable|numeric|min:0.0001',
            'currency_rate_with_tax' => 'nullable|numeric|min:0.0001',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'is_tax_applied_to_currency' => 'nullable|boolean',
            
            // Discount Information
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'allowed_discount' => 'nullable|numeric|min:0',
            
            // Tax Information
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            
            // Totals
            'total_without_tax' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',
            'total_foreign' => 'nullable|numeric|min:0',
            'total_local' => 'nullable|numeric|min:0',
            
            // Payment Information
            'cash_paid' => 'nullable|numeric|min:0',
            'checks_paid' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            
            // Additional Information
            'notes' => 'nullable|string|max:1000',
            
            // Items validation
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Basic Information Messages
            'supplier_id.required' => 'Supplier is required.',
            'supplier_id.exists' => 'Selected supplier does not exist.',
            'currency_id.required' => 'Currency is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'branch_id.exists' => 'Selected branch does not exist.',
            
            // Purchase Invoice Information Messages
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date must be today or a future date.',
            'supplier_email.email' => 'Supplier email must be a valid email address.',
            'supplier_email.max' => 'Supplier email must not exceed 150 characters.',
            'supplier_mobile.max' => 'Supplier mobile must not exceed 20 characters.',
            'licensed_operator.max' => 'Licensed operator must not exceed 255 characters.',
            
            // Financial Information Messages
            'exchange_rate.numeric' => 'Exchange rate must be a number.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'currency_rate.numeric' => 'Currency rate must be a number.',
            'currency_rate.min' => 'Currency rate must be greater than 0.',
            'tax_rate_id.exists' => 'Selected tax rate does not exist.',
            
            // Discount Messages
            'discount_percentage.numeric' => 'Discount percentage must be a number.',
            'discount_percentage.min' => 'Discount percentage cannot be negative.',
            'discount_percentage.max' => 'Discount percentage cannot exceed 100%.',
            'discount_amount.numeric' => 'Discount amount must be a number.',
            'discount_amount.min' => 'Discount amount cannot be negative.',
            
            // Tax Messages
            'tax_percentage.numeric' => 'Tax percentage must be a number.',
            'tax_percentage.min' => 'Tax percentage cannot be negative.',
            'tax_percentage.max' => 'Tax percentage cannot exceed 100%.',
            'tax_amount.numeric' => 'Tax amount must be a number.',
            'tax_amount.min' => 'Tax amount cannot be negative.',
            
            // Items Messages
            'items.required' => 'At least one item is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item is required for each line.',
            'items.*.item_id.exists' => 'Selected item does not exist.',
            'items.*.unit_id.exists' => 'Selected unit does not exist.',
            'items.*.warehouse_id.exists' => 'Selected warehouse does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.numeric' => 'Quantity must be a number.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.numeric' => 'Unit price must be a number.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'items.*.discount_percentage.numeric' => 'Item discount percentage must be a number.',
            'items.*.discount_percentage.max' => 'Item discount percentage cannot exceed 100%.',
            'items.*.tax_rate.numeric' => 'Item tax rate must be a number.',
            'items.*.tax_rate.max' => 'Item tax rate cannot exceed 100%.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'supplier_id' => 'supplier',
            'currency_id' => 'currency',
            'employee_id' => 'employee',
            'branch_id' => 'branch',
            'due_date' => 'due date',
            'supplier_email' => 'supplier email',
            'supplier_mobile' => 'supplier mobile',
            'licensed_operator' => 'licensed operator',
            'exchange_rate' => 'exchange rate',
            'currency_rate' => 'currency rate',
            'tax_rate_id' => 'tax rate',
            'discount_percentage' => 'discount percentage',
            'discount_amount' => 'discount amount',
            'tax_percentage' => 'tax percentage',
            'tax_amount' => 'tax amount',
            'total_without_tax' => 'total without tax',
            'total_amount' => 'total amount',
            'grand_total' => 'grand total',
            'items.*.item_id' => 'item',
            'items.*.unit_id' => 'unit',
            'items.*.warehouse_id' => 'warehouse',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
            'items.*.discount_percentage' => 'discount percentage',
            'items.*.discount_amount' => 'discount amount',
            'items.*.tax_rate' => 'tax rate',
            'items.*.tax_amount' => 'tax amount',
            'items.*.total' => 'total',
        ];
    }
}
