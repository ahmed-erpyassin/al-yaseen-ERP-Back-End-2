<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRiskRequest extends FormRequest
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
        $user = $this->user();
        
        return [
            // Project selection (required)
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                }),
            ],
            
            // Risk details
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            
            'description' => [
                'nullable',
                'string',
                'max:65535',
            ],
            
            // Risk assessment
            'impact' => [
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
            
            'probability' => [
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
            
            // Mitigation and status
            'mitigation_plan' => [
                'nullable',
                'string',
                'max:65535',
            ],
            
            'status' => [
                'required',
                Rule::in(['open', 'mitigated', 'closed']),
            ],
            
            // Employee assignment
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                }),
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Please select a project.',
            'project_id.exists' => 'The selected project does not exist or does not belong to your company.',
            
            'title.required' => 'Project risk title is required.',
            'title.min' => 'Project risk title must be at least 3 characters.',
            'title.max' => 'Project risk title cannot exceed 255 characters.',
            
            'description.max' => 'Description cannot exceed 65535 characters.',
            
            'impact.required' => 'Please select the risk impact level.',
            'impact.in' => 'Impact must be one of: low, medium, high.',
            
            'probability.required' => 'Please select the risk probability level.',
            'probability.in' => 'Probability must be one of: low, medium, high.',
            
            'mitigation_plan.max' => 'Mitigation plan cannot exceed 65535 characters.',
            
            'status.required' => 'Please select the risk status.',
            'status.in' => 'Status must be one of: open, mitigated, closed.',
            
            'assigned_to.exists' => 'The selected employee does not exist or does not belong to your company.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'project',
            'title' => 'risk title',
            'description' => 'risk description',
            'impact' => 'risk impact',
            'probability' => 'risk probability',
            'mitigation_plan' => 'mitigation plan',
            'status' => 'risk status',
            'assigned_to' => 'assigned employee',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            
            // Custom validation: Check if project belongs to user's company
            if ($this->filled('project_id')) {
                $project = \Modules\ProjectsManagment\Models\Project::find($this->project_id);
                if ($project && $project->company_id !== $user->company_id) {
                    $validator->errors()->add('project_id', 'The selected project does not belong to your company.');
                }
            }
            
            // Custom validation: Check if employee belongs to user's company
            if ($this->filled('assigned_to')) {
                $employee = \Modules\HumanResources\Models\Employee::find($this->assigned_to);
                if ($employee && $employee->company_id !== $user->company_id) {
                    $validator->errors()->add('assigned_to', 'The selected employee does not belong to your company.');
                }
            }
            
            // Custom validation: Check for duplicate risk titles within the same project
            if ($this->filled(['project_id', 'title'])) {
                $existingRisk = \Modules\ProjectsManagment\Models\ProjectRisk::where('project_id', $this->project_id)
                    ->where('title', $this->title)
                    ->where('company_id', $user->company_id)
                    ->first();
                    
                if ($existingRisk) {
                    $validator->errors()->add('title', 'A risk with this title already exists for the selected project.');
                }
            }
        });
    }
}
