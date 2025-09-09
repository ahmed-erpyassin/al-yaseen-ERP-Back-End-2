<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentWarehouseRequest extends FormRequest
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
            'department_number' => 'required|string|max:255|unique:department_warehouses,department_number',
            'department_name_ar' => 'required|string|max:255',
            'department_name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'department_number.required' => 'رقم القسم مطلوب',
            'department_number.unique' => 'رقم القسم موجود مسبقاً',
            'department_name_ar.required' => 'اسم القسم باللغة العربية مطلوب',
            'manager_email.email' => 'بريد المدير الإلكتروني غير صحيح',
        ];
    }
}
