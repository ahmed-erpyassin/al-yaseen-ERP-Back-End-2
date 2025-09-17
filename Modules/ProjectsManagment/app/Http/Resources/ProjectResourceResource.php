<?php

namespace Modules\ProjectsManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'allocation' => $this->allocation,
            'allocation_percentage' => $this->allocation_percentage,
            'allocation_value' => $this->allocation_value,
            'resource_type' => $this->resource_type,
            'status' => $this->status,
            'notes' => $this->notes,
            
            // Relationships
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_number' => $this->project->project_number,
                    'name' => $this->project->name,
                    'project_value' => $this->project->project_value,
                    'status' => $this->project->status,
                ];
            }),
            
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'supplier_number' => $this->supplier->supplier_number,
                    'supplier_name' => $this->supplier->supplier_name,
                    'email' => $this->supplier->email,
                    'phone' => $this->supplier->phone,
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
            'formatted_allocation_value' => $this->formatCurrency($this->allocation_value),
            'allocation_percentage_display' => $this->allocation_percentage . '%',
            'resource_type_label' => $this->getResourceTypeLabel(),
            'status_label' => $this->getStatusLabel(),
            'project_number' => $this->project_number, // From accessor
            'project_name' => $this->project_name, // From accessor
            'supplier_number' => $this->supplier_number, // From accessor
            'supplier_name' => $this->supplier_name, // From accessor
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
        
        if ($this->relationLoaded('project') && $this->project && $this->project->relationLoaded('currency') && $this->project->currency) {
            return $this->project->currency->currency_code . ' ' . $formattedAmount;
        }
        
        return $formattedAmount;
    }
    
    /**
     * Get resource type label.
     */
    private function getResourceTypeLabel(): string
    {
        $typeLabels = [
            'supplier' => 'Supplier',
            'internal' => 'Internal',
            'contractor' => 'Contractor',
            'consultant' => 'Consultant',
        ];
        
        return $typeLabels[$this->resource_type] ?? ucfirst($this->resource_type);
    }
    
    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        $statusLabels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'completed' => 'Completed',
        ];
        
        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }
    
    /**
     * Check if the current user can edit this resource.
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
     * Check if the current user can delete this resource.
     */
    private function canDelete(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }
        
        // User can delete if they belong to the same company, the record is not deleted,
        // and the resource is not completed
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
                'resource_type' => 'project_resource',
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
                'resource_type' => 'project_resource_collection',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
                'resource_type_options' => [
                    'supplier' => 'Supplier',
                    'internal' => 'Internal',
                    'contractor' => 'Contractor',
                    'consultant' => 'Consultant',
                ],
                'status_options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'completed' => 'Completed',
                ],
            ],
        ]);
    }
}
