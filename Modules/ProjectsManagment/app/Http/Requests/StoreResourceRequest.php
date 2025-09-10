<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Required fields
            'project_id' => 'required|exists:projects,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'role' => 'required|string|max:255',
            'resource_type' => 'required|in:supplier,internal,contractor,consultant',
            
            // Optional fields
            'supplier_number' => 'nullable|string|max:100',
            'supplier_name' => 'nullable|string|max:255',
            'project_number' => 'nullable|string|max:100',
            'project_name' => 'nullable|string|max:255',
            'allocation_percentage' => 'nullable|numeric|min:0|max:100',
            'allocation_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:active,inactive,completed',
            
            // Legacy allocation field (for backward compatibility)
            'allocation' => 'nullable|string|max:500',
            
            // System fields (auto-populated)
            'company_id' => 'sometimes|exists:companies,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'fiscal_year_id' => 'sometimes|exists:fiscal_years,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project selection is required.',
            'project_id.exists' => 'Selected project does not exist.',
            'supplier_id.required' => 'Supplier selection is required.',
            'supplier_id.exists' => 'Selected supplier does not exist.',
            'role.required' => 'Role is required.',
            'role.max' => 'Role cannot exceed 255 characters.',
            'resource_type.required' => 'Resource type is required.',
            'resource_type.in' => 'Invalid resource type selected. Must be: supplier, internal, contractor, or consultant.',
            'allocation_percentage.numeric' => 'Allocation percentage must be a number.',
            'allocation_percentage.min' => 'Allocation percentage cannot be less than 0%.',
            'allocation_percentage.max' => 'Allocation percentage cannot exceed 100%.',
            'allocation_value.numeric' => 'Allocation value must be a number.',
            'allocation_value.min' => 'Allocation value cannot be negative.',
            'status.in' => 'Invalid status selected. Must be: active, inactive, or completed.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
            'supplier_number.max' => 'Supplier number cannot exceed 100 characters.',
            'supplier_name.max' => 'Supplier name cannot exceed 255 characters.',
            'project_number.max' => 'Project number cannot exceed 100 characters.',
            'project_name.max' => 'Project name cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'Project',
            'supplier_id' => 'Supplier',
            'role' => 'Role',
            'resource_type' => 'Resource Type',
            'allocation_percentage' => 'Allocation Percentage',
            'allocation_value' => 'Allocation Value',
            'notes' => 'Notes',
            'status' => 'Status',
            'supplier_number' => 'Supplier Number',
            'supplier_name' => 'Supplier Name',
            'project_number' => 'Project Number',
            'project_name' => 'Project Name',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-populate system fields from authenticated user
        if ($this->user()) {
            $this->merge([
                'company_id' => $this->user()->company_id,
                'branch_id' => $this->user()->branch_id,
                'fiscal_year_id' => $this->user()->fiscal_year_id,
                'user_id' => $this->user()->id,
                'created_by' => $this->user()->id,
            ]);
        }

        // Set default values
        if (!$this->has('resource_type') || empty($this->resource_type)) {
            $this->merge(['resource_type' => 'supplier']);
        }

        if (!$this->has('status') || empty($this->status)) {
            $this->merge(['status' => 'active']);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation: Check if project belongs to user's company
            if ($this->project_id && $this->user()) {
                $project = \Modules\ProjectsManagment\Models\Project::find($this->project_id);
                if ($project && $project->company_id !== $this->user()->company_id) {
                    $validator->errors()->add('project_id', 'Selected project does not belong to your company.');
                }
            }

            // Custom validation: Check if supplier belongs to user's company
            if ($this->supplier_id && $this->user()) {
                $supplier = \Modules\Inventory\Models\Supplier::find($this->supplier_id);
                if ($supplier && $supplier->company_id !== $this->user()->company_id) {
                    $validator->errors()->add('supplier_id', 'Selected supplier does not belong to your company.');
                }
            }

            // Custom validation: Ensure either allocation_percentage or allocation_value is provided
            if (!$this->allocation_percentage && !$this->allocation_value) {
                $validator->errors()->add('allocation_percentage', 'Either allocation percentage or allocation value must be provided.');
                $validator->errors()->add('allocation_value', 'Either allocation percentage or allocation value must be provided.');
            }

            // Custom validation: Check if both allocation_percentage and allocation_value are provided, they should be consistent
            if ($this->allocation_percentage && $this->allocation_value && $this->project_id) {
                $project = \Modules\ProjectsManagment\Models\Project::find($this->project_id);
                if ($project && $project->project_value) {
                    $calculatedValue = ($this->allocation_percentage / 100) * $project->project_value;
                    $tolerance = 0.01; // Allow small rounding differences
                    
                    if (abs($calculatedValue - $this->allocation_value) > $tolerance) {
                        $validator->errors()->add('allocation_value', 'Allocation value does not match the calculated value based on percentage and project value.');
                    }
                }
            }
        });
    }
}
