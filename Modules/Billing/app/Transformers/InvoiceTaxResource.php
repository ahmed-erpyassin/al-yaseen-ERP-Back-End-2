<?php

namespace Modules\Billing\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceTaxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'invoice_id'  => $this->invoice_id,
            'invoice_number' => $this->invoice?->invoice_number,
            'tax_id'      => $this->tax_id,
            'tax_name'    => $this->tax?->name,
            'tax_amount'  => $this->tax_amount,
            'created_at'  => $this->created_at,
        ];
    }
}
