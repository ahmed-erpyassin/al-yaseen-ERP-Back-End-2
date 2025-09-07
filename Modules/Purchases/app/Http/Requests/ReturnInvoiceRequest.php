<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnInvoiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'currency_id' => 'nullable',
            'employee_id' => 'nullable',
            'customer_id' => 'required',
            'journal_id' => 'nullable',
            'journal_number' => 'required|integer',
            'cash_paid' => 'nullable|numeric|min:0',
            'checks_paid' => 'nullable|numeric|min:0',
            'allowed_discount' => 'nullable|numeric|min:0',
            'total_without_tax' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            'exchange_rate' => 'required|numeric|min:0',
            'total_foreign' => 'nullable|numeric|min:0',
            'total_local' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.total_foreign' => 'nullable|numeric|min:0',
            'items.*.total_local' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
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
