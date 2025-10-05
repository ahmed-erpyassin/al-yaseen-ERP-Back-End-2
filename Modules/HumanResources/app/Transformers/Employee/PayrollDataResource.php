<?php

namespace Modules\HumanResources\Transformers\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Payroll record reference
            'payroll_record_id' => $this->payroll_record_id,
            
            // Employee information
            'employee' => [
                'id' => $this->employee_id,
                'number' => $this->employee_number,
                'name' => $this->employee_name,
                'national_id' => $this->national_id,
                'marital_status' => $this->marital_status,
                'job_title' => $this->job_title,
                'duration' => $this->duration,
                'full_employee' => $this->employee ? [
                    'id' => $this->employee->id,
                    'employee_number' => $this->employee->employee_number,
                    'full_name' => $this->employee->full_name,
                    'first_name' => $this->employee->first_name,
                    'last_name' => $this->employee->last_name,
                    'email' => $this->employee->email,
                    'phone1' => $this->employee->phone1,
                    'hire_date' => $this->employee->hire_date?->format('Y-m-d'),
                    'department' => [
                        'id' => $this->employee->department_id,
                        'name' => $this->employee->department?->name,
                    ],
                    'job_title_info' => [
                        'id' => $this->employee->job_title_id,
                        'name' => $this->employee->jobTitle?->name,
                    ],
                ] : null,
            ],
            
            // Salary information
            'salary' => [
                'basic_salary' => (float) $this->basic_salary,
                'allowances' => (float) $this->allowances,
                'deductions' => (float) $this->deductions,
                'income_tax' => (float) $this->income_tax,
                'salary_for_payment' => (float) $this->salary_for_payment,
                'paid_in_cash' => (float) $this->paid_in_cash,
                'net_salary' => (float) ($this->salary_for_payment - $this->paid_in_cash),
            ],
            
            // Overtime information
            'overtime' => [
                'hours' => (float) $this->overtime_hours,
                'rate' => (float) $this->overtime_rate,
                'amount' => (float) $this->overtime_amount,
            ],
            
            // Calculated totals
            'calculations' => [
                'gross_salary' => (float) ($this->basic_salary + $this->allowances + $this->overtime_amount),
                'total_deductions' => (float) ($this->income_tax + $this->deductions),
                'net_payable' => (float) $this->salary_for_payment,
                'remaining_balance' => (float) ($this->salary_for_payment - $this->paid_in_cash),
            ],
            
            // Status and notes
            'status' => $this->status,
            'notes' => $this->notes,
            
            // Company context
            'company' => [
                'id' => $this->company_id,
                'name' => $this->company?->name,
            ],
            
            // Audit information
            'audit' => [
                'created_by' => [
                    'id' => $this->created_by,
                    'name' => $this->creator?->name,
                ],
                'updated_by' => [
                    'id' => $this->updated_by,
                    'name' => $this->updater?->name,
                ],
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            ],
            
            // Additional metadata
            'metadata' => [
                'is_active' => $this->status === 'active',
                'has_overtime' => $this->overtime_hours > 0,
                'has_allowances' => $this->allowances > 0,
                'has_deductions' => $this->deductions > 0,
                'has_income_tax' => $this->income_tax > 0,
                'has_cash_payment' => $this->paid_in_cash > 0,
                'tax_percentage' => $this->basic_salary > 0 ? 
                    round(($this->income_tax / $this->basic_salary) * 100, 2) : 0,
                'net_percentage' => $this->basic_salary > 0 ? 
                    round(($this->salary_for_payment / $this->basic_salary) * 100, 2) : 0,
                'formatted_basic_salary' => number_format($this->basic_salary, 2),
                'formatted_salary_for_payment' => number_format($this->salary_for_payment, 2),
                'formatted_paid_in_cash' => number_format($this->paid_in_cash, 2),
                'marital_status_display' => ucfirst($this->marital_status ?? 'single'),
                'duration_display' => $this->duration ?: 'Not specified',
            ],
            
            // Validation flags
            'validation' => [
                'has_required_fields' => !empty($this->employee_id) && !empty($this->basic_salary),
                'salary_calculation_valid' => $this->salary_for_payment == 
                    ($this->basic_salary + $this->allowances + $this->overtime_amount - $this->income_tax - $this->deductions),
                'overtime_calculation_valid' => $this->overtime_amount == 
                    ($this->overtime_hours * $this->overtime_rate),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'payroll_data',
                'version' => '1.0',
            ],
        ];
    }
}
