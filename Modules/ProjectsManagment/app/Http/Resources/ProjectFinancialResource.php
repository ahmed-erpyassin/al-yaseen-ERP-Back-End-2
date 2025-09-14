<?php

namespace Modules\ProjectsManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectFinancialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Foreign Key IDs
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'currency_id' => $this->currency_id,
            'project_id' => $this->project_id,
            
            // Financial Data
            'exchange_rate' => $this->exchange_rate,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'amount' => $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'description' => $this->description,
            
            // System Fields
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_number' => $this->project->project_number,
                    'name' => $this->project->name,
                    'project_value' => $this->project->project_value,
                    'currency_id' => $this->project->currency_id,
                    'status' => $this->project->status,
                    'display_name' => $this->project->project_number . ' - ' . $this->project->name,
                ];
            }),
            
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'currency_code' => $this->currency->currency_code,
                    'currency_name_ar' => $this->currency->currency_name_ar,
                    'currency_name_en' => $this->currency->currency_name_en,
                    'display_name' => $this->currency->currency_code . ' - ' . ($this->currency->currency_name_ar ?: $this->currency->currency_name_en),
                ];
            }),
            
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            
            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),
            
            'deleter' => $this->whenLoaded('deleter', function () {
                return [
                    'id' => $this->deleter->id,
                    'name' => $this->deleter->name,
                    'email' => $this->deleter->email,
                ];
            }),
            
            // Computed Fields
            'formatted_amount' => $this->formatAmount(),
            'formatted_date' => $this->date?->format('d/m/Y'),
            'formatted_exchange_rate' => number_format($this->exchange_rate, 4),
            'reference_display' => $this->reference_type . ' - ' . $this->reference_id,
            
            // Status indicators
            'is_deleted' => !is_null($this->deleted_at),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Format the amount with currency symbol if available.
     */
    private function formatAmount(): string
    {
        $formattedAmount = number_format($this->amount, 2);
        
        if ($this->relationLoaded('currency') && $this->currency) {
            return $this->currency->currency_code . ' ' . $formattedAmount;
        }
        
        return $formattedAmount;
    }

    /**
     * Check if the current user can edit this project financial.
     */
    private function canEdit(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }
        
        // User can edit if they belong to the same company and the record is not deleted
        return $user->company_id === $this->company_id && is_null($this->deleted_at);
    }

    /**
     * Check if the current user can delete this project financial.
     */
    private function canDelete(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }
        
        // User can delete if they belong to the same company and the record is not deleted
        return $user->company_id === $this->company_id && is_null($this->deleted_at);
    }

    /**
     * Get additional meta information when requested.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'project_financial',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Customize the response for collections.
     */
    public static function collection($resource)
    {
        return parent::collection($resource)->additional([
            'meta' => [
                'resource_type' => 'project_financial_collection',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }
}
