<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',

            // Notes
            'notes' => 'nullable|string|max:1000',

            // Items - Combined Rules
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|integer|exists:items,id',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.total_foreign' => 'nullable|numeric|min:0',
            'items.*.total_local' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
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
