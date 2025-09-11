<?php

namespace Modules\HumanResources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id'           => 'required|exists:users,id',
            'company_id'        => 'required|integer',
            'branch_id'         => 'required|integer',
            'fiscal_year_id'    => 'required|integer',
            'employee_id'       => 'required|exists:employees,id',
            'leave_type_id'     => 'required|exists:leave_types,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'days_count'        => 'required|integer|min:1',
            'previous_balance'  => 'nullable|integer|min:0',
            'deducted'          => 'nullable|integer|min:0',
            'remaining_balance' => 'nullable|integer|min:0',
            'notes'             => 'nullable|string',
            'status'            => 'required|in:pending,approved,rejected,cancelled',
            'approved_at'       => 'nullable|date',
            'approved_by'       => 'nullable|exists:users,id',
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
