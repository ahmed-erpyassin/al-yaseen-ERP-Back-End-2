<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'company_id'        => 'required|integer|exists:companies,id',
            'user_id'           => 'required|integer|exists:users,id',
            'client_type'       => 'required|in:0,1', // 0 = فردي, 1 = تجاري
            'client_number'     => 'required|string|unique:clients,client_number',
            'company_name'      => 'required|string|max:255',
            'first_name'        => 'required|string|max:100',
            'second_name'       => 'required|string|max:100',
            'phone'             => 'required|string|max:50',
            'mobile'            => 'required|string|max:50',
            'address1'          => 'required|string|max:255',
            'address2'          => 'nullable|string|max:255',
            'city'              => 'required|string|max:100',
            'region'            => 'nullable|string|max:100',
            'postal_code'       => 'nullable|string|max:50',
            'licensed_operator' => 'nullable|string|max:255',
            'code_number'       => 'required|string|unique:clients,code_number',
            'invoice_method'    => 'required|string|max:100',
            'department_id'     => 'required|integer|exists:departments,id',
            'project_id'        => 'required|integer|exists:projects,id',
            'funder_id'         => 'required|integer|exists:funders,id',
            'currency_id'       => 'required|integer|exists:currencies,id',
            'employee_id'       => 'required|integer|exists:employees,id',
            'email'             => 'nullable|email|max:255',
            'category'          => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ];
    }
}
