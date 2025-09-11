<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectFinancialRequest extends FormRequest
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
        $projectFinancialId = $this->route('id') ?? $this->route('project_financial');
        
        return [
            // Foreign Keys (optional for update)
            'project_id' => 'sometimes|exists:projects,id',
            'currency_id' => 'sometimes|exists:currencies,id',
            
            // Financial fields
            'exchange_rate' => 'sometimes|numeric|min:0.0001|max:999999.9999',
            'reference_type' => 'sometimes|string|max:255',
            'reference_id' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0|max:999999999999.99',
            'date' => 'sometimes|date',
            'description' => 'nullable|string|max:1000',
            
            // System fields (auto-populated)
            'updated_by' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'project_id.exists' => 'Selected project does not exist.',
            'currency_id.exists' => 'Selected currency does not exist.',
            'exchange_rate.numeric' => 'Exchange rate must be a valid number.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'exchange_rate.max' => 'Exchange rate cannot exceed 999,999.9999.',
            'reference_type.string' => 'Reference type must be a string.',
            'reference_type.max' => 'Reference type cannot exceed 255 characters.',
            'reference_id.string' => 'Reference ID must be a string.',
            'reference_id.max' => 'Reference ID cannot exceed 255 characters.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'amount.max' => 'Amount cannot exceed 999,999,999,999.99.',
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
                'updated_by' => $this->user()->id,
            ]);
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

            // Custom validation: Check for duplicate reference within the same project (excluding current record)
            if ($this->project_id && $this->reference_type && $this->reference_id) {
                $projectFinancialId = $this->route('id') ?? $this->route('project_financial');
                
                $exists = \Modules\ProjectsManagment\Models\ProjectFinancial::where('project_id', $this->project_id)
                    ->where('reference_type', $this->reference_type)
                    ->where('reference_id', $this->reference_id)
                    ->where('id', '!=', $projectFinancialId)
                    ->exists();
                
                if ($exists) {
                    $validator->errors()->add('reference_id', 'This reference ID already exists for the selected project and reference type.');
                }
            }
        });
    }
}
