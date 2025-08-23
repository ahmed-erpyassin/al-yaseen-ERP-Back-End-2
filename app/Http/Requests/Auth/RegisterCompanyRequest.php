<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCompanyRequest extends FormRequest
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
            'company_name'                      => 'required|string',
            'commercial_registration_number'    => 'required|unique:companies',
            'company_type'                      => 'required|integer',
            'work_type'                         => 'required|integer',
            'company_address'                   => 'required|string',
            'company_logo'                      => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email'                             => 'required|email',
            'country_code'                      => 'required|string',
            'phone'                             => 'required|string',
            'allow_emails'                     => 'required|boolean',
            'income_tax_rate'                   => 'nullable|numeric',
            'vat_rate'                          => 'nullable|numeric',
            'fiscal_year'                       => 'required|numeric',
            'from'                              => 'required|date',
            'to'                                => 'required|date',
            'currency_id'                       => 'required|integer'
        ];
    }
}
