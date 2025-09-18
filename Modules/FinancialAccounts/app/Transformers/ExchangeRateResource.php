<?php

namespace Modules\FinancialAccounts\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'currency'    => new CurrencyResource($this->whenLoaded('currency')),
            'rate_date'   => $this->rate_date,
            'rate'        => $this->rate,

            'company_id'  => $this->company_id,
            'branch_id'   => $this->branch_id,

            'created_by'  => $this->created_by,
            'updated_by'  => $this->updated_by,
            'deleted_by'  => $this->deleted_by,

            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}
