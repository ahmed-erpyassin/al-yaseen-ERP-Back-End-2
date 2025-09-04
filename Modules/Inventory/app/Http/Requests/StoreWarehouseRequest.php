<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ✅ Get the validation rules for Add Warehouse with all required fields.
     */
    public function rules(): array
    {
        return [
            // ✅ Basic Information
            'warehouse_number' => 'required|string|max:255|unique:warehouses,warehouse_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',

            // ✅ Warehouse Keeper Information (References from employees table)
            'warehouse_keeper_employee_number' => 'nullable|string|max:255',
            'warehouse_keeper_employee_name' => 'nullable|string|max:255',
            'warehouse_keeper_id' => 'nullable|integer', // Reference to employees table

            // ✅ Contact Information
            'mobile' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',

            // ✅ Account Information (References from accounts table)
            'sales_account_id' => 'nullable|integer', // Reference to accounts table
            'purchase_account_id' => 'nullable|integer', // Reference to accounts table

            // ✅ System Fields
            'branch_id' => 'nullable|integer', // Reference to branches table
            'department_warehouse_id' => 'nullable|integer', // Reference to department_warehouses table
            'inventory_valuation_method' => 'required|in:natural_division,no_value,first_purchase_price,second_purchase_price,third_purchase_price',
            'status' => 'required|in:active,inactive',

            // ✅ Additional Data
            'warehouse_data' => 'nullable|array',
        ];
    }

    /**
     * ✅ Get custom messages for validator errors (Arabic).
     */
    public function messages(): array
    {
        return [
            // ✅ Basic Information
            'warehouse_number.required' => 'رقم المخزن مطلوب',
            'warehouse_number.unique' => 'رقم المخزن موجود مسبقاً',
            'name.required' => 'اسم المخزن مطلوب',
            'description.max' => 'الوصف يجب أن يكون أقل من 1000 حرف',
            'address.max' => 'العنوان يجب أن يكون أقل من 500 حرف',

            // ✅ Warehouse Keeper
            'warehouse_keeper_employee_number.max' => 'رقم موظف أمين المخزن يجب أن يكون أقل من 255 حرف',
            'warehouse_keeper_employee_name.max' => 'اسم موظف أمين المخزن يجب أن يكون أقل من 255 حرف',

            // ✅ Contact Information
            'mobile.max' => 'رقم الجوال يجب أن يكون أقل من 20 حرف',
            'fax_number.max' => 'رقم الفاكس يجب أن يكون أقل من 20 حرف',
            'phone_number.max' => 'رقم الهاتف يجب أن يكون أقل من 20 حرف',

            // ✅ System Fields
            'inventory_valuation_method.required' => 'طريقة تقييم البضائع مطلوبة',
            'inventory_valuation_method.in' => 'طريقة تقييم البضائع غير صالحة',
            'status.required' => 'الحالة مطلوبة',
            'status.in' => 'الحالة يجب أن تكون نشط أو غير نشط',
        ];
    }
}
