<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
            'unit_id' => 'required|exists:units,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'parent_id' => 'nullable|exists:items,id',
            'item_number' => 'nullable|string|max:255|unique:items,item_number',
            'code' => 'required|string|max:255|unique:items,code',
            'catalog_number' => 'nullable|string|max:255|unique:items,catalog_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'model' => 'nullable|string|max:255',
            'unit_name' => 'nullable|string|max:255',
            'type' => 'required|in:product,service,material,raw_material',
            'quantity' => 'nullable|numeric|min:0',
            'balance' => 'nullable|numeric',
            'minimum_limit' => 'nullable|numeric|min:0',
            'maximum_limit' => 'nullable|numeric|min:0',
            'reorder_limit' => 'nullable|numeric|min:0',
            'max_reorder_limit' => 'nullable|numeric|min:0',
            // Purchase Prices (أسعار الشراء)
            'first_purchase_price' => 'nullable|numeric|min:0',
            'second_purchase_price' => 'nullable|numeric|min:0',
            'third_purchase_price' => 'nullable|numeric|min:0',
            'purchase_discount_rate' => 'nullable|numeric|min:0|max:100',
            'purchase_prices_include_vat' => 'boolean',

            // Sale Prices (أسعار البيع)
            'first_sale_price' => 'nullable|numeric|min:0',
            'second_sale_price' => 'nullable|numeric|min:0',
            'third_sale_price' => 'nullable|numeric|min:0',
            'sale_discount_rate' => 'nullable|numeric|min:0|max:100',
            'maximum_sale_discount_rate' => 'nullable|numeric|min:0|max:100',
            'minimum_allowed_sale_price' => 'nullable|numeric|min:0',
            'sale_prices_include_vat' => 'boolean',

            // VAT Information (معلومات الضريبة)
            'item_subject_to_vat' => 'boolean',

            'notes' => 'nullable|string',

            // Barcode Information (معلومات الباركود)
            'barcode' => 'nullable|string|max:255|unique:items,barcode',
            'barcode_type' => 'nullable|string|in:C128,EAN13,C39,UPCA,ITF',

            // Product Information (معلومات المنتج)
            'expiry_date' => 'nullable|date|after:today',
            'image' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'item_type' => 'required|string|max:255',

            'active' => 'boolean',
            'stock_tracking' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'unit_id.required' => 'الوحدة مطلوبة',
            'unit_id.exists' => 'الوحدة غير موجودة',
            'warehouse_id.exists' => 'المخزن غير موجود',
            'parent_id.exists' => 'الصنف الأب غير موجود',
            'item_number.unique' => 'رقم الصنف موجود مسبقاً',
            'code.required' => 'كود الصنف مطلوب',
            'code.unique' => 'كود الصنف موجود مسبقاً',
            'catalog_number.unique' => 'رقم الكتالوج موجود مسبقاً',
            'name.required' => 'اسم الصنف مطلوب',
            'type.required' => 'نوع الصنف مطلوب',
            'type.in' => 'نوع الصنف غير صحيح',
            'barcode.unique' => 'الباركود موجود مسبقاً',
            'quantity.numeric' => 'الكمية يجب أن تكون رقم',
            'quantity.min' => 'الكمية يجب أن تكون أكبر من أو تساوي صفر',
            'balance.numeric' => 'الرصيد يجب أن يكون رقم',
            'minimum_limit.numeric' => 'الحد الأدنى يجب أن يكون رقم',
            'minimum_limit.min' => 'الحد الأدنى يجب أن يكون أكبر من أو يساوي صفر',
            'maximum_limit.numeric' => 'الحد الأقصى يجب أن يكون رقم',
            'maximum_limit.min' => 'الحد الأقصى يجب أن يكون أكبر من أو يساوي صفر',
            'reorder_limit.numeric' => 'حد إعادة الطلب يجب أن يكون رقم',
            'reorder_limit.min' => 'حد إعادة الطلب يجب أن يكون أكبر من أو يساوي صفر',
            'max_reorder_limit.numeric' => 'أغلى حد لإعادة الطلب يجب أن يكون رقم',
            'max_reorder_limit.min' => 'أغلى حد لإعادة الطلب يجب أن يكون أكبر من أو يساوي صفر',

            // Purchase Prices Messages
            'purchase_discount_rate.numeric' => 'نسبة الخصم عند الشراء يجب أن تكون رقم',
            'purchase_discount_rate.min' => 'نسبة الخصم عند الشراء يجب أن تكون أكبر من أو تساوي صفر',
            'purchase_discount_rate.max' => 'نسبة الخصم عند الشراء يجب أن تكون أقل من أو تساوي 100% (يجب إدخال النسبة بالرمز %)',

            // Sale Prices Messages
            'sale_discount_rate.numeric' => 'نسبة الخصم عند البيع يجب أن تكون رقم',
            'sale_discount_rate.min' => 'نسبة الخصم عند البيع يجب أن تكون أكبر من أو تساوي صفر',
            'sale_discount_rate.max' => 'نسبة الخصم عند البيع يجب أن تكون أقل من أو تساوي 100% (يجب إدخال النسبة بالرمز %)',
            'maximum_sale_discount_rate.numeric' => 'أعلى نسبة خصم عند البيع يجب أن تكون رقم',
            'maximum_sale_discount_rate.min' => 'أعلى نسبة خصم عند البيع يجب أن تكون أكبر من أو تساوي صفر',
            'maximum_sale_discount_rate.max' => 'أعلى نسبة خصم عند البيع يجب أن تكون أقل من أو تساوي 100% (يجب إدخال النسبة بالرمز %)',
            'minimum_allowed_sale_price.numeric' => 'أقل سعر بيع مسموح به يجب أن يكون رقم',
            'minimum_allowed_sale_price.min' => 'أقل سعر بيع مسموح به يجب أن يكون أكبر من أو يساوي صفر',

            // VAT Toggle Messages
            'sale_prices_include_vat.boolean' => 'أسعار البيع تشمل الضريبة المضافة يجب أن تكون قيمة منطقية (نعم/لا)',
            'purchase_prices_include_vat.boolean' => 'أسعار الشراء تشمل الضريبة المضافة يجب أن تكون قيمة منطقية (نعم/لا)',
            'item_subject_to_vat.boolean' => 'يخضع الصنف لضريبة المضافة يجب أن تكون قيمة منطقية (نعم/لا)',

            // Barcode Messages
            'barcode.unique' => 'الباركود موجود مسبقاً',
            'barcode.max' => 'الباركود يجب أن يكون أقل من 255 حرف',
            'barcode_type.in' => 'نوع الباركود يجب أن يكون أحد الأنواع المدعومة: C128, EAN13, C39, UPCA, ITF',

            // Product Information Messages
            'expiry_date.date' => 'تاريخ الانتهاء يجب أن يكون تاريخ صحيح',
            'expiry_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد اليوم',
            'item_type.required' => 'نوع الصنف مطلوب',
            'item_type.max' => 'نوع الصنف يجب أن يكون أقل من 255 حرف',
        ];
    }
}
