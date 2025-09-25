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
            // Required fields
            'company_id'        => 'required|integer|exists:companies,id',
            'branch_id'         => 'nullable|integer|exists:branches,id',
            'currency_id'       => 'required|integer|exists:currencies,id',
            'employee_id'       => 'required|integer|exists:employees,id',
            'country_id'        => 'nullable|integer|exists:countries,id',
            'region_id'         => 'nullable|integer|exists:regions,id',
            'city_id'           => 'nullable|integer|exists:cities,id',

            // Customer identification
            'customer_number'   => [
                'nullable', // Will be auto-generated if not provided
                'string',
                'max:50',
                Rule::unique('customers', 'customer_number')->ignore($customerId),
            ],
            'customer_type'     => 'required|in:individual,business',
            'balance'           => 'nullable|numeric|min:0',

            // Company/Business information
            'company_name'      => 'required|string|max:255', // Required as per your request
            'first_name'        => 'nullable|string|max:100', // Optional as per your request
            'second_name'       => 'nullable|string|max:100', // Optional as per your request
            'contact_name'      => 'nullable|string|max:100',

            // Contact information
            'email'             => [
                'nullable', // Optional as per your request
                'email',
                'max:150',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone'             => 'nullable|string|max:50', // Optional as per your request
            'mobile'            => 'nullable|string|max:50', // Optional as per your request

            // Address information
            'address_one'       => 'nullable|string|max:255', // Optional as per your request
            'address_two'       => 'nullable|string|max:255', // Optional as per your request
            'postal_code'       => 'nullable|string|max:20', // Optional as per your request
            'licensed_operator' => 'nullable|string|max:255', // Optional as per your request

            // Tax and business information
            'tax_number'        => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customers', 'tax_number')->ignore($customerId),
            ],

            // Barcode information
            'code'              => [
                'nullable', // Optional as per your request
                'string',
                'max:50',
                Rule::unique('customers', 'code')->ignore($customerId),
            ],
            'barcode'           => 'nullable|string|max:100',
            'barcode_type'      => 'nullable|string|in:C128,EAN13,C39,UPCA,ITF',

            // Additional information
            'notes'             => 'nullable|string|max:1000',
            'status'            => 'nullable|in:active,inactive',
            'invoice_type'      => 'nullable|string|max:100',
            'category'          => 'nullable|string|max:100',
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
