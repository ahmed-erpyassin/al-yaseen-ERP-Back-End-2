<?php

namespace Modules\FinancialAccounts\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalFinancialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'code'         => $this->code,
            'name'         => $this->name,
            'status'       => $this->status,
            'fiscal_year'  => $this->fiscalYear?->name,
            'company_id'   => $this->company_id,
            'branch_id'    => $this->branch_id,
        ];
    }
}
