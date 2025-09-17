<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectFinancialRequest extends FormRequest
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
            'project_id' => 'required|exists:projects,id',
            'currency_id' => 'required|exists:currencies,id',
            
            // Financial fields
            'exchange_rate' => 'required|numeric|min:0.0001|max:999999.9999',
            'reference_type' => 'required|string|max:255',
            'reference_id' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:999999999999.99',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            
            // System fields (auto-populated)
            'user_id' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'Selected project does not exist.',
            'currency_id.required' => 'Currency is required.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'exchange_rate.required' => 'Exchange rate is required.',
            'exchange_rate.numeric' => 'Exchange rate must be a valid number.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'exchange_rate.max' => 'Exchange rate cannot exceed 999,999.9999.',
            'reference_type.required' => 'Reference type is required.',
            'reference_type.string' => 'Reference type must be a string.',
            'reference_type.max' => 'Reference type cannot exceed 255 characters.',
            'reference_id.required' => 'Reference ID is required.',
            'reference_id.string' => 'Reference ID must be a string.',
            'reference_id.max' => 'Reference ID cannot exceed 255 characters.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'amount.max' => 'Amount cannot exceed 999,999,999,999.99.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'description.string' => 'Description must be a string.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'project',
            'currency_id' => 'currency',
            'exchange_rate' => 'exchange rate',
            'reference_type' => 'reference type',
            'reference_id' => 'reference ID',
            'amount' => 'amount',
            'date' => 'date',
            'description' => 'description',
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
                'updated_by' => $this->user()->id,
            ]);
        }

        // Set default exchange rate if not provided
        if (!$this->has('exchange_rate') || empty($this->exchange_rate)) {
            $this->merge(['exchange_rate' => 1.0000]);
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

            // Custom validation: Check if currency exists and is active
            if ($this->currency_id) {
                $currency = \Modules\FinancialAccounts\Models\Currency::find($this->currency_id);
                if ($currency && isset($currency->active) && !$currency->active) {
                    $validator->errors()->add('currency_id', 'Selected currency is not active.');
                }
            }

            // Custom validation: Ensure date is not in the future
            if ($this->date && \Carbon\Carbon::parse($this->date)->isFuture()) {
                $validator->errors()->add('date', 'Date cannot be in the future.');
            }

            // Custom validation: Check for duplicate reference within the same project
            if ($this->project_id && $this->reference_type && $this->reference_id) {
                $exists = \Modules\ProjectsManagment\Models\ProjectFinancial::where('project_id', $this->project_id)
                    ->where('reference_type', $this->reference_type)
                    ->where('reference_id', $this->reference_id)
                    ->exists();
                
                if ($exists) {
                    $validator->errors()->add('reference_id', 'This reference ID already exists for the selected project and reference type.');
                }
            }
        });
    }
}
