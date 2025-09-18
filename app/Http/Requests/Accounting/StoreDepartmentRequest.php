<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
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
            'company_id'          => 'required|exists:companies,id',
            'user_id'             => 'required|exists:users,id',
            'manager'             => 'required|string|max:255',
            'address'             => 'required|string|max:255',
            'work_phone'          => 'required|string|max:20',
            'home_phone'          => 'required|string|max:20',
            'fax'                 => 'required|string|max:20',
            'description'         => 'required|string',
            'description_en'      => 'required|string',
            'funder_id'           => 'required|exists:funders,id',
            'parent_id'           => 'required|integer|exists:departments,id',
            'status'              => 'required|in:0,1,2,3,4,5',
            'expected_start_date' => 'required|date',
            'expected_end_date'   => 'required|date',
            'actual_start_date'   => 'required|date',
            'actual_end_date'     => 'required|date',
            'budget_id'           => 'required|exists:budgets,id',
            'notes'               => 'required|string',
        ];
    }
}
