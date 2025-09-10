<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'movement_type' => [
                'required',
                Rule::in(['in', 'out', 'transfer', 'adjustment'])
            ],
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'movement_date' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'inventory_item_id.required' => 'الصنف مطلوب',
            'inventory_item_id.exists' => 'الصنف غير موجود',
            'warehouse_id.required' => 'المخزن مطلوب',
            'warehouse_id.exists' => 'المخزن غير موجود',
            'movement_type.required' => 'نوع الحركة مطلوب',
            'movement_type.in' => 'نوع الحركة غير صحيح',
            'quantity.required' => 'الكمية مطلوبة',
            'quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
        ];
    }
}
