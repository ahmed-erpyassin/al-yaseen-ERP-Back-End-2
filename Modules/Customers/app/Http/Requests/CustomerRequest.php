<?php

namespace Modules\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $customerId = $this->route('customer'); // or however you get the current customer ID

        return [
            'company_id'        => 'required|integer',
            'branch_id'         => 'required|integer',
            'currency_id'       => 'required|integer',
            'employee_id'       => 'required|integer',
            'country_id'        => 'required|integer',
            'region_id'         => 'required|integer',
            'city_id'           => 'required|integer',
            'customer_number'   => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'customer_number')->ignore($customerId),
            ],
            'company_name'      => 'required|string|max:255',
            'first_name'        => 'required|string|max:100',
            'second_name'       => 'required|string|max:100',
            'contact_name'      => 'required|string|max:100',
            'email'             => [
                'required',
                'email',
                'max:150',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone'             => 'required|string|max:50',
            'mobile'            => 'required|string|max:50',
            'address_one'       => 'required|string|max:255',
            'address_two'       => 'required|string|max:255',
            'postal_code'       => 'required|string|max:20',
            'licensed_operator' => 'required|string|max:255',
            'tax_number'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'tax_number')->ignore($customerId),
            ],
            'notes'             => 'nullable|string|max:500',
            'status'            => 'required|in:active,inactive',
            'code'              => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'code')->ignore($customerId),
            ],
            'invoice_type'      => 'required|string|max:100',
            'category'          => 'required|string|max:100',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Company selection is required.',
            'company_id.exists' => 'Selected company does not exist.',
            'branch_id.required' => 'Branch selection is required.',
            'branch_id.exists' => 'Selected branch does not exist.',
            'currency_id.required' => 'Currency selection is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'employee_id.required' => 'Employee selection is required.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'country_id.required' => 'Country selection is required.',
            'country_id.exists' => 'Selected country does not exist.',
            'region_id.required' => 'Region selection is required.',
            'region_id.exists' => 'Selected region does not exist.',
            'city_id.required' => 'City selection is required.',
            'city_id.exists' => 'Selected city does not exist.',
            'customer_number.required' => 'Customer number is required.',
            'customer_number.unique' => 'This customer number already exists.',
            'company_name.required' => 'Company name is required.',
            'first_name.required' => 'First name is required.',
            'second_name.required' => 'Second name is required.',
            'contact_name.required' => 'Contact name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'Phone number is required.',
            'mobile.required' => 'Mobile number is required.',
            'address_one.required' => 'Address line 1 is required.',
            'address_two.required' => 'Address line 2 is required.',
            'postal_code.required' => 'Postal code is required.',
            'licensed_operator.required' => 'Licensed operator is required.',
            'tax_number.required' => 'Tax number is required.',
            'tax_number.unique' => 'This tax number already exists.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
            'code.required' => 'Code is required.',
            'code.unique' => 'This code already exists.',
            'invoice_type.required' => 'Invoice type is required.',
            'category.required' => 'Category is required.',
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
