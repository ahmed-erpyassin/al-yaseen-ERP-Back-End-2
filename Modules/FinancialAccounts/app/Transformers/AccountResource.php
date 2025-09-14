<?php

namespace Modules\FinancialAccounts\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'code'       => $this->code,
            'name'       => $this->name,
            'type'       => $this->type,
            'currency'   => $this->currency?->code,
            'fiscalYear' => $this->fiscalYear?->name,
            'group'      => $this->group?->name,
        ];
    }
}
