<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutgoingOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic order information
            'customer_id' => 'required|exists:customers,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'employee_id' => 'nullable|exists:users,id',
            'journal_id' => 'nullable|exists:journals,id',

            // Customer information (auto-filled but can be manually entered)
            'customer_number' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_mobile' => 'nullable|string|max:20',
            'licensed_operator' => 'nullable|string|max:255',

            // Date and time fields (auto-filled)
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i:s',
            'due_date' => 'nullable|date|after_or_equal:date',

            // Financial fields
            'exchange_rate' => 'nullable|numeric|min:0',
            'cash_paid' => 'nullable|numeric|min:0',
            'checks_paid' => 'nullable|numeric|min:0',
            'allowed_discount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_without_tax' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'is_tax_inclusive' => 'nullable|boolean',
            'total_amount' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            'total_foreign' => 'nullable|numeric|min:0',
            'total_local' => 'nullable|numeric|min:0',

            // Notes
            'notes' => 'nullable|string|max:1000',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_number' => 'nullable|string|max:50',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.total_without_tax' => 'nullable|numeric|min:0',
            'items.*.total_foreign' => 'nullable|numeric|min:0',
            'items.*.total_local' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0'
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item is required for each line.',
            'items.*.item_id.exists' => 'Selected item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.min' => 'Unit price must be 0 or greater.',
            'discount_percentage.max' => 'Discount percentage cannot exceed 100%.',
            'tax_percentage.max' => 'Tax percentage cannot exceed 100%.',
            'items.*.discount_percentage.max' => 'Item discount percentage cannot exceed 100%.',
            'items.*.tax_rate.max' => 'Item tax rate cannot exceed 100%.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
