<?php

namespace App\Http\Requests\Accounts;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'account_number'    => 'required|string|max:50|unique:accounts,account_number',
            'name'              => 'required|string|max:255',

            'account_type'      => 'required|in:asset,liability,equity,revenue,expense',
            'account_nature'    => 'required|in:all,debit,credit',

            'level'             => 'required|integer|min:1|max:10',

            'currency_id'       => 'nullable|exists:currencies,id',

            'report_type'       => 'nullable|in:balance_sheet,income_statement,other',

            'allow_all_users'   => 'boolean',
            'allowed_user_id'   => 'nullable|exists:users,id',

            'opening_date'      => 'nullable|date',
            'opened_by'         => 'required|string|max:255',

            'linked_account'    => 'nullable|string|max:255',
            'property_id'       => 'nullable|string|max:255',

            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'depreciation_classification' => 'required|in:none,pl,trading,operating,income_expense',
        ];
    }
}
