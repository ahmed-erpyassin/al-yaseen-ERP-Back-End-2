<?php

namespace Modules\HumanResources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_id'           => 'required|integer|exists:companies,id',
            'user_id'              => 'required|integer|exists:users,id',
            'branch_id'            => 'required|integer|exists:branches,id',
            'fiscal_year_id'       => 'required|integer|exists:fiscal_years,id',
            'name'                 => 'nullable|string|max:255',
            'number'               => 'required|integer',
            'manager_id'           => 'required|integer|exists:users,id',
            'address'              => 'required|string',
            'work_phone'           => 'nullable|string|max:25',
            'home_phone'           => 'nullable|string|max:25',
            'fax'                  => 'nullable|string|max:50',
            'statement'            => 'nullable|string|max:150',
            'statement_en'         => 'nullable|string|max:150',
            'parent_id'            => 'nullable|integer|exists:projects,id',
            'funder_id'            => 'nullable|integer|exists:funders,id',
            'project_status'       => 'required|in:not_started,inprogress,completed,paused,canceled',
            'status'               => 'required|in:active,inactive',
            'proposed_start_date'  => 'nullable|date',
            'proposed_end_date'    => 'nullable|date',
            'actual_start_date'    => 'nullable|date',
            'actual_end_date'      => 'nullable|date',
            'budget_id'            => 'nullable|integer|exists:budgets,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
