<?php

namespace Modules\HumanResources\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PayrollDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $payrollRecordId = $this->route('payrollRecord')?->id;
        $payrollDataId = $this->route('payrollData')?->id;

        $rules = [
            // Employee relationship
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
                // Ensure employee is not already in this payroll record (except for updates)
                Rule::unique('payroll_data', 'employee_id')
                    ->where('payroll_record_id', $payrollRecordId)
                    ->ignore($payrollDataId)
            ],
            
            // Employee information (editable)
            'employee_number' => 'nullable|string|max:50',
            'employee_name' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|in:single,married',
            'job_title' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:100',
            
            // Salary information
            'basic_salary' => 'required|numeric|min:0|max:999999999.99',
            'income_tax' => 'nullable|numeric|min:0|max:999999999.99',
            'salary_for_payment' => 'nullable|numeric|min:0|max:999999999.99',
            'paid_in_cash' => 'nullable|numeric|min:0|max:999999999.99',
            
            // Additional fields
            'allowances' => 'nullable|numeric|min:0|max:999999999.99',
            'deductions' => 'nullable|numeric|min:0|max:999999999.99',
            'overtime_hours' => 'nullable|numeric|min:0|max:999.99',
            'overtime_rate' => 'nullable|numeric|min:0|max:9999.99',
            'overtime_amount' => 'nullable|numeric|min:0|max:999999999.99',
            
            // Status and notes
            'status' => 'nullable|string|in:active,inactive',
            'notes' => 'nullable|string|max:1000',
        ];

        // Additional validation for update requests
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // Make employee_id optional for updates if not changing
            $rules['employee_id'] = [
                'sometimes',
                'integer',
                'exists:employees,id',
                Rule::unique('payroll_data', 'employee_id')
                    ->where('payroll_record_id', $payrollRecordId)
                    ->ignore($payrollDataId)
            ];
            $rules['basic_salary'] = 'sometimes|numeric|min:0|max:999999999.99';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee is required.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'employee_id.unique' => 'This employee is already added to this payroll record.',
            'employee_number.max' => 'Employee number cannot exceed 50 characters.',
            'employee_name.max' => 'Employee name cannot exceed 255 characters.',
            'national_id.max' => 'National ID cannot exceed 50 characters.',
            'marital_status.in' => 'Marital status must be either single or married.',
            'job_title.max' => 'Job title cannot exceed 255 characters.',
            'duration.max' => 'Duration cannot exceed 100 characters.',
            'basic_salary.required' => 'Basic salary is required.',
            'basic_salary.numeric' => 'Basic salary must be a number.',
            'basic_salary.min' => 'Basic salary cannot be negative.',
            'basic_salary.max' => 'Basic salary is too large.',
            'income_tax.numeric' => 'Income tax must be a number.',
            'income_tax.min' => 'Income tax cannot be negative.',
            'income_tax.max' => 'Income tax is too large.',
            'salary_for_payment.numeric' => 'Salary for payment must be a number.',
            'salary_for_payment.min' => 'Salary for payment cannot be negative.',
            'salary_for_payment.max' => 'Salary for payment is too large.',
            'paid_in_cash.numeric' => 'Paid in cash must be a number.',
            'paid_in_cash.min' => 'Paid in cash cannot be negative.',
            'paid_in_cash.max' => 'Paid in cash is too large.',
            'allowances.numeric' => 'Allowances must be a number.',
            'allowances.min' => 'Allowances cannot be negative.',
            'allowances.max' => 'Allowances is too large.',
            'deductions.numeric' => 'Deductions must be a number.',
            'deductions.min' => 'Deductions cannot be negative.',
            'deductions.max' => 'Deductions is too large.',
            'overtime_hours.numeric' => 'Overtime hours must be a number.',
            'overtime_hours.min' => 'Overtime hours cannot be negative.',
            'overtime_hours.max' => 'Overtime hours is too large.',
            'overtime_rate.numeric' => 'Overtime rate must be a number.',
            'overtime_rate.min' => 'Overtime rate cannot be negative.',
            'overtime_rate.max' => 'Overtime rate is too large.',
            'overtime_amount.numeric' => 'Overtime amount must be a number.',
            'overtime_amount.min' => 'Overtime amount cannot be negative.',
            'overtime_amount.max' => 'Overtime amount is too large.',
            'status.in' => 'Status must be either active or inactive.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'employee',
            'employee_number' => 'employee number',
            'employee_name' => 'employee name',
            'national_id' => 'national ID',
            'marital_status' => 'marital status',
            'job_title' => 'job title',
            'duration' => 'duration',
            'basic_salary' => 'basic salary',
            'income_tax' => 'income tax',
            'salary_for_payment' => 'salary for payment',
            'paid_in_cash' => 'paid in cash',
            'allowances' => 'allowances',
            'deductions' => 'deductions',
            'overtime_hours' => 'overtime hours',
            'overtime_rate' => 'overtime rate',
            'overtime_amount' => 'overtime amount',
            'status' => 'status',
            'notes' => 'notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for optional fields
        $nullableFields = [
            'employee_number', 'employee_name', 'national_id', 'marital_status',
            'job_title', 'duration', 'income_tax', 'salary_for_payment', 
            'paid_in_cash', 'allowances', 'deductions', 'overtime_hours',
            'overtime_rate', 'overtime_amount', 'status', 'notes'
        ];

        $data = [];
        foreach ($nullableFields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $data[$field] = null;
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }

        // Set default values
        if (!$this->has('status') || $this->input('status') === null) {
            $this->merge(['status' => 'active']);
        }

        // Set default income tax to 0 if not provided
        if (!$this->has('income_tax') || $this->input('income_tax') === null) {
            $this->merge(['income_tax' => 0]);
        }

        // Set default allowances and deductions to 0 if not provided
        if (!$this->has('allowances') || $this->input('allowances') === null) {
            $this->merge(['allowances' => 0]);
        }

        if (!$this->has('deductions') || $this->input('deductions') === null) {
            $this->merge(['deductions' => 0]);
        }

        // Set default overtime values to 0 if not provided
        if (!$this->has('overtime_hours') || $this->input('overtime_hours') === null) {
            $this->merge(['overtime_hours' => 0]);
        }

        if (!$this->has('overtime_rate') || $this->input('overtime_rate') === null) {
            $this->merge(['overtime_rate' => 0]);
        }

        if (!$this->has('paid_in_cash') || $this->input('paid_in_cash') === null) {
            $this->merge(['paid_in_cash' => 0]);
        }
    }
}
