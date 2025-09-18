<?php

namespace Modules\FinancialAccounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaAttachmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'journal_entry_id' => 'nullable|exists:journals_entries,id',
            'type' => 'nullable|string|max:50',
            'document_id' => 'nullable|integer',
            'file_path' => 'required|string|max:255',
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
