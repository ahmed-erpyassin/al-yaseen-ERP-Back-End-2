<?php

namespace Modules\ProjectsManagment\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ProjectsManagment\Models\ProjectDocument;

class ProjectDocumentService
{
    /**
     * Get documents for a user with filters and pagination.
     */
    public function getDocuments($user, array $filters = [], int $perPage = 15)
    {
        $query = ProjectDocument::with(['project', 'creator', 'updater'])
            ->forCompany($user->company_id);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $this->applySorting($query, $sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Create a new document.
     */
    public function createDocument(array $data, $user): ProjectDocument
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['fiscal_year_id'] = $data['fiscal_year_id'] ?? $user->fiscal_year_id;

            $document = ProjectDocument::create($data);

            // Load relationships for response
            $document->load(['project', 'creator']);

            return $document;
        });
    }

    /**
     * Get a document by ID.
     */
    public function getDocumentById(int $id, $user): ProjectDocument
    {
        return ProjectDocument::with(['project', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update a document.
     */
    public function updateDocument(int $id, array $data, $user): ProjectDocument
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $document = ProjectDocument::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            $document->update($data);

            return $document->load(['project', 'creator', 'updater']);
        });
    }

    /**
     * Delete a document (soft delete).
     */
    public function deleteDocument(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $document = ProjectDocument::forCompany($user->company_id)->findOrFail($id);

            // Set deleted_by before soft delete
            $document->update(['deleted_by' => $user->id]);

            return $document->delete();
        });
    }

    /**
     * Restore a soft-deleted document.
     */
    public function restoreDocument(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $document = ProjectDocument::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $document->restore();

            if ($result) {
                $document->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a document.
     */
    public function forceDeleteDocument(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $document = ProjectDocument::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            // Delete the actual file if it exists
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            return $document->forceDelete();
        });
    }

    /**
     * Get trashed documents.
     */
    public function getTrashedDocuments($user, int $perPage = 15)
    {
        return ProjectDocument::onlyTrashed()
            ->with(['project', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search documents with advanced filters.
     */
    public function searchDocuments($user, array $searchParams, int $perPage = 15)
    {
        $query = ProjectDocument::with(['project', 'creator', 'updater'])
            ->forCompany($user->company_id);

        // Apply search filters
        $this->applySearchFilters($query, $searchParams);

        // Apply sorting
        $sortBy = $searchParams['sort_by'] ?? 'created_at';
        $sortOrder = $searchParams['sort_order'] ?? 'desc';
        $this->applySorting($query, $sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get documents by project.
     */
    public function getDocumentsByProject(int $projectId, $user, int $perPage = 15)
    {
        return ProjectDocument::with(['project', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get documents by type.
     */
    public function getDocumentsByType(string $type, $user, int $perPage = 15)
    {
        return ProjectDocument::with(['project', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('document_type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Handle file upload for document.
     */
    public function handleFileUpload($file, array $data): array
    {
        if (!$file) {
            return $data;
        }

        // Store the file
        $path = $file->store('project-documents', 'public');
        
        // Add file information to data
        $data['file_name'] = $file->getClientOriginalName();
        $data['file_path'] = $path;
        $data['file_size'] = $file->getSize();
        $data['file_type'] = $file->getClientMimeType();

        return $data;
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return ProjectDocument::forCompany($user->company_id)
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->distinct()
            ->pluck($field)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (!empty($filters['file_type'])) {
            $query->where('file_type', 'like', "%{$filters['file_type']}%");
        }

        if (!empty($filters['general_search'])) {
            $search = $filters['general_search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Apply search filters to the query.
     */
    private function applySearchFilters($query, array $searchParams): void
    {
        // General search
        if (!empty($searchParams['search'])) {
            $search = $searchParams['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($projectQuery) use ($search) {
                      $projectQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('project_number', 'like', "%{$search}%");
                  });
            });
        }

        // Apply the same filters as applyFilters method
        $this->applyFilters($query, $searchParams);
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, string $sortBy, string $sortOrder): void
    {
        $allowedSortFields = [
            'id', 'title', 'document_type', 'file_name', 'file_size',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
