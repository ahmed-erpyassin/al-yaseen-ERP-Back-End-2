<?php

namespace Modules\ProjectsManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
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
            'priority' => $this->priority,
            'progress' => $this->progress,
            
            // Dates
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'completed_date' => $this->completed_date,
            
            // Time Tracking
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            
            // Additional Information
            'notes' => $this->notes,
            'tags' => $this->tags,
            
            // Relationships
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_number' => $this->project->project_number,
                    'name' => $this->project->name,
                    'status' => $this->project->status,
                ];
            }),
            
            'assignedUser' => $this->whenLoaded('assignedUser', function () {
                return [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                ];
            }),
            
            'documents' => $this->whenLoaded('documents', function () {
                return ProjectDocumentResource::collection($this->documents);
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
            'progress_percentage' => $this->progress . '%',
            'status_label' => $this->getStatusLabel(),
            'priority_label' => $this->getPriorityLabel(),
            'days_remaining' => $this->getDaysRemaining(),
            'is_overdue' => $this->isOverdue(),
            'time_variance' => $this->getTimeVariance(),
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
     * Get priority label.
     */
    private function getPriorityLabel(): string
    {
        $priorityLabels = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];
        
        return $priorityLabels[$this->priority] ?? ucfirst($this->priority);
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
     * Check if task is overdue.
     */
    private function isOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }
        
        return now()->isAfter($this->due_date);
    }
    
    /**
     * Get time variance (actual vs estimated hours).
     */
    private function getTimeVariance(): ?float
    {
        if (!$this->estimated_hours || !$this->actual_hours) {
            return null;
        }
        
        return $this->actual_hours - $this->estimated_hours;
    }
    
    /**
     * Check if the current user can edit this task.
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
     * Check if the current user can delete this task.
     */
    private function canDelete(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }
        
        // User can delete if they belong to the same company, the record is not deleted,
        // and the task is not completed
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
                'resource_type' => 'project_task',
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
                'resource_type' => 'project_task_collection',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
                'status_options' => [
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'on_hold' => 'On Hold',
                ],
                'priority_options' => [
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'critical' => 'Critical',
                ],
            ],
        ]);
    }
}
