<?php

namespace Modules\HumanResources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_id'          => 'required|integer|exists:companies,id',
            'user_id'             => 'required|integer|exists:users,id',
            'branch_id'           => 'required|integer|exists:branches,id',
            'fiscal_year_id'      => 'required|integer|exists:fiscal_years,id',
            'department_id'       => 'required|integer|exists:departments,id',
            'job_title_id'        => 'required|integer|exists:job_titles,id',
            'manager_id'          => 'nullable|integer|exists:employees,id',

            'employee_number'     => 'required|string|unique:employees,employee_number',
            'code'                => 'required|string|unique:employees,code',

            'nickname'            => 'required|string|max:50',
            'first_name'          => 'required|string|max:100',
            'second_name'         => 'required|string|max:100',
            'third_name'          => 'nullable|string|max:100',
            'phone1'              => 'required|string|max:20',
            'phone2'              => 'nullable|string|max:20',
            'email'               => 'required|email|max:255|unique:employees,email',

            'birth_date'          => 'required|date',
            'address'             => 'required|string|max:255',
            'national_id'         => 'required|string|max:100',
            'id_number'           => 'required|string|max:50',
            'gender'              => 'required|in:male,female',

            'wives_count'         => 'nullable|integer|min:0',
            'children_count'      => 'nullable|integer|min:0',
            'dependents_count'    => 'nullable|string|max:50',

            'car_number'          => 'nullable|string|max:50',
            'is_driver'           => 'boolean',
            'is_sales'            => 'boolean',

            'hire_date'           => 'required|date',
            'employee_code'       => 'required|string|max:50',
            'employee_identifier' => 'required|string|max:50',
            'job_address'         => 'required|string|max:255',

            'salary'              => 'required|string|max:255',
            'billing_rate'        => 'required|numeric|min:0',
            'monthly_discount'    => 'required|numeric|min:0',

            'currency_id'         => 'required|integer|exists:currencies,id',

            'notes'               => 'nullable|string',
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
