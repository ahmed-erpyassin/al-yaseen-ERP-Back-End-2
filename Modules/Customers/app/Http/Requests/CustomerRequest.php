<?php

namespace Modules\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the customer ID from the route parameter
        $customerId = $this->route('id');

        return  [
             'company_id' => ['required', 'integer', 'exists:companies,id'],
            'branch_id' => ['nullable'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'second_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customerId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'address_one' => ['nullable', 'string', 'max:255'],
            'address_two' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            // 'company_id.required' => __('The company field is required.'),
            // 'company_id.integer' => __('The company must be a valid integer.'),
            // 'company_id.exists' => __('The selected company does not exist.'),
            // 'branch_id.integer' => __('The branch must be a valid integer.'),
            // 'branch_id.exists' => __('The selected branch does not exist.'),
            'currency_id.required' => __('The currency field is required.'),
            'currency_id.integer' => __('The currency must be a valid integer.'),
            'currency_id.exists' => __('The selected currency does not exist.'),
            'employee_id.integer' => __('The employee must be a valid integer.'),
            'employee_id.exists' => __('The selected employee does not exist.'),
            'country_id.integer' => __('The country must be a valid integer.'),
            'country_id.exists' => __('The selected country does not exist.'),
            'region_id.integer' => __('The region must be a valid integer.'),
            'region_id.exists' => __('The selected region does not exist.'),
            'city_id.integer' => __('The city must be a valid integer.'),
            'city_id.exists' => __('The selected city does not exist.'),
            'first_name.required' => __('The first name field is required.'),
            'first_name.string' => __('The first name must be a string.'),
            'first_name.max' => __('The first name may not be greater than 255 characters.'),
            'second_name.string' => __('The second name must be a string.'),
            'second_name.max' => __('The second name may not be greater than 255 characters.'),
            'contact_name.string' => __('The contact name must be a string.'),
            'contact_name.max' => __('The contact name may not be greater than 255 characters.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.max' => __('The email may not be greater than 255 characters.'),
            'email.unique' => __('The email has already been taken.'),
            'phone.string' => __('The phone must be a string.'),
            'phone.max' => __('The phone may not be greater than 50 characters.'),
            'mobile.string' => __('The mobile must be a string.'),
            'mobile.max' => __('The mobile may not be greater than 50 characters.'),
            'address_one.string' => __('The address one must be a string.'),
            'address_one.max' => __('The address one may not be greater than 255 characters.'),
            'address_two.string' => __('The address two must be a string.'),
            'address_two.max' => __('The address two may not be greater than 255 characters.'),
            'postal_code.string' => __('The postal code must be a string.'),
            'postal_code.max' => __('The postal code may not be greater than 20 characters.'),
            'tax_number.string' => __('The tax number must be a string.'),
            'tax_number.max' => __('The tax number may not be greater than 100 characters.'),
            'notes.string' => __('The notes must be a string.'),
            'status.required' => __('The status field is required.'),
            'status.in' => __('The selected status is invalid. Allowed values are active or inactive.'),
        ];
    }
}
