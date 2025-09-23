<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManufacturingFormulaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ✅ Get the validation rules for Manufacturing Formula creation.
     */
    public function rules(): array
    {
        return [
            // ✅ Required fields
            'item_id' => 'required|exists:items,id',
            'consumed_quantity' => 'required|numeric|min:0',
            'produced_quantity' => 'required|numeric|min:0',
            'labor_cost' => 'required|numeric|min:0',
            'operating_cost' => 'required|numeric|min:0', // Will be mapped to overhead_cost
            'waste_cost' => 'required|numeric|min:0', // Will be mapped to total_raw_material_cost
            
            // ✅ Optional fields
            'unit_id' => 'nullable|exists:units,id',
            'formula_name' => 'nullable|string|max:255',
            'formula_description' => 'nullable|string|max:1000',
            'formula_number' => 'nullable|string|max:50|unique:manufactured_formulas,formula_number',

            // ✅ Purchase price selection
            'selected_purchase_price_type' => 'nullable|in:first,second,third',

            // ✅ Quality control
            'quality_requirements' => 'nullable|string|max:1000',
            'requires_inspection' => 'nullable|boolean', // Will be mapped to requires_quality_check

            // ✅ Status fields
            'status' => 'nullable|in:draft,active,completed,cancelled',
            'is_active' => 'nullable|boolean',

            // ✅ Fields that will be ignored (not in database)
            'batch_size' => 'nullable|numeric',
            'production_time_minutes' => 'nullable|numeric',
            'preparation_time_minutes' => 'nullable|numeric',
            'production_notes' => 'nullable|string',
            'preparation_notes' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'tolerance_percentage' => 'nullable|numeric',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
            
            // ✅ Additional optional fields
            'batch_size' => 'nullable|numeric|min:0',
            'production_time_minutes' => 'nullable|integer|min:0',
            'preparation_time_minutes' => 'nullable|integer|min:0',
            'production_notes' => 'nullable|string|max:1000',
            'preparation_notes' => 'nullable|string|max:1000',
            'usage_instructions' => 'nullable|string|max:1000',
            
            // ✅ Quality control
            'tolerance_percentage' => 'nullable|numeric|min:0|max:100',
            'quality_requirements' => 'nullable|string|max:1000',
            'requires_inspection' => 'nullable|boolean',
            
            // ✅ Status fields
            'status' => 'nullable|in:draft,active,inactive,archived',
            'is_active' => 'nullable|boolean',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
            
            // ✅ System fields (optional for API)
            'company_id' => 'nullable|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
        ];
    }

    /**
     * ✅ Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'Item selection is required | اختيار الصنف مطلوب',
            'item_id.exists' => 'Selected item does not exist | الصنف المحدد غير موجود',
            
            'consumed_quantity.required' => 'Consumed quantity is required | الكمية المستهلكة مطلوبة',
            'consumed_quantity.numeric' => 'Consumed quantity must be a number | الكمية المستهلكة يجب أن تكون رقم',
            'consumed_quantity.min' => 'Consumed quantity cannot be negative | الكمية المستهلكة لا يمكن أن تكون سالبة',
            
            'produced_quantity.required' => 'Produced quantity is required | الكمية المنتجة مطلوبة',
            'produced_quantity.numeric' => 'Produced quantity must be a number | الكمية المنتجة يجب أن تكون رقم',
            'produced_quantity.min' => 'Produced quantity cannot be negative | الكمية المنتجة لا يمكن أن تكون سالبة',
            
            'labor_cost.required' => 'Labor cost is required | تكلفة العمالة مطلوبة',
            'labor_cost.numeric' => 'Labor cost must be a number | تكلفة العمالة يجب أن تكون رقم',
            'labor_cost.min' => 'Labor cost cannot be negative | تكلفة العمالة لا يمكن أن تكون سالبة',
            
            'operating_cost.required' => 'Operating cost is required | التكلفة التشغيلية مطلوبة',
            'operating_cost.numeric' => 'Operating cost must be a number | التكلفة التشغيلية يجب أن تكون رقم',
            'operating_cost.min' => 'Operating cost cannot be negative | التكلفة التشغيلية لا يمكن أن تكون سالبة',
            
            'waste_cost.required' => 'Waste cost is required | تكلفة الهدر مطلوبة',
            'waste_cost.numeric' => 'Waste cost must be a number | تكلفة الهدر يجب أن تكون رقم',
            'waste_cost.min' => 'Waste cost cannot be negative | تكلفة الهدر لا يمكن أن تكون سالبة',
            
            'unit_id.exists' => 'Selected unit does not exist | الوحدة المحددة غير موجودة',
            
            'formula_number.unique' => 'Formula number already exists | رقم المعادلة موجود مسبقاً',
            'formula_number.max' => 'Formula number is too long | رقم المعادلة طويل جداً',
            
            'selected_purchase_price_type.in' => 'Invalid purchase price selection | اختيار سعر الشراء غير صالح',
            
            'batch_size.numeric' => 'Batch size must be a number | حجم الدفعة يجب أن يكون رقم',
            'batch_size.min' => 'Batch size cannot be negative | حجم الدفعة لا يمكن أن يكون سالب',
            
            'production_time_minutes.integer' => 'Production time must be an integer | وقت الإنتاج يجب أن يكون رقم صحيح',
            'production_time_minutes.min' => 'Production time cannot be negative | وقت الإنتاج لا يمكن أن يكون سالب',
            
            'preparation_time_minutes.integer' => 'Preparation time must be an integer | وقت التحضير يجب أن يكون رقم صحيح',
            'preparation_time_minutes.min' => 'Preparation time cannot be negative | وقت التحضير لا يمكن أن يكون سالب',
            
            'tolerance_percentage.numeric' => 'Tolerance percentage must be a number | نسبة التسامح يجب أن تكون رقم',
            'tolerance_percentage.min' => 'Tolerance percentage cannot be negative | نسبة التسامح لا يمكن أن تكون سالبة',
            'tolerance_percentage.max' => 'Tolerance percentage cannot exceed 100% | نسبة التسامح لا يمكن أن تتجاوز 100%',
            
            'status.in' => 'Invalid status value | قيمة الحالة غير صالحة',
            
            'effective_from.date' => 'Effective from must be a valid date | تاريخ البداية يجب أن يكون تاريخ صالح',
            'effective_to.date' => 'Effective to must be a valid date | تاريخ النهاية يجب أن يكون تاريخ صالح',
            'effective_to.after' => 'Effective to must be after effective from | تاريخ النهاية يجب أن يكون بعد تاريخ البداية',
            
            'company_id.exists' => 'Selected company does not exist | الشركة المحددة غير موجودة',
            'user_id.exists' => 'Selected user does not exist | المستخدم المحدد غير موجود',
            'branch_id.exists' => 'Selected branch does not exist | الفرع المحدد غير موجود',
        ];
    }

    /**
     * ✅ Get custom attribute names for validation messages.
     */
    public function attributes(): array
    {
        return [
            'item_id' => 'Item | الصنف',
            'consumed_quantity' => 'Consumed Quantity | الكمية المستهلكة',
            'produced_quantity' => 'Produced Quantity | الكمية المنتجة',
            'labor_cost' => 'Labor Cost | تكلفة العمالة',
            'operating_cost' => 'Operating Cost | التكلفة التشغيلية',
            'waste_cost' => 'Waste Cost | تكلفة الهدر',
            'unit_id' => 'Unit | الوحدة',
            'formula_name' => 'Formula Name | اسم المعادلة',
            'formula_description' => 'Formula Description | وصف المعادلة',
            'formula_number' => 'Formula Number | رقم المعادلة',
            'selected_purchase_price_type' => 'Purchase Price Selection | اختيار سعر الشراء',
            'batch_size' => 'Batch Size | حجم الدفعة',
            'production_time_minutes' => 'Production Time | وقت الإنتاج',
            'preparation_time_minutes' => 'Preparation Time | وقت التحضير',
            'production_notes' => 'Production Notes | ملاحظات الإنتاج',
            'preparation_notes' => 'Preparation Notes | ملاحظات التحضير',
            'usage_instructions' => 'Usage Instructions | تعليمات الاستخدام',
            'tolerance_percentage' => 'Tolerance Percentage | نسبة التسامح',
            'quality_requirements' => 'Quality Requirements | متطلبات الجودة',
            'requires_inspection' => 'Requires Inspection | يتطلب فحص',
            'status' => 'Status | الحالة',
            'is_active' => 'Active Status | حالة النشاط',
            'effective_from' => 'Effective From | ساري من',
            'effective_to' => 'Effective To | ساري إلى',
            'company_id' => 'Company | الشركة',
            'user_id' => 'User | المستخدم',
            'branch_id' => 'Branch | الفرع',
        ];
    }

    /**
     * ✅ Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // ✅ Set default values if not provided
        $this->merge([
            'selected_purchase_price_type' => $this->selected_purchase_price_type ?? 'first',
            'status' => $this->status ?? 'active',
            'is_active' => $this->is_active ?? true,
            'requires_inspection' => $this->requires_inspection ?? false,
        ]);
    }
}
