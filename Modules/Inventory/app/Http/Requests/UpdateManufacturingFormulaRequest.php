<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManufacturingFormulaRequest extends FormRequest
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
        $companyId = $this->user()->company_id;
        $formulaId = $this->route('id');

        return [
            // ✅ Item Information (Final Product)
            'item_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('items', 'id')->where('company_id', $companyId)
            ],
            
            // ✅ Formula Information
            'formula_name' => 'sometimes|nullable|string|max:255',
            'formula_description' => 'sometimes|nullable|string|max:1000',
            'formula_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('bom_items', 'formula_number')
                    ->where('company_id', $companyId)
                    ->ignore($formulaId)
            ],
            
            // ✅ Manufacturing Details
            'manufacturing_duration' => 'sometimes|nullable|string|max:255',
            'manufacturing_duration_unit' => 'sometimes|nullable|in:minutes,hours,days,weeks,months',
            
            // ✅ Quantities
            'consumed_quantity' => 'sometimes|nullable|numeric|min:0',
            'produced_quantity' => 'sometimes|nullable|numeric|min:0',
            'batch_size' => 'sometimes|nullable|numeric|min:0',
            
            // ✅ Unit Information
            'unit_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:units,id'
            ],
            
            // ✅ Cost Information
            'labor_cost' => 'sometimes|nullable|numeric|min:0',
            'operating_cost' => 'sometimes|nullable|numeric|min:0',
            'waste_cost' => 'sometimes|nullable|numeric|min:0',
            
            // ✅ Purchase Price Selection
            'selected_purchase_price_type' => 'sometimes|nullable|in:first,second,third',
            
            // ✅ Time Information
            'production_time_minutes' => 'sometimes|nullable|integer|min:0',
            'preparation_time_minutes' => 'sometimes|nullable|integer|min:0',
            
            // ✅ Notes and Instructions
            'production_notes' => 'sometimes|nullable|string|max:1000',
            'preparation_notes' => 'sometimes|nullable|string|max:1000',
            'usage_instructions' => 'sometimes|nullable|string|max:1000',
            
            // ✅ Quality Control
            'tolerance_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
            'quality_requirements' => 'sometimes|nullable|string|max:1000',
            'requires_inspection' => 'sometimes|nullable|boolean',
            
            // ✅ Status Information
            'status' => 'sometimes|nullable|in:draft,active,inactive,archived',
            'is_active' => 'sometimes|nullable|boolean',
            
            // ✅ Effective Dates
            'effective_from' => 'sometimes|nullable|date',
            'effective_to' => 'sometimes|nullable|date|after:effective_from',
            
            // ✅ System fields (optional for API)
            'company_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('companies', 'id')
            ],
            'user_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')
            ],
            'branch_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('branches', 'id')
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'Item selection is required | اختيار الصنف مطلوب',
            'item_id.exists' => 'Selected item does not exist | الصنف المحدد غير موجود',
            
            'consumed_quantity.numeric' => 'Consumed quantity must be a number | الكمية المستهلكة يجب أن تكون رقم',
            'consumed_quantity.min' => 'Consumed quantity cannot be negative | الكمية المستهلكة لا يمكن أن تكون سالبة',
            
            'produced_quantity.numeric' => 'Produced quantity must be a number | الكمية المنتجة يجب أن تكون رقم',
            'produced_quantity.min' => 'Produced quantity cannot be negative | الكمية المنتجة لا يمكن أن تكون سالبة',
            
            'labor_cost.numeric' => 'Labor cost must be a number | تكلفة العمالة يجب أن تكون رقم',
            'labor_cost.min' => 'Labor cost cannot be negative | تكلفة العمالة لا يمكن أن تكون سالبة',
            
            'operating_cost.numeric' => 'Operating cost must be a number | التكلفة التشغيلية يجب أن تكون رقم',
            'operating_cost.min' => 'Operating cost cannot be negative | التكلفة التشغيلية لا يمكن أن تكون سالبة',
            
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
     * Get custom attribute names for validation messages.
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
            'manufacturing_duration' => 'Manufacturing Duration | مدة التصنيع',
            'manufacturing_duration_unit' => 'Duration Unit | وحدة المدة',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation logic
            $this->validateStatusTransition($validator);
            $this->validateEffectiveDates($validator);
            $this->validateQuantityLogic($validator);
        });
    }

    /**
     * Validate status transitions.
     */
    protected function validateStatusTransition($validator)
    {
        $newStatus = $this->input('status');
        
        if (!$newStatus) {
            return;
        }

        // Get current formula to check current status
        $formulaId = $this->route('id');
        if ($formulaId) {
            $currentFormula = \Modules\Inventory\Models\BomItem::find($formulaId);
            
            if ($currentFormula) {
                $currentStatus = $currentFormula->status;
                
                // Define allowed status transitions
                $allowedTransitions = [
                    'draft' => ['active', 'inactive', 'archived'],
                    'active' => ['inactive', 'archived'],
                    'inactive' => ['active', 'archived'],
                    'archived' => [], // Cannot change from archived
                ];
                
                if (isset($allowedTransitions[$currentStatus]) && 
                    !in_array($newStatus, $allowedTransitions[$currentStatus])) {
                    $validator->errors()->add(
                        'status',
                        "Cannot change status from {$currentStatus} to {$newStatus}."
                    );
                }
            }
        }
    }

    /**
     * Validate effective dates logic.
     */
    protected function validateEffectiveDates($validator)
    {
        $effectiveFrom = $this->input('effective_from');
        $effectiveTo = $this->input('effective_to');

        if ($effectiveFrom && $effectiveTo && $effectiveFrom >= $effectiveTo) {
            $validator->errors()->add(
                'effective_to',
                'Effective to date must be after effective from date.'
            );
        }

        // Check if dates are in the past for active formulas
        $status = $this->input('status');
        if ($status === 'active' && $effectiveTo && $effectiveTo < now()->toDateString()) {
            $validator->errors()->add(
                'effective_to',
                'Cannot set active formula with effective to date in the past.'
            );
        }
    }

    /**
     * Validate quantity logic.
     */
    protected function validateQuantityLogic($validator)
    {
        $consumedQuantity = $this->input('consumed_quantity');
        $producedQuantity = $this->input('produced_quantity');

        // Both quantities should be provided if one is provided
        if (($consumedQuantity !== null && $producedQuantity === null) || 
            ($consumedQuantity === null && $producedQuantity !== null)) {
            
            if ($consumedQuantity !== null) {
                $validator->errors()->add(
                    'produced_quantity',
                    'Produced quantity is required when consumed quantity is provided.'
                );
            } else {
                $validator->errors()->add(
                    'consumed_quantity',
                    'Consumed quantity is required when produced quantity is provided.'
                );
            }
        }

        // Validate efficiency ratio (optional business rule)
        if ($consumedQuantity !== null && $producedQuantity !== null && 
            $consumedQuantity > 0 && $producedQuantity > 0) {
            
            $efficiencyRatio = $producedQuantity / $consumedQuantity;
            
            // Example: efficiency ratio should be reasonable (between 0.1 and 10)
            if ($efficiencyRatio < 0.1 || $efficiencyRatio > 10) {
                $validator->errors()->add(
                    'produced_quantity',
                    'The efficiency ratio (produced/consumed) seems unrealistic. Please verify the quantities.'
                );
            }
        }
    }
}
