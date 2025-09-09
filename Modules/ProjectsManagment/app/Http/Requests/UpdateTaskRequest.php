<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            // Optional fields for update
            'project_id' => 'sometimes|exists:projects,id',
            'task_name' => 'sometimes|string|max:255',
            'assigned_to' => 'sometimes|exists:users,id',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|in:to_do,in_progress,done,blocked',
            
            // Optional fields
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'notes' => 'sometimes|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'start_date' => 'sometimes|date',
            'estimated_hours' => 'sometimes|integer|min:1|max:1000',
            'actual_hours' => 'sometimes|integer|min:0|max:1000',
            'progress' => 'sometimes|numeric|min:0|max:100',
            
            // Records (links/URLs)
            'records' => 'sometimes|array',
            'records.*' => 'nullable|string|url|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'project_id.exists' => 'Selected project does not exist.',
            'task_name.max' => 'Task name cannot exceed 255 characters.',
            'assigned_to.exists' => 'Selected employee does not exist.',
            'due_date.date' => 'Due date must be a valid date.',
            'status.in' => 'Invalid status selected.',
            'milestone_id.exists' => 'Selected milestone does not exist.',
            'priority.in' => 'Invalid priority selected.',
            'start_date.date' => 'Start date must be a valid date.',
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
        
        // Set updated_by
        $this->merge([
            'updated_by' => $user->id,
        ]);

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

        // Validate date logic
        if ($this->has('start_date') && $this->has('due_date')) {
            $startDate = $this->start_date;
            $dueDate = $this->due_date;
            
            if ($startDate && $dueDate && $startDate > $dueDate) {
                $this->merge(['start_date' => $dueDate]);
            }
        }
    }
}
