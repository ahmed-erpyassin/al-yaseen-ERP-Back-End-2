<?php

namespace Modules\HumanResources\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'company_id'        => $this->company_id,
            'branch_id'         => $this->branch_id,
            'fiscal_year_id'    => $this->fiscal_year_id,
            'employee_id'       => $this->employee_id,
            'leave_type_id'     => $this->leave_type_id,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'days_count'        => $this->days_count,
            'previous_balance'  => $this->previous_balance,
            'deducted'          => $this->deducted,
            'remaining_balance' => $this->remaining_balance,
            'notes'             => $this->notes,
            'status'            => $this->status,
            'approved_at'       => $this->approved_at,
            'approved_by'       => $this->approved_by,
            'created_by'        => $this->created_by,
            'updated_by'        => $this->updated_by,
            'deleted_by'        => $this->deleted_by,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
