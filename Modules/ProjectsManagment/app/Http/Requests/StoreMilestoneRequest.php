<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'status' => 'required|in:not_started,in_progress,completed',
            
            // Optional fields
            'milestone_number' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'progress' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
            
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
            'name.required' => 'Milestone name is required.',
            'name.max' => 'Milestone name cannot exceed 255 characters.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'status.required' => 'Status selection is required.',
            'status.in' => 'Invalid status selected. Must be: not_started, in_progress, or completed.',
            'progress.numeric' => 'Progress must be a number.',
            'progress.min' => 'Progress cannot be less than 0%.',
            'progress.max' => 'Progress cannot exceed 100%.',
            'milestone_number.integer' => 'Milestone number must be a whole number.',
            'milestone_number.min' => 'Milestone number must be at least 1.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'Project',
            'name' => 'Milestone Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'milestone_number' => 'Milestone Number',
            'description' => 'Description',
            'progress' => 'Progress',
            'notes' => 'Notes',
            'status' => 'Status',
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

        // Set default progress if not provided
        if (!$this->has('progress') || $this->progress === null) {
            $this->merge(['progress' => 0]);
        }

        // Set default status if not provided
        if (!$this->has('status') || empty($this->status)) {
            $this->merge(['status' => 'not_started']);
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

            // Custom validation: Check milestone number uniqueness per project
            if ($this->milestone_number && $this->project_id) {
                $exists = \Modules\ProjectsManagment\Models\ProjectMilestone::where('project_id', $this->project_id)
                    ->where('milestone_number', $this->milestone_number)
                    ->exists();
                
                if ($exists) {
                    $validator->errors()->add('milestone_number', 'This milestone number already exists for the selected project.');
                }
            }
        });
    }
}
