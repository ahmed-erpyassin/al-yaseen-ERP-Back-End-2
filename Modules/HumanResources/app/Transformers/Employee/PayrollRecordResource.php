<?php

namespace Modules\HumanResources\Transformers\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Core information
            'payroll_number' => $this->payroll_number,
            'date' => $this->date?->format('Y-m-d'),
            'second_date' => $this->second_date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            
            // Currency information
            'currency' => [
                'id' => $this->currency_id,
                'code' => $this->currency?->code,
                'name' => $this->currency?->name,
                'symbol' => $this->currency?->symbol,
                'rate' => $this->currency_rate,
            ],
            
            // Account information
            'account' => [
                'id' => $this->account_id,
                'number' => $this->account_number,
                'name' => $this->account_name,
                'full_account' => $this->account ? [
                    'id' => $this->account->id,
                    'code' => $this->account->code,
                    'name' => $this->account->name,
                    'type' => $this->account->type,
                ] : null,
            ],
            
            // Payment details
            'payment_account' => $this->payment_account,
            'salaries_wages_period' => $this->salaries_wages_period,
            
            // Calculated totals
            'totals' => [
                'total_salaries' => (float) $this->total_salaries,
                'total_income_tax_deductions' => (float) $this->total_income_tax_deductions,
                'total_payable_amount' => (float) $this->total_payable_amount,
                'total_salaries_paid_cash' => (float) $this->total_salaries_paid_cash,
                'net_amount' => (float) ($this->total_payable_amount - $this->total_salaries_paid_cash),
            ],
            
            // Company information
            'company' => [
                'id' => $this->company_id,
                'name' => $this->company?->name,
            ],
            
            // Branch information
            'branch' => [
                'id' => $this->branch_id,
                'name' => $this->branch?->name,
            ],
            
            // Fiscal year information
            'fiscal_year' => [
                'id' => $this->fiscal_year_id,
                'name' => $this->fiscalYear?->name,
                'start_date' => $this->fiscalYear?->start_date?->format('Y-m-d'),
                'end_date' => $this->fiscalYear?->end_date?->format('Y-m-d'),
            ],
            
            // Payroll data summary
            'payroll_data_summary' => [
                'total_employees' => $this->payrollData?->count() ?? 0,
                'active_employees' => $this->payrollData?->where('status', 'active')->count() ?? 0,
                'inactive_employees' => $this->payrollData?->where('status', 'inactive')->count() ?? 0,
            ],
            
            // Payroll data (when loaded)
            'payroll_data' => PayrollDataResource::collection($this->whenLoaded('payrollData')),
            
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
                'can_edit' => $this->status === 'draft',
                'can_delete' => $this->status === 'draft',
                'can_approve' => $this->status === 'draft',
                'can_pay' => $this->status === 'approved',
                'is_editable' => in_array($this->status, ['draft']),
                'formatted_date' => $this->date?->format('F j, Y'),
                'formatted_second_date' => $this->second_date?->format('F j, Y'),
                'period_display' => $this->salaries_wages_period ?: 
                    ($this->date ? "Salaries and wages for " . $this->date->format('F Y') : null),
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
                'resource_type' => 'payroll_record',
                'version' => '1.0',
            ],
        ];
    }
}
