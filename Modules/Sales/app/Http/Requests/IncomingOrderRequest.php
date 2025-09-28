<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomingOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'branch_id' => 'required|integer|exists:branches,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'customer_id' => 'required|integer|exists:customers,id',
            'journal_id' => 'nullable|integer',
            'journal_number' => 'required|integer|min:1',

            // Auto-generated fields (optional in request)
            'book_code' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i:s',
            'due_date' => 'required|date|after_or_equal:today',

            // Customer Information
            'customer_email' => 'nullable|email|max:150',
            'licensed_operator' => 'nullable|string|max:255',

            // Financial Information
            'cash_paid' => 'nullable|numeric|min:0|max:999999999.99',
            'checks_paid' => 'nullable|numeric|min:0|max:999999999.99',
            'allowed_discount' => 'nullable|numeric|min:0|max:999999999.99',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'total_without_tax' => 'nullable|numeric|min:0|max:999999999.99',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0|max:999999999.99',
            'total_amount' => 'nullable|numeric|min:0|max:999999999.99',
            'remaining_balance' => 'nullable|numeric|min:0|max:999999999.99',
            'exchange_rate' => 'required|numeric|min:0.0001|max:999999.9999',
            'total_foreign' => 'nullable|numeric|min:0|max:999999999.9999',
            'total_local' => 'nullable|numeric|min:0|max:999999999.9999',
            'is_tax_inclusive' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.item_number' => 'nullable|string|max:100',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.unit_name' => 'nullable|string|max:100',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.0001|max:999999.9999',
            'items.*.unit_price' => 'required|numeric|min:0|max:999999999.99',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0|max:999999999.99',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.total_foreign' => 'nullable|numeric|min:0|max:999999999.9999',
            'items.*.total_local' => 'nullable|numeric|min:0|max:999999999.9999',
            'items.*.total' => 'nullable|numeric|min:0|max:999999999.99'
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
