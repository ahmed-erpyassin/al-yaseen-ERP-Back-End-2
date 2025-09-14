<?php

namespace Modules\FinancialAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'journal' => 'required|string|max:255',
            'document_id' => 'nullable|integer',
            'type' => 'required|string|in:manual,sales,purchase,payment,receipt,adjustment,inventory,production',
            'entry_number' => 'required|string|max:100|unique:journals_entries,entry_number,' . $this->route('id'),
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|string|in:draft,posted,cancelled',
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
