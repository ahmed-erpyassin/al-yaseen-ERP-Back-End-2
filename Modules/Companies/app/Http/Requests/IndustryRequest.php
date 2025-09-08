<?php

namespace Modules\Companies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndustryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150|unique:industries,name,' . $this->industry,
            'name_en' => 'required|string|max:150|unique:industries,name_en,' . $this->industry,
            'description' => 'nullable|string',
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
