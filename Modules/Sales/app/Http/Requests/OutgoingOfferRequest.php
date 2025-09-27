<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutgoingOfferRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'branch_id'             => 'required|integer|exists:branches,id',
            'currency_id'           => 'required|integer|exists:currencies,id',
            'employee_id'           => 'nullable|integer|exists:employees,id',
            'customer_id'           => 'required|integer|exists:customers,id',
            'journal_id'            => 'nullable|integer|exists:journals,id',

            // Auto-generated fields (optional in request)
            'code'                  => 'nullable|string|max:255', // Book code (auto-generated)
            'journal_number'        => 'nullable|integer|min:1', // Auto-generated from journal
            'invoice_number'        => 'nullable|string|max:255', // Auto-generated sequential
            'date'                  => 'nullable|date', // Auto-generated (today)
            'time'                  => 'nullable|date_format:H:i:s', // Auto-generated (now)
            'due_date'              => 'required|date|after_or_equal:today',

            // Customer contact fields
            'email'                 => 'nullable|email|max:255',
            'licensed_operator'     => 'nullable|string|max:255',
            'cash_paid'             => 'nullable|numeric|min:0|max:999999999.99',
            'checks_paid'           => 'nullable|numeric|min:0|max:999999999.99',
            'allowed_discount'      => 'nullable|numeric|min:0|max:999999999.99',
            'total_without_tax'     => 'nullable|numeric|min:0|max:999999999.99',
            'tax_percentage'        => 'nullable|numeric|min:0|max:100',
            'tax_amount'            => 'nullable|numeric|min:0|max:999999999.99',
            'total_amount'          => 'nullable|numeric|min:0|max:999999999.99',
            'remaining_balance'     => 'nullable|numeric|min:0|max:999999999.99',
            'exchange_rate'         => 'required|numeric|min:0.0001|max:999999.9999',
            'total_foreign'         => 'nullable|numeric|min:0|max:999999999.9999',
            'total_local'           => 'nullable|numeric|min:0|max:999999999.9999',
            'notes'                 => 'nullable|string|max:1000',
            'items'                 => 'required|array|min:1',
            'items.*.item_id'       => 'required|integer|exists:items,id',
            'items.*.unit_id'       => 'nullable|integer|exists:units,id',
            'items.*.item_number'   => 'nullable|string|max:255', // Auto-populated from item
            'items.*.item_name'     => 'nullable|string|max:255', // Auto-populated from item
            'items.*.description'   => 'nullable|string|max:500',
            'items.*.quantity'      => 'required|numeric|min:0.0001|max:999999.9999',
            'items.*.unit_price'    => 'required|numeric|min:0|max:999999.9999',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate'      => 'nullable|numeric|min:0|max:100',
            'items.*.total_foreign' => 'nullable|numeric|min:0|max:999999999.9999',
            'items.*.total_local'   => 'nullable|numeric|min:0|max:999999999.9999',
            'items.*.total'         => 'nullable|numeric|min:0|max:999999999.9999',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Branch is required',
            'currency_id.required' => 'Currency is required',
            'employee_id.required' => 'Employee is required',
            'customer_id.required' => 'Customer is required',
            'journal_number.required' => 'Journal number is required',
            'journal_number.min' => 'Journal number must be at least 1',
            'invoice_number.required' => 'Invoice number is required',
            'time.required' => 'Time is required',
            'time.date_format' => 'Time must be in HH:MM:SS format',
            'due_date.required' => 'Due date is required',
            'due_date.after_or_equal' => 'Due date must be today or later',
            'exchange_rate.required' => 'Exchange rate is required',
            'exchange_rate.min' => 'Exchange rate must be greater than 0',
            'items.required' => 'At least one item is required',
            'items.min' => 'At least one item is required',
            'items.*.item_id.required' => 'Item is required for each line',
            'items.*.description.required' => 'Description is required for each item',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be greater than 0',
            'items.*.unit_price.required' => 'Unit price is required for each item',
            'items.*.unit_price.min' => 'Unit price must be 0 or greater',
            'items.*.discount_rate.required' => 'Discount rate is required for each item',
            'items.*.discount_rate.max' => 'Discount rate cannot exceed 100%',
            'items.*.tax_rate.required' => 'Tax rate is required for each item',
            'items.*.tax_rate.max' => 'Tax rate cannot exceed 100%',
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
