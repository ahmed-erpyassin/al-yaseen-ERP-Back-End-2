<?php

namespace Modules\FinancialAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CostCenterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_id'     => 'required|exists:companies,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'parent_id'      => 'nullable|exists:cost_centers,id',
            'code'           => 'required|string|max:50|unique:cost_centers,code',
            'name'           => 'required|string|max:150',
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
