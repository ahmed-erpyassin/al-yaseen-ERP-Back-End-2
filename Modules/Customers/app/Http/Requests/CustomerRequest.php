<?php

namespace Modules\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_id'   => 'required|integer',
            'branch_id'    => 'required|integer',
            'currency_id'  => 'required|integer',
            'employee_id'  => 'required|integer',
            'country_id'   => 'required|integer',
            'region_id'    => 'required|integer',
            'city_id'      => 'required|integer',

            'first_name'   => 'required|string|max:100',
            'second_name'  => 'required|string|max:100',
            'contact_name' => 'required|string|max:100',
            'email'        => 'required|email|max:150',
            'phone'        => 'required|string|max:50',
            'mobile'       => 'required|string|max:50',
            'address_one'  => 'required|string|max:255',
            'address_two'  => 'required|string|max:255',
            'postal_code'  => 'required|string|max:20',
            'tax_number'   => 'required|string|max:50',
            'notes'        => 'nullable|string|max:500',
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
