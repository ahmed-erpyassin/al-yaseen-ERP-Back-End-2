<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'    => 'required|exists:companies,id',
            'user_id'       => 'required|exists:users,id',
            'notbook'       => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255|unique:sales_invoices,invoice_number',
            'invoice_date'  => 'nullable|date',
            'invoice_time'  => 'nullable|date_format:H:i',
            'due_date'      => 'nullable|date',
            'client_id'     => 'required|exists:clients,id',

            'currency_id'   => 'required|exists:currencies,id',
            'currency_rate' => 'required|numeric|min:0',
            'include_tax' => 'required|boolean',

            'notes'         => 'nullable|string',

            'cash_paid'     => 'nullable|numeric|min:0',
            'card_paid'     => 'nullable|numeric|min:0',
            'card_cash_currency' => 'required|exists:currencies,id',
            'allowed_discount'   => 'nullable|numeric|min:0',
            'subtotal_without_tax' => 'nullable|numeric|min:0',
            'vat'           => 'nullable|numeric|min:0',
            'total_amount'  => 'nullable|numeric|min:0',
            'advance_paid'  => 'nullable|numeric|min:0',

            'items'                 => 'required|array|min:1',
            'items.*.item_code'     => 'required|string|max:255',
            'items.*.item_name'     => 'required|string|max:255',
            'items.*.unit'          => 'nullable|string|max:50',
            'items.*.quantity'      => 'required|numeric|min:0',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.total'         => 'required|numeric|min:0',
        ];
    }
}
