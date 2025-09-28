<?php

namespace Modules\Suppliers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'user_id' => 'nullable|exists:users,id',
            // 'company_id' => 'required|exists:companies,id',
            // 'branch_id' => 'nullable|exists:branches,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'employee_id' => 'nullable|exists:users,id',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'city_id' => 'nullable|exists:cities,id',
            'first_name' => 'required|string|max:255',
            'second_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address_one' => 'nullable|string|max:255',
            'address_two' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            // 'company_id.required' => __('The company field is required.'),
            // 'company_id.exists' => __('The selected company is invalid.'),
            'first_name.required' => __('The first name field is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'status.required' => __('The status field is required.'),
            'status.in' => __('The selected status is invalid. Allowed values are active or inactive.'),
            'currency_id.exists' => __('The selected currency is invalid.'),
            'employee_id.exists' => __('The selected employee is invalid.'),
            'country_id.exists' => __('The selected country is invalid.'),
            'region_id.exists' => __('The selected region is invalid.'),
            'city_id.exists' => __('The selected city is invalid.'),
        ];
    }
}
