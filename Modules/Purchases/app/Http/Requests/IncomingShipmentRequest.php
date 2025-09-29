<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomingShipmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'branch_id' => 'nullable|exists:branches,id',
            'currency_id' => 'required|exists:currencies,id',
            'employee_id' => 'nullable|exists:employees,id',
            'customer_id' => 'required|exists:customers,id',
            'journal_id' => 'nullable|exists:journals,id',
            'journal_number' => 'required|integer|min:1',

            // Auto-generated fields (optional in request as they're generated automatically)
            'ledger_code' => 'nullable|string|max:50',
            'ledger_number' => 'nullable|integer',
            'ledger_invoice_count' => 'nullable|integer',
            'invoice_number' => 'nullable|string|max:50',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i:s',

            // Required manual fields
            'due_date' => 'required|date|after_or_equal:today',
            'customer_email' => 'nullable|email|max:150',
            'customer_mobile' => 'nullable|string|max:20',
            'licensed_operator' => 'nullable|string|max:255',

            // Financial fields
            'cash_paid' => 'nullable|numeric|min:0',
            'checks_paid' => 'nullable|numeric|min:0',
            'allowed_discount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_without_tax' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'total_foreign' => 'nullable|numeric|min:0',
            'total_local' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.serial_number' => 'nullable|integer',
            'items.*.shipment_number' => 'nullable|string|max:50',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_number' => 'nullable|string|max:50',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.unit_name' => 'nullable|string|max:50',
            'items.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'items.*.warehouse_number' => 'nullable|string|max:50',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.net_unit_price' => 'nullable|numeric|min:0',
            'items.*.line_total_before_tax' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.line_total_after_tax' => 'nullable|numeric|min:0',
            'items.*.total_foreign' => 'nullable|numeric|min:0',
            'items.*.total_local' => 'nullable|numeric|min:0',
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
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'currency_id.required' => 'Currency is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be today or later.',
            'customer_email.email' => 'Customer email must be a valid email address.',
            'exchange_rate.required' => 'Exchange rate is required.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item is required for each line.',
            'items.*.item_id.exists' => 'Selected item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.min' => 'Unit price must be 0 or greater.',
            'items.*.warehouse_id.exists' => 'Selected warehouse does not exist.',
            'items.*.unit_id.exists' => 'Selected unit does not exist.',
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
