<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic invoice information
            'due_date' => 'required|date|after:today',
            'customer_id' => 'required|exists:customers,id',
            'customer_email' => 'nullable|email|max:150',
            'licensed_operator' => 'nullable|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'required|numeric|min:0',
            'is_tax_inclusive' => 'boolean',
            'notes' => 'nullable|string|max:1000',

            // Financial fields
            'cash_paid' => 'nullable|numeric|min:0',
            'checks_paid' => 'nullable|numeric|min:0',
            'allowed_discount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'due_date.required' => 'Due date is required.',
            'due_date.after' => 'Due date must be after today.',
            'customer_id.required' => 'Customer selection is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'customer_email.email' => 'Please provide a valid email address.',
            'currency_id.required' => 'Currency selection is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'exchange_rate.required' => 'Exchange rate is required.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item selection is required for all items.',
            'items.*.item_id.exists' => 'Selected item does not exist.',
            'items.*.unit_id.required' => 'Unit selection is required for all items.',
            'items.*.unit_id.exists' => 'Selected unit does not exist.',
            'items.*.quantity.required' => 'Quantity is required for all items.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.required' => 'Unit price is required for all items.',
            'items.*.unit_price.min' => 'Unit price must be greater than or equal to 0.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default values for auto-generated fields
        $this->merge([
            'type' => 'invoice',
            'status' => 'draft',
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
        ]);
    }
}
