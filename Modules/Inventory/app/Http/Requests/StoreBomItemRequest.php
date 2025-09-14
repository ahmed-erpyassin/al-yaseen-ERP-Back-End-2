<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBomItemRequest extends FormRequest
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
            'branch_id' => 'nullable|exists:branches,id',
            'item_id' => 'required|exists:items,id',
            'component_id' => 'required|exists:items,id|different:item_id',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0.000001',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'الصنف الأساسي مطلوب',
            'item_id.exists' => 'الصنف الأساسي غير موجود',
            'component_id.required' => 'المكون مطلوب',
            'component_id.exists' => 'المكون غير موجود',
            'component_id.different' => 'المكون يجب أن يكون مختلف عن الصنف الأساسي',
            'unit_id.required' => 'الوحدة مطلوبة',
            'unit_id.exists' => 'الوحدة غير موجودة',
            'quantity.required' => 'الكمية مطلوبة',
            'quantity.numeric' => 'الكمية يجب أن تكون رقم',
            'quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
        ];
    }
}
