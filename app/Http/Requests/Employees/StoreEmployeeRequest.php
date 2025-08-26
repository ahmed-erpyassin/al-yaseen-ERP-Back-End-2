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
            'employee_number'      => 'required|string|unique:employees,employee_number',
            'title'                => 'required|string|max:50',
            'first_name'           => 'required|string|max:100',
            'second_name'          => 'required|string|max:100',
            'third_name'           => 'required|string|max:100',
            'phone1'               => 'required|string|max:20',
            'phone2'               => 'required|string|max:20',
            'email'                => 'required|email|max:255',
            'id_number'            => 'required|string|max:50',
            'passport'             => 'required|string|max:50',
            'nationality'          => 'required|string|max:100',
            'birth_date'           => 'required|date',
            'facebook_id'          => 'required|string|max:255',
            'children_count'       => 'required|integer',
            'daughters_count'      => 'required|integer',
            'gender'               => 'required|in:ذكر,أنثى',
            'wives_count'          => 'required|integer',
            'brothers_count'       => 'required|integer',
            'sisters_count'        => 'required|integer',
            'car_number'           => 'required|string|max:50',

            'job_title'            => 'required|string|max:255',
            'hiring_date'          => 'required|date',
            'employee_code'        => 'required|string|max:50',
            'employee_identifier'  => 'required|string|max:50',
            'address'              => 'required|string|max:255',
            'department'           => 'required|string|max:255',
            'sub_department'       => 'required|string|max:255',
            'monthly_department'   => 'required|string|max:255',
            'basic_salary'         => 'required|numeric',
            'overtime_rate'        => 'required|numeric',
            'currency'             => 'required|string|max:10',
            'notes'                => 'required|string',
            'attachments'          => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ];
    }
}
