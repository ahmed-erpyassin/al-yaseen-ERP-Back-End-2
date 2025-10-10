<?php

namespace Modules\HumanResources\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'basic_salary' => $this->basic_salary,
            'allowances' => $this->allowances,
            'deductions' => $this->deductions,
            'overtime_hours' => $this->overtime_hours,
            'overtime_rate' => $this->overtime_rate,
            'overtime_amount' => $this->overtime_amount,
            'gross_salary' => $this->gross_salary,
            'net_salary' => $this->net_salary,
            'tax_amount' => $this->tax_amount,
            'insurance_amount' => $this->insurance_amount,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'employee_number' => $this->employee->employee_number,
                    'full_name' => $this->employee->full_name,
                    'first_name' => $this->employee->first_name,
                    'last_name' => $this->employee->last_name,
                    'email' => $this->employee->email,
                    'phone' => $this->employee->phone,
                    'department' => $this->employee->department ? [
                        'id' => $this->employee->department->id,
                        'name' => $this->employee->department->name,
                    ] : null,
                    'job_title' => $this->employee->jobTitle ? [
                        'id' => $this->employee->jobTitle->id,
                        'name' => $this->employee->jobTitle->name,
                    ] : null,
                ];
            }),

            'payroll_record' => $this->whenLoaded('payrollRecord', function () {
                return [
                    'id' => $this->payrollRecord->id,
                    'payroll_number' => $this->payrollRecord->payroll_number,
                    'date' => $this->payrollRecord->date,
                    'status' => $this->payrollRecord->status,
                ];
            }),

            // Computed fields
            'formatted_basic_salary' => number_format($this->basic_salary, 2),
            'formatted_allowances' => number_format($this->allowances, 2),
            'formatted_deductions' => number_format($this->deductions, 2),
            'formatted_overtime_amount' => number_format($this->overtime_amount, 2),
            'formatted_gross_salary' => number_format($this->gross_salary, 2),
            'formatted_net_salary' => number_format($this->net_salary, 2),
            'formatted_tax_amount' => number_format($this->tax_amount, 2),
            'formatted_insurance_amount' => number_format($this->insurance_amount, 2),
        ];
    }
}
