<?php

namespace Modules\Billing\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company' => $this->company?->name,
            'branch' => $this->branch?->name,
            'currency' => $this->currency?->code,
            'employee' => $this->employee?->name,
            'name' => $this->name,
            'type' => $this->type,
            'code' => $this->code,
            'max_documents' => $this->max_documents,
            'current_number' => $this->current_number,
            'status' => $this->status,
            'notes' => $this->notes,
            'financial_journal' => $this->financialJournal?->name,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'created_by' => $this->creator?->name,
            'updated_by' => $this->updater?->name,
            'deleted_by' => $this->deleter?->name,
        ];
    }
}
