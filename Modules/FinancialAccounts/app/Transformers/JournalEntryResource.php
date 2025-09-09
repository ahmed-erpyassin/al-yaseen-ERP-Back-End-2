<?php

namespace Modules\FinancialAccounts\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fiscal_year' => $this->fiscalYear,
            'user' => $this->user,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'journal' => $this->journal,
            'document_id' => $this->document_id,
            'type' => $this->type, // manual, sales, purchase, payment, receipt, adjustment, inventory, production
            'entry_number' => $this->entry_number,
            'entry_date' => $this->entry_date,
            'description' => $this->description,
            'status' => $this->status, // draft, posted, cancelled
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
