<?php

namespace Modules\Billing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoicePaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'invoice_id'     => 'required|exists:invoices,id',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|max:50',
            'amount'         => 'required|numeric|min:0',
            'currency_id'    => 'required|exists:currencies,id',
            'exchange_rate'  => 'nullable|numeric|min:0',
            'reference'      => 'nullable|string|max:255',
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
