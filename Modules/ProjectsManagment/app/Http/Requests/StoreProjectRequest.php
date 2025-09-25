<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProjectRequest extends FormRequest
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
            // Required Foreign Keys
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'required|exists:branches,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'cost_center_id' => 'required|exists:cost_centers,id',
            
            // Customer Information (required)
            'customer_id' => 'required|exists:customers,id',
            
            // Currency Information (required)
            'currency_id' => 'required|exists:currencies,id',
            'currency_price' => 'required|numeric|min:0',
            'include_vat' => 'boolean',
            
            // Project Basic Information
            'project_number' => 'nullable|string|max:255|unique:projects,project_number',
            'name' => 'required|string|max:255', // Project Name
            'description' => 'nullable|string',
            'project_value' => 'required|numeric|min:0',
            
            // Project Manager
            'manager_id' => 'required|exists:users,id',
            'project_manager_name' => 'nullable|string|max:255',
            
            // Dates
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            
            // Status
            'status' => 'required|in:draft,open,on-hold,cancelled,closed',
            
            // Location
            'country_id' => 'required|exists:countries,id',
            
            // Additional Information
            'notes' => 'nullable|string',
            
            // Auto-populated customer fields (will be filled automatically)
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'licensed_operator' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer selection is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'currency_id.required' => 'Currency selection is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'currency_price.required' => 'Currency price is required.',
            'currency_price.numeric' => 'Currency price must be a valid number.',
            'currency_price.min' => 'Currency price must be greater than or equal to 0.',
            'name.required' => 'Project name is required.',
            'project_value.required' => 'Project value is required.',
            'project_value.numeric' => 'Project value must be a valid number.',
            'project_value.min' => 'Project value must be greater than or equal to 0.',
            'manager_id.required' => 'Project manager selection is required.',
            'manager_id.exists' => 'Selected project manager does not exist.',
            'start_date.required' => 'Project start date is required.',
            'start_date.after_or_equal' => 'Project start date must be today or later.',
            'end_date.required' => 'Project end date is required.',
            'end_date.after' => 'Project end date must be after the start date.',
            'status.required' => 'Project status is required.',
            'status.in' => 'Invalid project status selected.',
            'country_id.required' => 'Country selection is required.',
            'country_id.exists' => 'Selected country does not exist.',
            'project_number.unique' => 'This project number is already taken.',
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
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default values
        $this->merge([
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id ?? $this->company_id,
            'include_vat' => $this->boolean('include_vat'),
        ]);
    }
}
