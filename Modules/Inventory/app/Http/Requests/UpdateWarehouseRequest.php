<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
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
            'location' => 'nullable|string|max:255',
            'warehouse_keeper_employee_number' => 'nullable|string|max:255',
            'warehouse_keeper_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'fax_number' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'department_warehouse_id' => 'nullable|exists:department_warehouses,id',
            'purchase_account' => 'nullable|string|max:255',
            'sale_account' => 'nullable|string|max:255',
            'inventory_valuation_method' => 'required|in:natural_division,no_value,first_purchase_price,second_purchase_price,third_purchase_price',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المخزن مطلوب',
            'inventory_valuation_method.required' => 'طريقة تقييم البضائع مطلوبة',
            'status.required' => 'الحالة مطلوبة',
        ];
    }
}
