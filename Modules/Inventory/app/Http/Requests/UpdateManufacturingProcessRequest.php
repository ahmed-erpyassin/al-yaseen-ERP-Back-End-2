<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManufacturingProcessRequest extends FormRequest
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

        return [
            // Manufacturing Formula Information
            'manufacturing_formula_id' => [
                'nullable',
                'integer',
                Rule::exists('bom_items', 'id')->where('company_id', $companyId)
            ],
            
            // Item Information (Final Product)
            'item_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('items', 'id')->where('company_id', $companyId)
            ],
            
            // Manufacturing Details
            'manufacturing_duration' => 'nullable|string|max:255',
            'manufacturing_duration_unit' => 'nullable|in:minutes,hours,days,weeks,months',
            'produced_quantity' => 'sometimes|required|numeric|min:0.0001|max:999999.9999',
            
            // Warehouse Information
            'raw_materials_warehouse_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                          ->where('status', 'active');
                })
            ],
            'finished_product_warehouse_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                          ->where('status', 'active');
                })
            ],
            
            // Process Information
            'status' => 'nullable|in:draft,in_progress,completed,cancelled',
            'process_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            
            // Cost Information
            'labor_cost' => 'nullable|numeric|min:0|max:999999.99',
            'overhead_cost' => 'nullable|numeric|min:0|max:999999.99',
            
            // Additional Information
            'notes' => 'nullable|string|max:1000',
            'batch_number' => 'nullable|string|max:100',
            'production_order_number' => 'nullable|string|max:100',
            
            // Raw Materials Array
            'raw_materials' => 'nullable|array',
            'raw_materials.*.item_id' => [
                'required_with:raw_materials',
                'integer',
                Rule::exists('items', 'id')->where('company_id', $companyId)
            ],
            'raw_materials.*.consumed_quantity' => 'required_with:raw_materials|numeric|min:0.0001|max:999999.9999',
            'raw_materials.*.unit_cost' => 'nullable|numeric|min:0|max:999999.99',
            'raw_materials.*.warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                          ->where('status', 'active');
                })
            ],
            'raw_materials.*.unit_id' => [
                'nullable',
                'integer',
                'exists:units,id'
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'manufacturing_formula_id.exists' => 'The selected manufacturing formula does not exist or does not belong to your company.',
            'item_id.required' => 'The item field is required.',
            'item_id.exists' => 'The selected item does not exist or does not belong to your company.',
            'produced_quantity.required' => 'The produced quantity field is required.',
            'produced_quantity.min' => 'The produced quantity must be greater than 0.',
            'produced_quantity.max' => 'The produced quantity cannot exceed 999,999.9999.',
            'raw_materials_warehouse_id.required' => 'The raw materials warehouse field is required.',
            'raw_materials_warehouse_id.exists' => 'The selected raw materials warehouse does not exist, is inactive, or does not belong to your company.',
            'finished_product_warehouse_id.required' => 'The finished product warehouse field is required.',
            'finished_product_warehouse_id.exists' => 'The selected finished product warehouse does not exist, is inactive, or does not belong to your company.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'labor_cost.numeric' => 'The labor cost must be a valid number.',
            'labor_cost.min' => 'The labor cost cannot be negative.',
            'labor_cost.max' => 'The labor cost cannot exceed 999,999.99.',
            'overhead_cost.numeric' => 'The overhead cost must be a valid number.',
            'overhead_cost.min' => 'The overhead cost cannot be negative.',
            'overhead_cost.max' => 'The overhead cost cannot exceed 999,999.99.',
            'notes.max' => 'The notes field cannot exceed 1000 characters.',
            'batch_number.max' => 'The batch number cannot exceed 100 characters.',
            'production_order_number.max' => 'The production order number cannot exceed 100 characters.',
            'raw_materials.array' => 'The raw materials must be an array.',
            'raw_materials.*.item_id.required_with' => 'Each raw material must have an item selected.',
            'raw_materials.*.item_id.exists' => 'One or more selected raw material items do not exist or do not belong to your company.',
            'raw_materials.*.consumed_quantity.required_with' => 'Each raw material must have a consumed quantity specified.',
            'raw_materials.*.consumed_quantity.min' => 'Each raw material consumed quantity must be greater than 0.',
            'raw_materials.*.consumed_quantity.max' => 'Each raw material consumed quantity cannot exceed 999,999.9999.',
            'raw_materials.*.unit_cost.numeric' => 'Each raw material unit cost must be a valid number.',
            'raw_materials.*.unit_cost.min' => 'Each raw material unit cost cannot be negative.',
            'raw_materials.*.unit_cost.max' => 'Each raw material unit cost cannot exceed 999,999.99.',
            'raw_materials.*.warehouse_id.exists' => 'One or more selected raw material warehouses do not exist, are inactive, or do not belong to your company.',
            'raw_materials.*.unit_id.exists' => 'One or more selected raw material units do not exist.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'manufacturing_formula_id' => 'manufacturing formula',
            'item_id' => 'item',
            'manufacturing_duration' => 'manufacturing duration',
            'manufacturing_duration_unit' => 'duration unit',
            'produced_quantity' => 'produced quantity',
            'raw_materials_warehouse_id' => 'raw materials warehouse',
            'finished_product_warehouse_id' => 'finished product warehouse',
            'process_date' => 'process date',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'labor_cost' => 'labor cost',
            'overhead_cost' => 'overhead cost',
            'batch_number' => 'batch number',
            'production_order_number' => 'production order number',
            'raw_materials.*.item_id' => 'raw material item',
            'raw_materials.*.consumed_quantity' => 'raw material consumed quantity',
            'raw_materials.*.unit_cost' => 'raw material unit cost',
            'raw_materials.*.warehouse_id' => 'raw material warehouse',
            'raw_materials.*.unit_id' => 'raw material unit',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation logic
            $this->validateWarehouseDifference($validator);
            $this->validateRawMaterialsUniqueness($validator);
            $this->validateDateLogic($validator);
            $this->validateStatusTransition($validator);
        });
    }

    /**
     * Validate that raw materials and finished product warehouses are different.
     */
    protected function validateWarehouseDifference($validator)
    {
        $rawWarehouse = $this->input('raw_materials_warehouse_id');
        $finishedWarehouse = $this->input('finished_product_warehouse_id');

        if ($rawWarehouse && $finishedWarehouse && $rawWarehouse === $finishedWarehouse) {
            $validator->errors()->add(
                'finished_product_warehouse_id',
                'The finished product warehouse must be different from the raw materials warehouse.'
            );
        }
    }

    /**
     * Validate that raw materials don't have duplicate items.
     */
    protected function validateRawMaterialsUniqueness($validator)
    {
        $rawMaterials = $this->input('raw_materials', []);
        $itemIds = array_column($rawMaterials, 'item_id');
        
        if (count($itemIds) !== count(array_unique($itemIds))) {
            $validator->errors()->add(
                'raw_materials',
                'Raw materials cannot contain duplicate items.'
            );
        }
    }

    /**
     * Validate date logic.
     */
    protected function validateDateLogic($validator)
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $processDate = $this->input('process_date');

        if ($processDate && $startDate && $processDate > $startDate) {
            $validator->errors()->add(
                'start_date',
                'The start date cannot be before the process date.'
            );
        }

        if ($processDate && $endDate && $processDate > $endDate) {
            $validator->errors()->add(
                'end_date',
                'The end date cannot be before the process date.'
            );
        }
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

        // Get current process to check current status
        $processId = $this->route('id');
        if ($processId) {
            $currentProcess = \Modules\Inventory\Models\ManufacturingProcess::find($processId);
            
            if ($currentProcess) {
                $currentStatus = $currentProcess->status;
                
                // Define allowed status transitions
                $allowedTransitions = [
                    'draft' => ['in_progress', 'cancelled'],
                    'in_progress' => ['completed', 'cancelled'],
                    'completed' => [], // Cannot change from completed
                    'cancelled' => ['draft'], // Can restart from cancelled
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
}
