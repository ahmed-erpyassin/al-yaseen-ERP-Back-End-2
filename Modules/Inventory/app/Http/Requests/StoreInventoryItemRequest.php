<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
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
            'item_number' => 'required|string|max:255|unique:inventory_items,item_number',
            'item_name_ar' => 'required|string|max:255',
            'item_name_en' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:inventory_items,barcode',
            'model' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'quantity' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'minimum_limit' => 'nullable|numeric|min:0',
            'reorder_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'item_number.required' => 'رقم الصنف مطلوب',
            'item_number.unique' => 'رقم الصنف موجود مسبقاً',
            'item_name_ar.required' => 'اسم الصنف باللغة العربية مطلوب',
            'barcode.unique' => 'الباركود موجود مسبقاً',
            'unit.required' => 'الوحدة مطلوبة',
        ];
    }
}
