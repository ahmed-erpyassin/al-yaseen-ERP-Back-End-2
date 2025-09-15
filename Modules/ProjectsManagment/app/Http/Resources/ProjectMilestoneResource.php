<?php

namespace Modules\ProjectsManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMilestoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'completion_percentage' => $this->completion_percentage,
            
            // Dates
            'due_date' => $this->due_date,
            'completed_date' => $this->completed_date,
            
            // Additional Information
            'notes' => $this->notes,
            'deliverables' => $this->deliverables,
            
            // Relationships
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_number' => $this->project->project_number,
                    'name' => $this->project->name,
                    'status' => $this->project->status,
                ];
            }),
            
            // System Information
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
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            
            // Computed Properties
            'completion_display' => $this->completion_percentage . '%',
            'status_label' => $this->getStatusLabel(),
            'days_remaining' => $this->getDaysRemaining(),
            'is_overdue' => $this->isOverdue(),
            'is_completed' => $this->status === 'completed',
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }
    
    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        $statusLabels = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'on_hold' => 'On Hold',
        ];
        
        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }
    
    /**
     * Get days remaining until due date.
     */
    private function getDaysRemaining(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }
    
    /**
     * Check if milestone is overdue.
     */
    private function isOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }
        
        return now()->isAfter($this->due_date);
    }
    
    /**
     * Check if the current user can edit this milestone.
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
     * Check if the current user can delete this milestone.
     */
    private function canDelete(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }
        
        // User can delete if they belong to the same company, the record is not deleted,
        // and the milestone is not completed
        return $user->company_id === $this->company_id && 
               is_null($this->deleted_at) && 
               $this->status !== 'completed';
    }
    
    /**
     * Get additional meta information when requested.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'project_milestone',
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
                'resource_type' => 'project_milestone_collection',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
                'status_options' => [
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'on_hold' => 'On Hold',
                ],
            ],
        ]);
    }
}
