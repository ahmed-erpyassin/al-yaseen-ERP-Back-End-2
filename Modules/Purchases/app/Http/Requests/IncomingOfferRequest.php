<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomingOfferRequest extends FormRequest
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
            'supplier_id' => 'required|exists:suppliers,id',

            // Quotation Information
            'quotation_number' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:50',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i:s',
            'due_date' => 'nullable|date|after_or_equal:date',

            // Customer Information (for incoming quotations)
            'customer_id' => 'nullable|exists:customers,id',
            'customer_number' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_mobile' => 'nullable|string|max:20',

            // Supplier Information
            'supplier_name' => 'nullable|string|max:255',
            'licensed_operator' => 'nullable|string|max:255',

            // Ledger System
            'journal_id' => 'nullable|integer',
            'journal_number' => 'nullable|integer',
            'ledger_code' => 'nullable|string|max:50',
            'ledger_number' => 'nullable|integer',
            'ledger_invoice_count' => 'nullable|integer',

            // Financial Information
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

            // Currency Information
            'exchange_rate' => 'required|numeric|min:0',
            'currency_rate' => 'nullable|numeric|min:0',
            'currency_rate_with_tax' => 'nullable|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'is_tax_applied_to_currency' => 'nullable|boolean',
            'total_foreign' => 'nullable|numeric|min:0',
            'total_local' => 'nullable|numeric|min:0',

            // Additional Information
            'notes' => 'nullable|string',

            // Items
            'items' => 'required|array|min:1',
            'items.*.serial_number' => 'nullable|integer',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_number' => 'nullable|string|max:50',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.unit_name' => 'nullable|string|max:50',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.total_foreign' => 'nullable|numeric|min:0',
            'items.*.total_local' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
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
