<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'symbol' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'decimal_places' => 'required|integer|min:0|max:6',

            // Balance Unit
            'balance_unit' => 'required|in:piece,liter,kilo,ton,carton',
            'custom_balance_unit' => 'nullable|string|max:255',

            // Dimensions
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'quantity_factor' => 'nullable|numeric|min:0.0001',

            // Second Unit
            'second_unit' => 'nullable|in:piece,liter,kilo,ton,carton',
            'custom_second_unit' => 'nullable|string|max:255',
            'second_unit_contains' => 'nullable|string|max:255',
            'custom_second_unit_contains' => 'nullable|string|max:255',
            'second_unit_content' => 'nullable|string',
            'second_unit_item_number' => 'nullable|string|max:255',

            // Third Unit
            'third_unit' => 'nullable|in:piece,liter,kilo,ton,carton',
            'custom_third_unit' => 'nullable|string|max:255',
            'third_unit_contains' => 'nullable|string|max:255',
            'custom_third_unit_contains' => 'nullable|string|max:255',
            'third_unit_content' => 'nullable|string',
            'third_unit_item_number' => 'nullable|string|max:255',

            // Default Units
            'default_handling_unit_id' => 'nullable|exists:units,id',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',

            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الوحدة مطلوب',
            'decimal_places.required' => 'عدد الخانات العشرية مطلوب',
            'decimal_places.integer' => 'عدد الخانات العشرية يجب أن يكون رقم صحيح',
            'decimal_places.min' => 'عدد الخانات العشرية يجب أن يكون أكبر من أو يساوي صفر',
            'decimal_places.max' => 'عدد الخانات العشرية يجب أن يكون أقل من أو يساوي 6',
            'balance_unit.required' => 'وحدة الرصيد مطلوبة',
            'balance_unit.in' => 'وحدة الرصيد غير صحيحة',
            'length.numeric' => 'الطول يجب أن يكون رقم',
            'length.min' => 'الطول يجب أن يكون أكبر من أو يساوي صفر',
            'width.numeric' => 'العرض يجب أن يكون رقم',
            'width.min' => 'العرض يجب أن يكون أكبر من أو يساوي صفر',
            'height.numeric' => 'الارتفاع يجب أن يكون رقم',
            'height.min' => 'الارتفاع يجب أن يكون أكبر من أو يساوي صفر',
            'quantity_factor.numeric' => 'معامل الكمية يجب أن يكون رقم',
            'quantity_factor.min' => 'معامل الكمية يجب أن يكون أكبر من صفر',
            'second_unit.in' => 'الوحدة الثانية غير صحيحة',
            'third_unit.in' => 'الوحدة الثالثة غير صحيحة',
            'default_handling_unit_id.exists' => 'وحدة التعامل الافتراضية غير موجودة',
            'default_warehouse_id.exists' => 'المخزن الافتراضي غير موجود',
            'status.required' => 'الحالة مطلوبة',
        ];
    }
}
