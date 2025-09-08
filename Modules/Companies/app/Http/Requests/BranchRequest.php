<?php

namespace Modules\Companies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:branches,code,' . $this->branch,
            'name' => 'required|string|max:150|unique:branches,name,' . $this->branch,
            'address' => 'nullable|string|max:255',
            'landline' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'logo' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'status' => 'in:active,inactive',

            'currency_id' => 'nullable|exists:currencies,id',
            'manager_id' => 'nullable|exists:users,id',
            'financial_year_id' => 'nullable|exists:financial_years,id',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'city_id' => 'nullable|exists:cities,id',
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
