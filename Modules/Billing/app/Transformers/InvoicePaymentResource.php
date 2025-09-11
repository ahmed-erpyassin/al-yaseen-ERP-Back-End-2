<?php

namespace Modules\Billing\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'invoice_id'    => $this->invoice_id,
            'invoice_number' => $this->invoice?->invoice_number,
            'payment_date'  => $this->payment_date,
            'payment_method' => $this->payment_method,
            'amount'        => $this->amount,
            'currency_id'   => $this->currency_id,
            'exchange_rate' => $this->exchange_rate,
            'reference'     => $this->reference,
            'created_at'    => $this->created_at,
        ];
    }
}
