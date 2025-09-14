<?php

namespace Modules\FinancialAccounts\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'company_id' => $this->company_id,
            'parent_id'  => $this->parent_id,
            'code'       => $this->code,
            'name'       => $this->name,
            'type'       => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'accounts'   => AccountResource::collection($this->whenLoaded('accounts')),
        ];
    }
}
