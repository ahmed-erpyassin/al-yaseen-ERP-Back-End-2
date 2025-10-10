<?php

namespace Modules\HumanResources\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRecordResource extends JsonResource
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
            'payroll_number' => $this->payroll_number,
            'date' => $this->date,
            'description' => $this->description,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'currency_rate' => $this->currency_rate,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                    'code' => $this->company->code,
                ];
            }),

            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'name' => $this->currency->name,
                    'code' => $this->currency->code,
                    'symbol' => $this->currency->symbol,
                ];
            }),

            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'name' => $this->account->name,
                    'code' => $this->account->code,
                    'type' => $this->account->type,
                ];
            }),

            'payroll_data' => $this->whenLoaded('payrollData', function () {
                return PayrollDataResource::collection($this->payrollData);
            }),

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),

            // Computed fields
            'payroll_data_count' => $this->whenCounted('payrollData'),
            'total_employees' => $this->payrollData->count() ?? 0,
            'status_label' => $this->getStatusLabel(),
            'formatted_date' => $this->date ? $this->date->format('Y-m-d') : null,
            'formatted_total_amount' => number_format($this->total_amount, 2),
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }
}
