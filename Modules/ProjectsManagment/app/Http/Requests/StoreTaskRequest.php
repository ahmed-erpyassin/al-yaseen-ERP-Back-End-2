<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'task_name' => 'required|string|max:255',
            'assigned_to' => 'required|exists:employees,id',
            'due_date' => 'required|date|after_or_equal:today',
            'status' => 'required|in:to_do,in_progress,done,blocked',

            // Optional fields
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'start_date' => 'nullable|date|before_or_equal:due_date',
            'estimated_hours' => 'nullable|integer|min:1|max:1000',
            'actual_hours' => 'nullable|integer|min:0|max:1000',
            'progress' => 'nullable|numeric|min:0|max:100',

            // Records (links/URLs)
            'records' => 'nullable|array',
            'records.*' => 'nullable|string|url|max:500',

            // System fields (auto-populated)
            'company_id' => 'sometimes|exists:companies,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'fiscal_year_id' => 'sometimes|exists:fiscal_years,id',
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
            'task_name.required' => 'Task name is required.',
            'task_name.max' => 'Task name cannot exceed 255 characters.',
            'assigned_to.required' => 'Assigned employee is required.',
            'assigned_to.exists' => 'Selected employee does not exist.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date must be today or later.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'milestone_id.exists' => 'Selected milestone does not exist.',
            'priority.in' => 'Invalid priority selected.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.before_or_equal' => 'Start date must be before or equal to due date.',
            'estimated_hours.integer' => 'Estimated hours must be a number.',
            'estimated_hours.min' => 'Estimated hours must be at least 1.',
            'estimated_hours.max' => 'Estimated hours cannot exceed 1000.',
            'actual_hours.integer' => 'Actual hours must be a number.',
            'actual_hours.min' => 'Actual hours must be at least 0.',
            'actual_hours.max' => 'Actual hours cannot exceed 1000.',
            'progress.numeric' => 'Progress must be a number.',
            'progress.min' => 'Progress must be at least 0.',
            'progress.max' => 'Progress cannot exceed 100.',
            'records.array' => 'Records must be an array.',
            'records.*.url' => 'Each record must be a valid URL.',
            'records.*.max' => 'Each record URL cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'Project',
            'task_name' => 'Task Name',
            'assigned_to' => 'Assigned To',
            'due_date' => 'Due Date',
            'milestone_id' => 'Milestone',
            'start_date' => 'Start Date',
            'estimated_hours' => 'Estimated Hours',
            'actual_hours' => 'Actual Hours',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $user = $this->user();

        // Auto-populate system fields
        $this->merge([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id ?? 1,
            'fiscal_year_id' => $user->fiscal_year_id ?? 1,
            'created_by' => $user->id,
        ]);

        // Set default values
        if (!$this->has('priority')) {
            $this->merge(['priority' => 'medium']);
        }

        if (!$this->has('progress')) {
            $this->merge(['progress' => 0]);
        }

        // If task_name is provided but title is not, use task_name as title
        if ($this->has('task_name') && !$this->has('title')) {
            $this->merge(['title' => $this->task_name]);
        }

        // Clean up records array (remove empty values)
        if ($this->has('records') && is_array($this->records)) {
            $cleanRecords = array_filter($this->records, function($record) {
                return !empty(trim($record));
            });
            $this->merge(['records' => array_values($cleanRecords)]);
        }
    }
}
