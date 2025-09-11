<?php

namespace Modules\Billing\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item' => $this->item?->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit?->name,
            'unit_price' => $this->unit_price,
            'discount' => $this->discount,
            'tax_id' => $this->tax_id,
            'total' => $this->total,
        ];
    }
}
