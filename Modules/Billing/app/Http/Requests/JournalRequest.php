<?php

namespace Modules\Billing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'currency_id' => 'required|exists:currencies,id',
            'employee_id' => 'nullable|exists:employees,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:sales,purchase',
            'code' => 'required|string|max:50|unique:journals,code,' . $this->id,
            'max_documents' => 'nullable|integer|min:0',
            'current_number' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,closed',
            'notes' => 'nullable|string',
            'financial_journal_id' => 'nullable|exists:journals_financial,id',
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
