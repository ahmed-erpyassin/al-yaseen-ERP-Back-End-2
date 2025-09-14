<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
            'company_id'         => 'required|integer|exists:companies,id',
            'user_id'            => 'required|integer|exists:users,id',
            'number'             => 'required|integer',
            'date'               => 'required|date',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date',
            'currency_id'        => 'required|integer|exists:currencies,id',
            'description'        => 'required|string',
            'description_en'     => 'required|string',
            'notes'              => 'nullable|string',
            'total_budget'       => 'required|numeric',
            'total_income'       => 'required|numeric',

            'items'                  => 'array',
            'items.*.account_number' => 'required|string',
            'items.*.dapertment'     => 'required|string',
            'items.*.amount'         => 'required|numeric',
            'items.*.expense'        => 'required|numeric',
            'items.*.allocated'      => 'required|numeric',
            'items.*.notes'          => 'nullable|string',
        ];
    }
}
