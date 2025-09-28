<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnInvoiceRequest extends FormRequest
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
            // Customer information
            'customer_id' => 'required|exists:customers,id',
            'customer_email' => 'nullable|email|max:150',
            
            // Return invoice details
            'due_date' => 'nullable|date|after_or_equal:today',
            'licensed_operator' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            
            // Employee and branch
            'employee_id' => 'nullable|exists:employees,id',
            'branch_id' => 'nullable|exists:branches,id',
            
            // Currency information
            'currency_id' => 'nullable|exists:currencies,id',
            
            // Tax settings
            'is_tax_inclusive' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            
            // Return invoice items
            'items' => 'nullable|array',
            'items.*.item_id' => 'required_with:items|exists:items,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'customer_email.email' => 'Customer email must be a valid email address.',
            'due_date.after_or_equal' => 'Due date must be today or a future date.',
            'licensed_operator.max' => 'Licensed operator name cannot exceed 255 characters.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'branch_id.exists' => 'Selected branch does not exist.',
            'tax_percentage.min' => 'Tax percentage cannot be negative.',
            'tax_percentage.max' => 'Tax percentage cannot exceed 100%.',
            
            // Item validation messages
            'items.array' => 'Items must be an array.',
            'items.*.item_id.required_with' => 'Item is required when items are provided.',
            'items.*.item_id.exists' => 'Selected item does not exist.',
            'items.*.unit_id.exists' => 'Selected unit does not exist.',
            'items.*.quantity.required_with' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.required_with' => 'Unit price is required for each item.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'items.*.tax_rate_id.exists' => 'Selected tax rate does not exist.',
            'items.*.notes.max' => 'Item notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'customer',
            'customer_email' => 'customer email',
            'due_date' => 'due date',
            'licensed_operator' => 'licensed operator',
            'employee_id' => 'employee',
            'branch_id' => 'branch',
            'currency_id' => 'currency',
            'is_tax_inclusive' => 'tax inclusive',
            'tax_percentage' => 'tax percentage',
            'items.*.item_id' => 'item',
            'items.*.unit_id' => 'unit',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
            'items.*.tax_rate_id' => 'tax rate',
            'items.*.notes' => 'item notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up numeric fields
        if ($this->has('tax_percentage')) {
            $this->merge([
                'tax_percentage' => (float) $this->tax_percentage
            ]);
        }

        // Clean up items data
        if ($this->has('items') && is_array($this->items)) {
            $items = [];
            foreach ($this->items as $item) {
                if (isset($item['quantity'])) {
                    $item['quantity'] = (float) $item['quantity'];
                }
                if (isset($item['unit_price'])) {
                    $item['unit_price'] = (float) $item['unit_price'];
                }
                $items[] = $item;
            }
            $this->merge(['items' => $items]);
        }
    }
}
