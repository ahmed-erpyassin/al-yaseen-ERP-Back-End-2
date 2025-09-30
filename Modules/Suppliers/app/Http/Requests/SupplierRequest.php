<?php

namespace Modules\Suppliers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Foreign Key Relations (made optional as they will be set automatically or can be null)
            'user_id'      => 'nullable|exists:users,id',
            'company_id'   => 'nullable|exists:companies,id',
            'branch_id'    => 'nullable|exists:branches,id',
            'currency_id'  => 'nullable|exists:currencies,id',
            'employee_id'  => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'project_id'   => 'nullable|exists:projects,id',
            'donor_id'     => 'nullable|exists:donors,id',
            'sales_representative_id' => 'nullable|exists:sales_representatives,id',
            'country_id'   => 'nullable|exists:countries,id',
            'region_id'    => 'nullable|exists:regions,id',
            'city_id'      => 'nullable|exists:cities,id',
            'barcode_type_id' => 'nullable|exists:barcode_types,id',

            // Supplier Type and Number
            'supplier_type' => 'required|in:individual,business',
            'supplier_number' => 'nullable|string|max:50|unique:suppliers,supplier_number,' . $this->route('supplier'),
            'supplier_name_ar' => 'required|string|max:255', // Company Name/Trade Name (required)
            'supplier_name_en' => 'nullable|string|max:255',
            'supplier_code' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',

            // Personal Names (can be empty)
            'first_name'   => 'nullable|string|max:100',
            'second_name'  => 'nullable|string|max:100',
            'contact_name' => 'nullable|string|max:100',

            // Contact Information (can be empty)
            'email'        => 'nullable|email|max:150',
            'phone'        => 'nullable|string|max:50',
            'mobile'       => 'nullable|string|max:50',
            'website'      => 'nullable|url|max:255',

            // Address Information (can be empty)
            'address_one'  => 'nullable|string|max:255', // Street Address 1
            'address_two'  => 'nullable|string|max:255', // Street Address 2
            'address'      => 'nullable|string|max:500', // Full address
            'postal_code'  => 'nullable|string|max:20',

            // Licensed Operator (entered manually)
            'licensed_operator' => 'nullable|string|max:255',

            // Account Data
            'code_number'  => 'nullable|string|max:50', // Code Number (entered manually)

            // Financial Information
            'tax_number'   => 'nullable|string|max:50',
            'commercial_register' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0',
            'balance'      => 'nullable|numeric',
            'last_transaction_date' => 'nullable|date',

            // Classification
            'classification' => 'required|in:major,medium,minor',
            'custom_classification' => 'nullable|string|max:100',

            // Additional Information
            'notes'        => 'nullable|string|max:1000',

            // Audit Fields
            'created_by'   => 'nullable|exists:users,id',
            'updated_by'   => 'nullable|exists:users,id',
            'deleted_by'   => 'nullable|exists:users,id',

            // Status
            'status'       => 'required|in:active,inactive',
            'active'       => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'supplier_type.required' => 'Supplier type is required.',
            'supplier_type.in' => 'Supplier type must be either Individual or Business.',
            'supplier_name_ar.required' => 'Company Name/Trade Name is required.',
            'supplier_name_ar.max' => 'Company Name cannot exceed 255 characters.',
            'supplier_number.unique' => 'This supplier number is already taken.',
            'email.email' => 'Please enter a valid email address.',
            'website.url' => 'Please enter a valid website URL.',
            'credit_limit.numeric' => 'Credit limit must be a valid number.',
            'credit_limit.min' => 'Credit limit cannot be negative.',
            'payment_terms.integer' => 'Payment terms must be a whole number.',
            'payment_terms.min' => 'Payment terms cannot be negative.',
            'balance.numeric' => 'Balance must be a valid number.',
            'last_transaction_date.date' => 'Last transaction date must be a valid date.',
            'classification.required' => 'Classification is required.',
            'classification.in' => 'Classification must be Major, Medium, or Minor Suppliers.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either Active or Inactive.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'supplier_type' => 'supplier type',
            'supplier_number' => 'supplier number',
            'supplier_name_ar' => 'company name',
            'supplier_name_en' => 'company name (English)',
            'supplier_code' => 'supplier code',
            'contact_person' => 'contact person',
            'first_name' => 'first name',
            'second_name' => 'second name',
            'contact_name' => 'contact name',
            'email' => 'email address',
            'phone' => 'phone number',
            'mobile' => 'mobile number',
            'website' => 'website',
            'address_one' => 'street address 1',
            'address_two' => 'street address 2',
            'postal_code' => 'postal code',
            'licensed_operator' => 'licensed operator',
            'code_number' => 'code number',
            'tax_number' => 'tax number',
            'commercial_register' => 'commercial register',
            'credit_limit' => 'credit limit',
            'payment_terms' => 'payment terms',
            'balance' => 'balance',
            'last_transaction_date' => 'last transaction date',
            'classification' => 'classification',
            'custom_classification' => 'custom classification',
            'notes' => 'notes',
            'status' => 'status',
            'branch_id' => 'branch',
            'currency_id' => 'currency',
            'department_id' => 'department',
            'project_id' => 'project',
            'donor_id' => 'donor',
            'sales_representative_id' => 'sales representative',
            'country_id' => 'country',
            'region_id' => 'region',
            'city_id' => 'city',
            'barcode_type_id' => 'barcode type',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */

    public function authorize(): bool
    {
        return true;
    }
/*
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
        */
}
