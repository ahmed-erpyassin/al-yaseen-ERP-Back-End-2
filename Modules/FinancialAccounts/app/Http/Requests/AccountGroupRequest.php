<?php

namespace Modules\FinancialAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'parent_id'  => 'nullable|exists:account_groups,id',
            'code'       => 'required|string|max:50|unique:account_groups,code',
            'name'       => 'required|string|max:150',
            'type'       => 'required|in:asset,liability,equity,revenue,expense',
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
