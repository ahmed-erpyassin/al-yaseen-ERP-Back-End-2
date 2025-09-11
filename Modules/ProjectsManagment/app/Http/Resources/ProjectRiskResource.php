<?php

namespace Modules\ProjectsManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectRiskResource extends JsonResource
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
            'project_id' => $this->project_id,
            'assigned_to' => $this->assigned_to,
            
            // Risk Details
            'title' => $this->title,
            'description' => $this->description,
            'impact' => $this->impact,
            'probability' => $this->probability,
            'mitigation_plan' => $this->mitigation_plan,
            'status' => $this->status,
            
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
                    'status' => $this->project->status,
                    'display_name' => $this->project->project_number . ' - ' . $this->project->name,
                ];
            }),
            
            'assigned_employee' => $this->whenLoaded('assignedEmployee', function () {
                if (!$this->assignedEmployee) {
                    return null;
                }
                
                $fullName = trim($this->assignedEmployee->first_name . ' ' . 
                               $this->assignedEmployee->second_name . ' ' . 
                               $this->assignedEmployee->third_name);
                
                return [
                    'id' => $this->assignedEmployee->id,
                    'employee_number' => $this->assignedEmployee->employee_number,
                    'first_name' => $this->assignedEmployee->first_name,
                    'second_name' => $this->assignedEmployee->second_name,
                    'third_name' => $this->assignedEmployee->third_name,
                    'full_name' => $fullName,
                    'display_name' => $this->assignedEmployee->employee_number . ' - ' . $fullName,
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
            'impact_level' => $this->impact_level,
            'probability_level' => $this->probability_level,
            'risk_score' => $this->risk_score,
            'risk_level' => $this->risk_level,
            'status_color' => $this->status_color,
            
            // Formatted Fields
            'formatted_created_at' => $this->created_at?->format('d/m/Y H:i'),
            'formatted_updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'impact_label' => ucfirst($this->impact),
            'probability_label' => ucfirst($this->probability),
            'status_label' => ucfirst($this->status),
            
            // Status indicators
            'is_deleted' => !is_null($this->deleted_at),
            'is_high_risk' => $this->risk_level === 'High',
            'is_open' => $this->status === 'open',
            'is_assigned' => !is_null($this->assigned_to),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
            
            // Additional computed fields
            'days_since_created' => $this->created_at ? $this->created_at->diffInDays(now()) : null,
            'has_mitigation_plan' => !empty($this->mitigation_plan),
            'risk_priority' => $this->getRiskPriority(),
        ];
    }

    /**
     * Get risk priority based on impact and probability.
     */
    private function getRiskPriority(): string
    {
        $score = $this->risk_score;
        
        if ($score >= 9) return 'Critical';
        if ($score >= 6) return 'High';
        if ($score >= 3) return 'Medium';
        return 'Low';
    }

    /**
     * Check if the current user can edit this project risk.
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
     * Check if the current user can delete this project risk.
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
                'resource_type' => 'project_risk',
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
                'resource_type' => 'project_risk_collection',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
                'risk_levels' => [
                    'low' => 'Low Risk (1-2)',
                    'medium' => 'Medium Risk (3-6)', 
                    'high' => 'High Risk (7-9)',
                ],
            ],
        ]);
    }
}
