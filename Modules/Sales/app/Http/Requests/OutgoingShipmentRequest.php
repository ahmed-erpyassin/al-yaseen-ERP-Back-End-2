<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutgoingShipmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Customer information
            'customer_id' => 'required|exists:customers,id',
            'customer_email' => 'nullable|email|max:150',

            // Optional fields
            'employee_id' => 'nullable|exists:employees,id',
            'branch_id' => 'nullable|exists:branches,id',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',

            // Status and licensed operator
            'status' => 'nullable|in:draft,pending,shipped,delivered,cancelled',
            'licensed_operator' => 'nullable|string|max:255',

            // Items validation
            'items' => 'sometimes|required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_number' => 'nullable|string|max:100',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.unit_name' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.notes' => 'nullable|string|max:500',
            'total_local' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.total_foreign' => 'nullable|numeric|min:0',
            'items.*.total_local' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
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
