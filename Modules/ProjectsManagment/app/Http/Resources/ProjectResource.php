<?php

namespace Modules\ProjectsManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'project_number' => $this->project_number,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'progress' => $this->progress,

            // Dates
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'project_date' => $this->project_date,

            // Financial Information
            'budget' => $this->budget,
            'project_value' => $this->project_value,
            'currency_price' => $this->currency_price,
            'actual_cost' => $this->actual_cost,
            'include_vat' => $this->include_vat,

            // Customer Information
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'licensed_operator' => $this->licensed_operator,

            // Project Management
            'project_manager_name' => $this->project_manager_name,
            'project_manager_email' => $this->project_manager_email,
            'project_manager_phone' => $this->project_manager_phone,

            // Location
            'location' => $this->location,
            'address' => $this->address,
            'coordinates' => $this->coordinates,

            // Additional Information
            'notes' => $this->notes,
            'tags' => $this->tags,
            'priority' => $this->priority,
            'visibility' => $this->visibility,

            // Relationships
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->first_name . ' ' . $this->customer->second_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),

            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'code' => $this->currency->currency_code,
                    'name' => $this->currency->currency_name_ar ?: $this->currency->currency_name_en,
                    'symbol' => $this->currency->currency_symbol,
                ];
            }),

            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->name,
                    'email' => $this->manager->email,
                ];
            }),

            'country' => $this->whenLoaded('country', function () {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                    'code' => $this->country->code,
                ];
            }),

            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),

            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),

            // Project Management Relationships
            'milestones_count' => $this->whenCounted('milestones'),
            'tasks_count' => $this->whenCounted('tasks'),
            'resources_count' => $this->whenCounted('resources'),
            'documents_count' => $this->whenCounted('documents'),
            'financials_count' => $this->whenCounted('financials'),
            'risks_count' => $this->whenCounted('risks'),

            'milestones' => $this->whenLoaded('milestones', function () {
                return ProjectMilestoneResource::collection($this->milestones);
            }),

            'tasks' => $this->whenLoaded('tasks', function () {
                return ProjectTaskResource::collection($this->tasks);
            }),

            'resources' => $this->whenLoaded('resources', function () {
                return ProjectResourceResource::collection($this->resources);
            }),

            'documents' => $this->whenLoaded('documents', function () {
                return ProjectDocumentResource::collection($this->documents);
            }),

            'financials' => $this->whenLoaded('financials', function () {
                return ProjectFinancialResource::collection($this->financials);
            }),

            'risks' => $this->whenLoaded('risks', function () {
                return ProjectRiskResource::collection($this->risks);
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
            'formatted_budget' => $this->formatCurrency($this->budget),
            'formatted_project_value' => $this->formatCurrency($this->project_value),
            'formatted_actual_cost' => $this->formatCurrency($this->actual_cost),
            'progress_percentage' => $this->progress . '%',
            'status_label' => $this->getStatusLabel(),
            'priority_label' => $this->getPriorityLabel(),
            'days_remaining' => $this->getDaysRemaining(),
            'is_overdue' => $this->isOverdue(),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Format currency amount.
     */
    private function formatCurrency($amount): string
    {
        if (!$amount) {
            return '0.00';
        }

        $formattedAmount = number_format($amount, 2);

        if ($this->relationLoaded('currency') && $this->currency) {
            return $this->currency->currency_code . ' ' . $formattedAmount;
        }

        return $formattedAmount;
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        $statusLabels = [
            'planning' => 'Planning',
            'active' => 'Active',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
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
     * Get days remaining until end date.
     */
    private function getDaysRemaining(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Check if project is overdue.
     */
    private function isOverdue(): bool
    {
        if (!$this->end_date || $this->status === 'completed') {
            return false;
        }

        return now()->isAfter($this->end_date);
    }

    /**
     * Check if the current user can edit this project.
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
     * Check if the current user can delete this project.
     */
    private function canDelete(Request $request): bool
    {
        $user = $request->user();

        if (!$user) {
            return false;
        }

        // User can delete if they belong to the same company, the record is not deleted,
        // and the project is not in completed status
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
                'resource_type' => 'project',
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
                'resource_type' => 'project_collection',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
                'status_options' => [
                    'planning' => 'Planning',
                    'active' => 'Active',
                    'on_hold' => 'On Hold',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
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
