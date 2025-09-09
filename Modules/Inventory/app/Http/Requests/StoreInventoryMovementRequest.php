<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ✅ Get the validation rules for creating inventory movement.
     */
    public function rules(): array
    {
        return [
            // ✅ Movement Information
            'movement_number' => 'nullable|string|max:255|unique:inventory_movements,movement_number',
            'movement_type' => 'required|in:outbound,inbound,transfer,manufacturing,inventory_count',
            'movement_date' => 'nullable|date',
            'movement_time' => 'nullable|date_format:H:i:s',
            
            // ✅ Vendor/Customer References
            'vendor_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'vendor_name' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            
            // ✅ Description
            'movement_description' => 'nullable|string|max:1000',
            
            // ✅ Invoice References
            'inbound_invoice_id' => 'nullable|integer',
            'outbound_invoice_id' => 'nullable|integer',
            'inbound_invoice_number' => 'nullable|string|max:255',
            'outbound_invoice_number' => 'nullable|string|max:255',
            
            // ✅ Additional Information
            'user_number' => 'nullable|string|max:255',
            'shipment_number' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            
            // ✅ Warehouse Reference
            'warehouse_id' => 'required|integer',
            
            // ✅ Status
            'status' => 'nullable|in:draft,confirmed,cancelled',
            
            // ✅ Movement Data (Details)
            'movement_data' => 'required|array|min:1',
            'movement_data.*.item_id' => 'required|integer',
            'movement_data.*.unit_id' => 'nullable|integer',
            'movement_data.*.quantity' => 'required|numeric|min:0.0001',
            'movement_data.*.unit_cost' => 'nullable|numeric|min:0',
            'movement_data.*.unit_price' => 'nullable|numeric|min:0',
            'movement_data.*.inventory_count' => 'nullable|numeric|min:0',
            'movement_data.*.notes' => 'nullable|string|max:500',
            'movement_data.*.batch_number' => 'nullable|string|max:255',
            'movement_data.*.expiry_date' => 'nullable|date',
            'movement_data.*.serial_number' => 'nullable|string|max:255',
            'movement_data.*.location_code' => 'nullable|string|max:255',
            'movement_data.*.shelf_number' => 'nullable|string|max:255',
            'movement_data.*.bin_number' => 'nullable|string|max:255',
        ];
    }

    /**
     * ✅ Get custom messages for validator errors (Arabic).
     */
    public function messages(): array
    {
        return [
            // ✅ Movement Information
            'movement_number.unique' => 'رقم الحركة موجود مسبقاً',
            'movement_type.required' => 'نوع الحركة مطلوب',
            'movement_type.in' => 'نوع الحركة غير صالح',
            'movement_date.date' => 'تاريخ الحركة غير صالح',
            'movement_time.date_format' => 'وقت الحركة غير صالح',
            
            // ✅ Warehouse
            'warehouse_id.required' => 'المخزن مطلوب',
            'warehouse_id.integer' => 'معرف المخزن غير صالح',
            
            // ✅ Movement Data
            'movement_data.required' => 'بيانات الحركة مطلوبة',
            'movement_data.array' => 'بيانات الحركة يجب أن تكون مصفوفة',
            'movement_data.min' => 'يجب إضافة عنصر واحد على الأقل',
            
            // ✅ Movement Data Items
            'movement_data.*.item_id.required' => 'الصنف مطلوب',
            'movement_data.*.item_id.integer' => 'معرف الصنف غير صالح',
            'movement_data.*.quantity.required' => 'الكمية مطلوبة',
            'movement_data.*.quantity.numeric' => 'الكمية يجب أن تكون رقم',
            'movement_data.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
            'movement_data.*.unit_cost.numeric' => 'تكلفة الوحدة يجب أن تكون رقم',
            'movement_data.*.unit_cost.min' => 'تكلفة الوحدة يجب أن تكون أكبر من أو تساوي صفر',
            'movement_data.*.unit_price.numeric' => 'سعر الوحدة يجب أن تكون رقم',
            'movement_data.*.unit_price.min' => 'سعر الوحدة يجب أن تكون أكبر من أو تساوي صفر',
            'movement_data.*.inventory_count.numeric' => 'عدد الجرد يجب أن يكون رقم',
            'movement_data.*.inventory_count.min' => 'عدد الجرد يجب أن يكون أكبر من أو يساوي صفر',
            'movement_data.*.expiry_date.date' => 'تاريخ الانتهاء غير صالح',
            'movement_data.*.notes.max' => 'الملاحظات يجب أن تكون أقل من 500 حرف',
        ];
    }

    /**
     * ✅ Get custom attribute names (Arabic).
     */
    public function attributes(): array
    {
        return [
            'movement_number' => 'رقم الحركة',
            'movement_type' => 'نوع الحركة',
            'movement_date' => 'تاريخ الحركة',
            'movement_time' => 'وقت الحركة',
            'vendor_name' => 'اسم المورد',
            'customer_name' => 'اسم العميل',
            'movement_description' => 'وصف الحركة',
            'warehouse_id' => 'المخزن',
            'movement_data' => 'بيانات الحركة',
        ];
    }
}
