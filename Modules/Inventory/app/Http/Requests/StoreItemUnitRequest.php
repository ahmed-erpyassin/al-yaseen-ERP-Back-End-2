<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemUnitRequest extends FormRequest
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
            'unit_id' => 'required|exists:units,id',
            'conversion_rate' => 'required|numeric|min:0.000001',
            'is_default' => 'boolean',

            // Unit Type and Configuration
            'unit_type' => 'required|in:balance,second,third',
            'quantity_factor' => 'nullable|numeric|min:0.0001',

            // Balance Unit (وحدة الرصيد)
            'balance_unit' => 'required|in:piece,liter,kilo,ton,carton',
            'custom_balance_unit' => 'nullable|string|max:255',

            // Dimensions (الأبعاد)
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',

            // Second Unit (الوحدة الثانية)
            'second_unit' => 'nullable|in:piece,liter,kilo,ton,carton',
            'custom_second_unit' => 'nullable|string|max:255',
            'second_unit_contains' => 'nullable|string|max:255',
            'custom_second_unit_contains' => 'nullable|string|max:255',
            'second_unit_content' => 'nullable|string',
            'second_unit_item_number' => 'nullable|string|max:255',

            // Third Unit (الوحدة الثالثة)
            'third_unit' => 'nullable|in:piece,liter,kilo,ton,carton',
            'custom_third_unit' => 'nullable|string|max:255',
            'third_unit_contains' => 'nullable|string|max:255',
            'custom_third_unit_contains' => 'nullable|string|max:255',
            'third_unit_content' => 'nullable|string',
            'third_unit_item_number' => 'nullable|string|max:255',

            // Default Units (الوحدات الافتراضية)
            'default_handling_unit_id' => 'nullable|exists:units,id',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',

            // Legacy Contains Information
            'contains' => 'nullable|string|max:255',
            'custom_contains' => 'nullable|string|max:255',
            'unit_content' => 'nullable|string',
            'unit_item_number' => 'nullable|string|max:255',

            // Pricing per Unit
            'unit_purchase_price' => 'nullable|numeric|min:0',
            'unit_sale_price' => 'nullable|numeric|min:0',

            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'الصنف مطلوب',
            'item_id.exists' => 'الصنف غير موجود',
            'unit_id.required' => 'الوحدة مطلوبة',
            'unit_id.exists' => 'الوحدة غير موجودة',
            'conversion_rate.required' => 'معدل التحويل مطلوب',
            'conversion_rate.numeric' => 'معدل التحويل يجب أن يكون رقم',
            'conversion_rate.min' => 'معدل التحويل يجب أن يكون أكبر من صفر',
            'unit_type.required' => 'نوع الوحدة مطلوب',
            'unit_type.in' => 'نوع الوحدة غير صحيح',
            'quantity_factor.numeric' => 'معامل الكمية يجب أن يكون رقم',
            'quantity_factor.min' => 'معامل الكمية يجب أن يكون أكبر من صفر',

            // Balance Unit Messages
            'balance_unit.required' => 'وحدة الرصيد مطلوبة',
            'balance_unit.in' => 'وحدة الرصيد غير صحيحة',

            // Dimensions Messages
            'length.numeric' => 'الطول يجب أن يكون رقم',
            'length.min' => 'الطول يجب أن يكون أكبر من أو يساوي صفر',
            'width.numeric' => 'العرض يجب أن يكون رقم',
            'width.min' => 'العرض يجب أن يكون أكبر من أو يساوي صفر',
            'height.numeric' => 'الارتفاع يجب أن يكون رقم',
            'height.min' => 'الارتفاع يجب أن يكون أكبر من أو يساوي صفر',

            // Second Unit Messages
            'second_unit.in' => 'الوحدة الثانية غير صحيحة',

            // Third Unit Messages
            'third_unit.in' => 'الوحدة الثالثة غير صحيحة',

            // Default Units Messages
            'default_handling_unit_id.exists' => 'وحدة التعامل الافتراضية غير موجودة',
            'default_warehouse_id.exists' => 'المخزن الافتراضي غير موجود',

            // Pricing Messages
            'unit_purchase_price.numeric' => 'سعر الشراء للوحدة يجب أن يكون رقم',
            'unit_purchase_price.min' => 'سعر الشراء للوحدة يجب أن يكون أكبر من أو يساوي صفر',
            'unit_sale_price.numeric' => 'سعر البيع للوحدة يجب أن يكون رقم',
            'unit_sale_price.min' => 'سعر البيع للوحدة يجب أن يكون أكبر من أو يساوي صفر',
            'status.required' => 'الحالة مطلوبة',
        ];
    }
}
