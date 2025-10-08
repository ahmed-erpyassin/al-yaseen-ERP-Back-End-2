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
            'logo' => 'nullable|max:1024',
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

    public function messages(): array
    {
        return [
            'title.required' => __('Company title is required.'),
            'commercial_registeration_number.required' => __('Commercial registration number is required.'),
            'email.required' => __('Company email is required.'),
            'email.email' => __('Please provide a valid email address.'),
            'currency_id.required' => __('Currency is required.'),
            'currency_id.exists' => __('Selected currency does not exist.'),
            'financial_year_id.required' => __('Financial year is required.'),
            'financial_year_id.exists' => __('Selected financial year does not exist.'),
            'industry_id.required' => __('Industry is required.'),
            'industry_id.exists' => __('Selected industry does not exist.'),
            'business_type_id.required' => __('Business type is required.'),
            'business_type_id.exists' => __('Selected business type does not exist.'),
            'country_id.required' => __('Country is required.'),
            'country_id.exists' => __('Selected country does not exist.'),
            'region_id.required' => __('Region is required.'),
            'region_id.exists' => __('Selected region does not exist.'),
            'city_id.required' => __('City is required.'),
            'city_id.exists' => __('Selected city does not exist.'),
            'income_tax_rate.numeric' => __('Income tax rate must be a number.'),
            'income_tax_rate.min' => __('Income tax rate must be at least :min%.', ['min' => 0]),
            'income_tax_rate.max' => __('Income tax rate may not be greater than :max%.', ['max' => 100]),
            'vat_rate.numeric' => __('VAT rate must be a number.'),
            'vat_rate.min' => __('VAT rate must be at least :min%.', ['min' => 0]),
            'vat_rate.max' => __('VAT rate may not be greater than :max%.', ['max' => 100]),
            'status.required' => __('Status is required.'),
            'status.in' => __('Status must be either active or inactive.'),
        ];
    }
}
