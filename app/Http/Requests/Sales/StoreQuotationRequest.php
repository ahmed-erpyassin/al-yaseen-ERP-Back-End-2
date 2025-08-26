<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
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
            'company_id'         => 'required|integer|exists:companies,id',
            'user_id'            => 'required|integer|exists:users,id',
            'quotation_number'   => 'required|string|unique:quotations,quotation_number',
            'quotation_date'     => 'required|date',
            'expiry_date'        => 'required|date',
            'customer_name'      => 'required|string',
            'customer_phone'     => 'required|string',
            'license_number'     => 'required|string',
            'customer_email'     => 'required|email',
            'currency_id'        => 'required|integer|exists:currencies,id',
            'exchange_rate'      => 'required|numeric',
            'allowed_discount'   => 'required|numeric',
            'subtotal_without_tax'=> 'required|numeric',
            'precentage'         => 'required|numeric',
            'vat'                => 'required|numeric',
            'total'              => 'required|numeric',
            'notes'              => 'required|string',
            'items'              => 'required|array|min:1',
            'items.*.number'     => 'required|string',
            'items.*.item_name'  => 'required|string',
            'items.*.unit'       => 'required|string',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total'      => 'required|numeric|min:0',
        ];
    }
}
