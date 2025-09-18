<?php

namespace Modules\FinancialAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_id'       => 'required|exists:companies,id',
            'fiscal_year_id'   => 'required|exists:fiscal_years,id',
            'currency_id'      => 'required|exists:currencies,id',
            'account_group_id' => 'required|exists:account_groups,id',
            'parent_id'        => 'nullable|exists:accounts,id',
            'code'             => 'required|string|max:50|unique:accounts,code',
            'name'             => 'required|string|max:150',
            'type'             => 'required|in:asset,liability,equity,revenue,expense',
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
