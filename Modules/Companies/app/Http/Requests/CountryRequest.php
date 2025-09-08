<?php

namespace Modules\Companies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:5|unique:countries,code,' . $this->country,
            'name' => 'required|string|max:150|unique:countries,name,' . $this->country,
            'name_en' => 'required|string|max:150|unique:countries,name_en,' . $this->country,
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:companies,id',
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
