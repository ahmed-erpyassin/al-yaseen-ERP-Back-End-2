<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutgoingShipment extends FormRequest
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
            'company_id'    => 'required|exists:companies,id',
            'user_id'       => 'required|exists:users,id',
            'notebook'      => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255|unique:outgoing_shipments,invoice_number',
            'invoice_date'  => 'required|date',
            'invoice_time'  => 'nullable|date_format:H:i',
            'due_date'      => 'nullable|date',
            'client_id'     => 'required|exists:clients,id',
            'notes'         => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.item_number'   => 'required|string|max:255',
            'items.*.item_name'     => 'required|string|max:255',
            'items.*.item_statement'=> 'required|string|max:50',
            'items.*.quantity'      => 'required|numeric|min:0',
            'items.*.unit'          => 'required|numeric|min:0',
            'items.*.warehouse_id'  => 'required|numeric|min:0',
        ];
    }
}
