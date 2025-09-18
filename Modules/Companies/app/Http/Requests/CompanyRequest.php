<?php

namespace Modules\Companies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'commercial_registeration_number' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'email' => 'required|email|max:150',
            'landline' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',

            'currency_id' => 'required|exists:currencies,id',
            'financial_year_id' => 'required|exists:fiscal_years,id',
            'industry_id' => 'required|exists:industries,id',
            'business_type_id' => 'required|exists:business_types,id',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',

            'income_tax_rate' => 'nullable|numeric|min:0|max:100',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // تقدر تضيف شروط بناء على المستخدم
    }
}
