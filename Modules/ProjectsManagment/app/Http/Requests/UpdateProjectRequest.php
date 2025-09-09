<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
        $projectId = $this->route('id');
        
        return [
            // Foreign Keys (optional for update)
            'company_id' => 'sometimes|exists:companies,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'fiscal_year_id' => 'sometimes|exists:fiscal_years,id',
            'cost_center_id' => 'sometimes|exists:cost_centers,id',
            
            // Customer Information
            'customer_id' => 'sometimes|exists:customers,id',
            
            // Currency Information
            'currency_id' => 'sometimes|exists:currencies,id',
            'currency_price' => 'sometimes|numeric|min:0',
            'include_vat' => 'sometimes|boolean',
            
            // Project Basic Information
            'project_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('projects', 'project_number')->ignore($projectId)
            ],
            'name' => 'sometimes|string|max:255', // Project Name
            'description' => 'sometimes|string',
            'project_value' => 'sometimes|numeric|min:0',
            
            // Project Manager
            'manager_id' => 'sometimes|exists:users,id',
            'project_manager_name' => 'sometimes|string|max:255',
            
            // Dates
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            
            // Status
            'status' => 'sometimes|in:draft,open,on-hold,cancelled,closed',
            
            // Location
            'country_id' => 'sometimes|exists:countries,id',
            
            // Additional Information
            'notes' => 'sometimes|string',
            
            // Auto-populated customer fields (optional for update)
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'customer_phone' => 'sometimes|string|max:20',
            'licensed_operator' => 'sometimes|string|max:255',
            
            // Budget and progress
            'budget' => 'sometimes|numeric|min:0',
            'actual_cost' => 'sometimes|numeric|min:0',
            'progress' => 'sometimes|numeric|min:0|max:100',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Selected customer does not exist.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'currency_price.numeric' => 'Currency price must be a valid number.',
            'currency_price.min' => 'Currency price must be greater than or equal to 0.',
            'name.string' => 'Project name must be a string.',
            'project_value.numeric' => 'Project value must be a valid number.',
            'project_value.min' => 'Project value must be greater than or equal to 0.',
            'manager_id.exists' => 'Selected project manager does not exist.',
            'start_date.date' => 'Start date must be a valid date.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after' => 'End date must be after the start date.',
            'status.in' => 'Invalid project status selected.',
            'country_id.exists' => 'Selected country does not exist.',
            'project_number.unique' => 'This project number is already taken.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget must be greater than or equal to 0.',
            'actual_cost.numeric' => 'Actual cost must be a valid number.',
            'actual_cost.min' => 'Actual cost must be greater than or equal to 0.',
            'progress.numeric' => 'Progress must be a valid number.',
            'progress.min' => 'Progress must be at least 0.',
            'progress.max' => 'Progress cannot exceed 100.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'Customer',
            'currency_id' => 'Currency',
            'currency_price' => 'Currency Price',
            'include_vat' => 'Include VAT',
            'project_number' => 'Project Number',
            'name' => 'Project Name',
            'project_value' => 'Project Value',
            'manager_id' => 'Project Manager',
            'project_manager_name' => 'Project Manager Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'country_id' => 'Country',
            'customer_name' => 'Customer Name',
            'customer_email' => 'Customer Email',
            'customer_phone' => 'Customer Phone',
            'licensed_operator' => 'Licensed Operator',
            'actual_cost' => 'Actual Cost',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert boolean values
        if ($this->has('include_vat')) {
            $this->merge([
                'include_vat' => $this->boolean('include_vat'),
            ]);
        }
    }
}
