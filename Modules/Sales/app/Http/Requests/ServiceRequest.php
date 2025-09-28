<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Customer information
            'customer_id' => 'required|exists:customers,id',
            'customer_email' => 'nullable|email|max:150',

            // Service details
            'due_date' => 'nullable|date|after:today',
            'licensed_operator' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',

            // Optional fields
            'employee_id' => 'nullable|exists:employees,id',
            'branch_id' => 'nullable|exists:branches,id',
            'currency_id' => 'nullable|exists:currencies,id',

            // Tax settings
            'is_tax_inclusive' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',

            // Service items validation
            'items' => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.account_number' => 'nullable|string|max:50',
            'items.*.account_name' => 'nullable|string|max:150',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.unit_name' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.apply_tax' => 'nullable|boolean',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'items.*.notes' => 'nullable|string|max:500',
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
