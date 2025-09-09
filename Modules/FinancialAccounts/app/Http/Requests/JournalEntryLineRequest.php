<?php

namespace Modules\FinancialAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryLineRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'journal_entry_id' => 'required|exists:journals_entries,id',
            'currency_id' => 'required|exists:currencies,id',
            'account_id' => 'required|exists:accounts,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'project_id' => 'nullable|exists:projects,id',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'exchange_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
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
