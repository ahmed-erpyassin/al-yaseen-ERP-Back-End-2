<?php

namespace Modules\FinancialAccounts\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'journal_entry_id' => $this->journal_entry_id,
            'currency_id' => $this->currency_id,
            'account_id' => $this->account_id,
            'cost_center_id' => $this->cost_center_id,
            'project_id' => $this->project_id,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'exchange_rate' => $this->exchange_rate,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'account' => $this->whenLoaded('account'),
            'currency' => $this->whenLoaded('currency'),
            'fiscal_year' => $this->whenLoaded('fiscalYear'),
        ];
    }
}
