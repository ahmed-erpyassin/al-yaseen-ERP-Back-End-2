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
            'user_id'      => 'required|exists:users,id',
            'company_id'   => 'required|exists:companies,id',
            'branch_id'    => 'required|exists:branches,id',
            'currency_id'  => 'required|exists:currencies,id',
            'employee_id'  => 'required|exists:employees,id',
            'country_id'   => 'required|exists:countries,id',
            'region_id'    => 'required|exists:regions,id',
            'city_id'      => 'required|exists:cities,id',

            'first_name'   => 'required|string|max:100',
            'second_name'  => 'nullable|string|max:100',
            'contact_name' => 'nullable|string|max:100',
            'email'        => 'nullable|email|max:150',
            'phone'        => 'nullable|string|max:50',
            'mobile'       => 'nullable|string|max:50',
            'address_one'  => 'nullable|string|max:255',
            'address_two'  => 'nullable|string|max:255',
            'postal_code'  => 'nullable|string|max:20',
            'tax_number'   => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:500',

            'created_by'   => 'nullable|exists:users,id',
            'updated_by'   => 'nullable|exists:users,id',
            'deleted_by'   => 'nullable|exists:users,id',

            'status'       => 'required|in:active,inactive',
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
