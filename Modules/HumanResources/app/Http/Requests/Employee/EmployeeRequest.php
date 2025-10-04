<?php

namespace Modules\HumanResources\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EmployeeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee') ? $this->route('employee')->id : null;
        
        return [
            // Core Information
            'company_id'          => 'required|integer|exists:companies,id',
            'user_id'             => 'nullable|integer|exists:users,id',
            'branch_id'           => 'required|integer|exists:branches,id',
            'fiscal_year_id'      => 'nullable|integer|exists:fiscal_years,id',
            'department_id'       => 'required|integer|exists:departments,id',
            'job_title_id'        => 'required|integer|exists:job_titles,id',
            'category'            => 'nullable|string|max:100',
            'manager_id'          => 'nullable|integer|exists:employees,id',

            // Employee Identification
            'employee_number'     => 'required|string|unique:employees,employee_number,' . $employeeId,
            'code'                => 'nullable|string|unique:employees,code,' . $employeeId,

            // Personal Information
            'nickname'            => 'nullable|string|max:50',
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'nullable|string|max:100',
            'second_name'         => 'nullable|string|max:100',
            'third_name'          => 'nullable|string|max:100',
            'phone1'              => 'nullable|string|max:20',
            'phone2'              => 'nullable|string|max:20',
            'email'               => 'required|email|max:255|unique:employees,email,' . $employeeId,

            // Personal Details
            'birth_date'          => 'required|date|before:today',
            'address'             => 'required|string|max:255',
            'national_id'         => 'required|string|max:100',
            'id_number'           => 'required|string|max:50',
            'gender'              => 'required|in:male,female',

            // Family Information
            'wives_count'         => 'nullable|integer|min:0|max:10',
            'children_count'      => 'nullable|integer|min:0|max:50',
            'dependents_count'    => 'nullable|string|max:50',
            'students_count'      => 'nullable|integer|min:0|max:50',

            // Employment Type
            'car_number'          => 'nullable|string|max:50',
            'is_driver'           => 'boolean',
            'is_sales'            => 'boolean',

            // Job Information
            'hire_date'           => 'required|date|before_or_equal:today',
            'employee_code'       => 'nullable|string|max:50',
            'employee_identifier' => 'nullable|string|max:50',
            'job_address'         => 'nullable|string|max:255',

            // Financial Information
            'salary'              => 'required|numeric|min:0',
            'billing_rate'        => 'nullable|numeric|min:0',
            'monthly_discount'    => 'nullable|numeric|min:0',
            'balance'             => 'nullable|numeric',

            // Currency Information
            'currency_id'         => 'required|integer|exists:currencies,id',
            'currency_rate'       => 'nullable|numeric|min:0.0001',

            // Additional Information
            'notes'               => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_number.unique' => 'This employee number is already taken.',
            'email.unique' => 'This email address is already registered.',
            'birth_date.before' => 'Birth date must be before today.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'wives_count.max' => 'Maximum number of wives cannot exceed 10.',
            'children_count.max' => 'Maximum number of children cannot exceed 50.',
            'students_count.max' => 'Maximum number of students cannot exceed 50.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default company_id if not provided
        if (!$this->has('company_id') && Auth::check()) {
            $this->merge([
                'company_id' => Auth::user()->company->id ?? null,
            ]);
        }

        // Set default values for boolean fields
        $this->merge([
            'is_driver' => $this->boolean('is_driver'),
            'is_sales' => $this->boolean('is_sales'),
        ]);

        // Set default counts to 0 if not provided
        $this->merge([
            'wives_count' => $this->input('wives_count', 0),
            'children_count' => $this->input('children_count', 0),
            'students_count' => $this->input('students_count', 0),
        ]);

        // Clean and format phone numbers
        if ($this->has('phone1')) {
            $this->merge(['phone1' => $this->cleanPhoneNumber($this->input('phone1'))]);
        }

        if ($this->has('phone2')) {
            $this->merge(['phone2' => $this->cleanPhoneNumber($this->input('phone2'))]);
        }

        // Format names (trim and proper case)
        $nameFields = ['first_name', 'last_name', 'second_name', 'third_name', 'nickname'];
        foreach ($nameFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => $this->formatName($this->input($field))]);
            }
        }
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber(?string $phone): ?string
    {
        if (!$phone) return null;

        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        return $cleaned ?: null;
    }

    /**
     * Format name (trim and proper case)
     */
    private function formatName(?string $name): ?string
    {
        if (!$name) return null;

        return trim($name);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
