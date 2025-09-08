<?php

namespace Modules\Companies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150|unique:cities,name,' . $this->city,
            'name_en' => 'required|string|max:150|unique:cities,name_en,' . $this->city,
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
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
