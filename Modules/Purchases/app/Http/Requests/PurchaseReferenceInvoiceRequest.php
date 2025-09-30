<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReferenceInvoiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'supplier_id' => 'required|exists:suppliers,id',
            'currency_id' => 'required|exists:currencies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'employee_id' => 'nullable|exists:employees,id',
            'customer_id' => 'nullable|exists:customers,id',
            'journal_id' => 'nullable|exists:journals,id',
            'journal_number' => 'nullable|integer',
            
            // Dates
            'due_date' => 'required|date|after_or_equal:today',
            
            // Supplier Information
            'supplier_email' => 'nullable|email',
            'licensed_operator' => 'nullable|string|max:255',
            
            // Financial Information - Original Fields
            'cash_paid' => 'nullable|numeric|min:0',
            'checks_paid' => 'nullable|numeric|min:0',
            'allowed_discount' => 'nullable|numeric|min:0',
            'total_without_tax' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            'exchange_rate' => 'nullable|numeric|min:0',
            'total_foreign' => 'nullable|numeric|min:0',
            'total_local' => 'nullable|numeric|min:0',
            
            // Financial Information - New Fields
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'is_tax_inclusive' => 'nullable|boolean',
            'is_tax_applied_to_currency' => 'nullable|boolean',
            'is_tax_applied_to_currency_rate' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            
            // Notes
            'notes' => 'nullable|string|max:1000',
            
            // Items - Purchase Reference Invoice Data
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier is required.',
            'supplier_id.exists' => 'Selected supplier does not exist.',
            'currency_id.required' => 'Currency is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be today or later.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item is required for each line.',
            'items.*.item_id.exists' => 'Selected item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.min' => 'Unit price must be 0 or greater.',
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
            'due_date' => 'due date',
            'supplier_email' => 'supplier email',
            'licensed_operator' => 'licensed operator',
            'tax_rate_id' => 'tax rate',
            'items.*.item_id' => 'item',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
            'items.*.notes' => 'notes',
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
