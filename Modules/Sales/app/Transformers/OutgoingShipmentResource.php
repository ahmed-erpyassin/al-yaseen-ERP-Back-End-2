<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class OutgoingShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'currency_id' => $this->currency_id,
            'employee_id' => $this->employee_id,
            'customer_id' => $this->customer_id,
            'journal_id' => $this->journal_id,
            'journal_number' => $this->journal_number,
            'type' => $this->type,
            'status' => $this->status,
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'allowed_discount' => $this->allowed_discount,
            'total_without_tax' => $this->total_without_tax,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'remaining_balance' => $this->remaining_balance,
            'exchange_rate' => $this->exchange_rate,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
