<?php

namespace Modules\Billing\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type,
            'company' => $this->company?->name,
            'branch' => $this->branch?->name,
            'currency' => $this->currency?->code,
            'exchange_rate' => $this->exchange_rate,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax_total' => $this->tax_total,
            'total' => $this->total,
            'status' => $this->status,
            'lines' => InvoiceLineResource::collection($this->whenLoaded('lines')),
        ];
    }
}
