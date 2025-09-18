<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebitNoteRequest extends FormRequest
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
            'company_id'          => 'required|exists:companies,id',
            'user_id'             => 'required|exists:users,id',
            'notbook'             => 'required|string|max:255',
            'invoice_number'      => 'required|string|max:255|unique:debit_notes,invoice_number',
            'invoice_date'        => 'required|date',
            'invoice_time'        => 'required|date_format:H:i:s',
            'due_date'            => 'required|date',
            'client_id'           => 'required|exists:clients,id',
            'currency_id'         => 'required|exists:currencies,id',
            'currency_rate'       => 'required|numeric|min:0',
            'account_id'          => 'required|exists:accounts,id',
            'notice_amount'       => 'required|numeric|min:0',
            'amount'              => 'required|numeric|min:0',
            'tax_rate'            => 'required|numeric|min:0',
            'tax_amount'          => 'required|numeric|min:0',
            'total_notice_amount' => 'required|numeric|min:0',
            'notes'               => 'nullable|string',
        ];
    }
}
