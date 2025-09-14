<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'         => 'required|integer|exists:companies,id',
            'user_id'            => 'required|integer|exists:users,id',
            'employee_number'      => 'required|string|unique:employees,employee_number',
            'nickname'                => 'required|string|max:50',
            'first_name'           => 'required|string|max:100',
            'second_name'          => 'required|string|max:100',
            'third_name'           => 'required|string|max:100',
            'phone1'               => 'required|string|max:20',
            'phone2'               => 'required|string|max:20',
            'email'                => 'required|email|max:255',
            'birth_date'           => 'required|date',
            'address'              => 'required|string|max:255',
            'national_id'          => 'required|string|max:100',
            'id_number'            => 'required|string|max:50',
            'gender'               => 'required|in:ذكر,أنثى',
            'wives_count'          => 'required|integer',
            'children_count'       => 'required|integer',
            'dependents_count'     => 'required|string|max:50',
            'car_number'           => 'required|string|max:50',
            'is_driver'            => 'required|boolean|max:50',
            'is_sales'             => 'required|boolean|max:50',
            'car_number'           => 'required|string|max:50',

            'job_title'            => 'required|string|max:255',
            'hiring_date'          => 'required|date',
            'employee_code'        => 'required|string|max:50',
            'employee_identifier'  => 'required|string|max:50',
            'job_address'          => 'required|string|max:255',
            'department_id'        => 'required|integer|exists:departments,id',
            'salary'               => 'required|string|max:255',
            'billing_rate'         => 'required|numeric',
            'monthly_discount'     => 'required|numeric',
            'currency_id'          => 'required|integer|exists:currencies,id',
            'notes'                => 'nullable|string',
        ];
    }
}
