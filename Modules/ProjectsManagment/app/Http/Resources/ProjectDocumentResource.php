<?php

namespace Modules\ProjectsManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjectDocumentResource extends JsonResource
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
            'document_type' => $this->document_type,
            
            // File Information
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'file_type' => $this->file_type,
            
            // Additional Information
            'version' => $this->version,
            'tags' => $this->tags,
            'notes' => $this->notes,
            
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
            'formatted_file_size' => $this->formatFileSize($this->file_size),
            'file_extension' => $this->getFileExtension(),
            'document_type_label' => $this->getDocumentTypeLabel(),
            'download_url' => $this->getDownloadUrl(),
            'is_image' => $this->isImage(),
            'is_pdf' => $this->isPdf(),
            'can_preview' => $this->canPreview(),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }
    
    /**
     * Format file size in human readable format.
     */
    private function formatFileSize(?int $bytes): string
    {
        if (!$bytes) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Get file extension.
     */
    private function getFileExtension(): ?string
    {
        if (!$this->file_name) {
            return null;
        }
        
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }
    
    /**
     * Get document type label.
     */
    private function getDocumentTypeLabel(): string
    {
        $typeLabels = [
            'contract' => 'Contract',
            'specification' => 'Specification',
            'drawing' => 'Drawing',
            'report' => 'Report',
            'invoice' => 'Invoice',
            'other' => 'Other',
        ];
        
        return $typeLabels[$this->document_type] ?? ucfirst($this->document_type);
    }
    
    /**
     * Get download URL for the file.
     */
    private function getDownloadUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        
        return Storage::url($this->file_path);
    }
    
    /**
     * Check if file is an image.
     */
    private function isImage(): bool
    {
        if (!$this->file_type) {
            return false;
        }
        
        return str_starts_with($this->file_type, 'image/');
    }
    
    /**
     * Check if file is a PDF.
     */
    private function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }
    
    /**
     * Check if file can be previewed.
     */
    private function canPreview(): bool
    {
        return $this->isImage() || $this->isPdf();
    }
    
    /**
     * Check if the current user can edit this document.
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
     * Check if the current user can delete this document.
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
                'resource_type' => 'project_document',
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
                'resource_type' => 'project_document_collection',
                'version' => '1.0',
                'generated_at' => now()->toISOString(),
                'document_type_options' => [
                    'contract' => 'Contract',
                    'specification' => 'Specification',
                    'drawing' => 'Drawing',
                    'report' => 'Report',
                    'invoice' => 'Invoice',
                    'other' => 'Other',
                ],
            ],
        ]);
    }
}
