<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'company_id'          => 'required|exists:companies,id',
            'user_id'             => 'required|exists:users,id',
            'notebook' => 'required|string|max:255',
            'project_number' => 'required|string|unique:projects,project_number',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'phone' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'licensed_operator' => 'required|string|max:255',
            'currency_id' => 'required|integer|exists:currencies,id',
            'currency_price' => 'required|numeric',
            'include_vat' => 'required|numeric',
            'project_name' => 'required|string|max:255',
            'manager_name' => 'required|string|max:255',
            'opportunity' => 'required|string|max:255',
            'statement' => 'required|string',
            'country_id' => 'required|integer|exists:countries,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|integer',
            'notes' => 'nullable|string',
        ];
    }
}
