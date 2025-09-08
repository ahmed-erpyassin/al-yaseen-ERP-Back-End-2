<?php

namespace Modules\Companies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150|unique:business_types,name,' . $this->business_type,
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
            'industry_id' => 'required|exists:industries,id',
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
