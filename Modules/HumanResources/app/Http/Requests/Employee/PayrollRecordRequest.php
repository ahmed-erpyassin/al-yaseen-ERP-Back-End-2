<?php

namespace Modules\HumanResources\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PayrollRecordRequest extends FormRequest
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
        $rules = [
            // Core relationships
            'company_id' => 'required|integer|exists:companies,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'fiscal_year_id' => 'required|integer|exists:fiscal_years,id',
            
            // Payroll specific fields
            'payroll_number' => 'nullable|string|max:50|unique:payroll_records,payroll_number,' . $this->route('payrollRecord')?->id,
            'date' => 'required|date',
            'second_date' => 'nullable|date|after_or_equal:date',
            
            // Currency information
            'currency_id' => 'required|integer|exists:currencies,id',
            'currency_rate' => 'nullable|numeric|min:0.0001|max:999999.9999',
            
            // Account information
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'account_id' => 'nullable|integer|exists:accounts,id',
            
            // Payment details
            'payment_account' => 'nullable|string|max:255',
            'salaries_wages_period' => 'nullable|string|max:255',
            
            // Calculated totals (optional, will be auto-calculated)
            'total_salaries' => 'nullable|numeric|min:0|max:999999999.99',
            'total_income_tax_deductions' => 'nullable|numeric|min:0|max:999999999.99',
            'total_payable_amount' => 'nullable|numeric|min:0|max:999999999.99',
            'total_salaries_paid_cash' => 'nullable|numeric|min:0|max:999999999.99',
            
            // Status and notes
            'status' => 'nullable|string|in:draft,approved,paid',
            'notes' => 'nullable|string|max:1000',
        ];

        // Additional validation for update requests
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // Make some fields optional for updates
            $rules['company_id'] = 'sometimes|integer|exists:companies,id';
            $rules['branch_id'] = 'sometimes|integer|exists:branches,id';
            $rules['fiscal_year_id'] = 'sometimes|integer|exists:fiscal_years,id';
            $rules['currency_id'] = 'sometimes|integer|exists:currencies,id';
            $rules['date'] = 'sometimes|date';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'Selected company does not exist.',
            'branch_id.required' => 'Branch is required.',
            'branch_id.exists' => 'Selected branch does not exist.',
            'fiscal_year_id.required' => 'Fiscal year is required.',
            'fiscal_year_id.exists' => 'Selected fiscal year does not exist.',
            'payroll_number.unique' => 'This payroll number already exists.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'second_date.date' => 'Second date must be a valid date.',
            'second_date.after_or_equal' => 'Second date must be after or equal to the first date.',
            'currency_id.required' => 'Currency is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'currency_rate.numeric' => 'Currency rate must be a number.',
            'currency_rate.min' => 'Currency rate must be greater than 0.',
            'currency_rate.max' => 'Currency rate is too large.',
            'account_id.exists' => 'Selected account does not exist.',
            'account_number.max' => 'Account number cannot exceed 50 characters.',
            'account_name.max' => 'Account name cannot exceed 255 characters.',
            'payment_account.max' => 'Payment account cannot exceed 255 characters.',
            'salaries_wages_period.max' => 'Salaries wages period cannot exceed 255 characters.',
            'total_salaries.numeric' => 'Total salaries must be a number.',
            'total_salaries.min' => 'Total salaries cannot be negative.',
            'total_salaries.max' => 'Total salaries is too large.',
            'total_income_tax_deductions.numeric' => 'Total income tax deductions must be a number.',
            'total_income_tax_deductions.min' => 'Total income tax deductions cannot be negative.',
            'total_income_tax_deductions.max' => 'Total income tax deductions is too large.',
            'total_payable_amount.numeric' => 'Total payable amount must be a number.',
            'total_payable_amount.min' => 'Total payable amount cannot be negative.',
            'total_payable_amount.max' => 'Total payable amount is too large.',
            'total_salaries_paid_cash.numeric' => 'Total salaries paid cash must be a number.',
            'total_salaries_paid_cash.min' => 'Total salaries paid cash cannot be negative.',
            'total_salaries_paid_cash.max' => 'Total salaries paid cash is too large.',
            'status.in' => 'Status must be one of: draft, approved, paid.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'company_id' => 'company',
            'branch_id' => 'branch',
            'fiscal_year_id' => 'fiscal year',
            'payroll_number' => 'payroll number',
            'date' => 'date',
            'second_date' => 'second date',
            'currency_id' => 'currency',
            'currency_rate' => 'currency rate',
            'account_id' => 'account',
            'account_number' => 'account number',
            'account_name' => 'account name',
            'payment_account' => 'payment account',
            'salaries_wages_period' => 'salaries wages period',
            'total_salaries' => 'total salaries',
            'total_income_tax_deductions' => 'total income tax deductions',
            'total_payable_amount' => 'total payable amount',
            'total_salaries_paid_cash' => 'total salaries paid cash',
            'status' => 'status',
            'notes' => 'notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-set company_id from request if not provided
        if (!$this->has('company_id') && $this->has('company_id')) {
            $this->merge([
                'company_id' => $this->company_id
            ]);
        }

        // Convert empty strings to null for optional fields
        $nullableFields = [
            'payroll_number', 'second_date', 'currency_rate', 'account_number', 
            'account_name', 'account_id', 'payment_account', 'salaries_wages_period',
            'total_salaries', 'total_income_tax_deductions', 'total_payable_amount',
            'total_salaries_paid_cash', 'status', 'notes'
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
    }
}
