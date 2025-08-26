<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class CreateFunderRequest extends FormRequest
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
            'number'            => 'required|integer',
            'manager'           => 'required|string',
            'address'           => 'required|string',
            'work_phone'        => 'required|string',
            'fax_number'        => 'required|string',
            'home_phone'        => 'required|string',
            'statement'         => 'required|string',
            'statement_en'      => 'required|string',
            'file_open_start'   => 'required|date',
            'is_active'         => 'required|integer',
            'notes'             => 'required|string',
            'documents'         => 'nullable|file'
        ];
    }
}
