<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\ProjectsManagment\Models\ProjectDocument;
use Modules\ProjectsManagment\Models\Project;
use Modules\ProjectsManagment\Http\Requests\StoreDocumentRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateDocumentRequest;
use Illuminate\Support\Facades\Storage;

/**
 * @group Project Management / Documents
 *
 * APIs for managing project documents, including upload, download, categorization, and document lifecycle management.
 */
class DocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            $query = ProjectDocument::with(['project', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply filters
            if ($request->has('project_id') && !empty($request->project_id)) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('document_category') && !empty($request->document_category)) {
                $query->where('document_category', $request->document_category);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Apply advanced search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSortFields = [
                'id', 'document_number', 'title', 'document_category', 'status',
                'upload_date', 'file_size', 'version', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $documents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created document.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('project_documents', $fileName, 'public');

                // Add file information to validated data
                $validatedData['file_path'] = $filePath;
                $validatedData['file_name'] = $file->getClientOriginalName();
                $validatedData['file_type'] = $file->getClientMimeType();
                $validatedData['file_size'] = $file->getSize();
            }

            // Remove the file from validated data as it's not a database field
            unset($validatedData['file']);

            $document = ProjectDocument::create($validatedData);
            $document->load(['project', 'creator']);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Document created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified document.
     */
    public function show($id): JsonResponse
    {
        try {
            $user =Auth::user();
            $companyId = $user->company_id;

            $document = ProjectDocument::with(['project', 'creator', 'updater'])
                ->forCompany($companyId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Document retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified document.
     */
    public function update(UpdateDocumentRequest $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Find document with company verification
            $document = ProjectDocument::forCompany($companyId)->findOrFail($id);

            // Store original data for audit trail
            $originalData = $document->toArray();
            $validatedData = $request->validated();

            // Auto-populate project data if project_id is provided
            if (isset($validatedData['project_id']) && $validatedData['project_id'] !== $document->project_id) {
                $project = Project::where('id', $validatedData['project_id'])
                    ->where('company_id', $companyId)
                    ->first();

                if (!$project) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected project does not exist or does not belong to your company'
                    ], 400);
                }

                $validatedData['project_number'] = $project->project_number;
                $validatedData['project_name'] = $project->name;
            }

            // Handle file upload if new file is provided
            if ($request->hasFile('file')) {
                // Validate file before processing
                $file = $request->file('file');

                if (!$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file upload'
                    ], 400);
                }

                // Delete old file if exists
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                // Store new file
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('project_documents', $fileName, 'public');

                // Add file information to validated data
                $validatedData['file_path'] = $filePath;
                $validatedData['file_name'] = $file->getClientOriginalName();
                $validatedData['file_type'] = $file->getClientMimeType();
                $validatedData['file_size'] = $file->getSize();
                $validatedData['upload_date'] = now()->toDateString();
            }

            // Remove the file from validated data as it's not a database field
            unset($validatedData['file']);

            // Update document
            $document->update($validatedData);
            $document->load(['project', 'creator', 'updater']);

            // Log the update for audit trail
            \Log::info('Document updated', [
                'document_id' => $document->id,
                'updated_by' => $user->id,
                'original_data' => $originalData,
                'new_data' => $document->fresh()->toArray(),
                'company_id' => $companyId
            ]);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Document updated successfully',
                'changes' => [
                    'updated_fields' => array_keys($validatedData),
                    'file_updated' => $request->hasFile('file')
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating document: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Remove the specified document (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user =Auth::user();
            $companyId = $user->company_id;

            $document = ProjectDocument::forCompany($companyId)->findOrFail($id);

            // Set deleted_by before soft delete
            $document->update(['deleted_by' => $user->id]);
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get projects for dropdown (with project number and name).
     */
    public function getProjects(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $projects = Project::where('company_id', $companyId)
                ->where('status', '!=', 'cancelled')
                ->select('id', 'code', 'project_number', 'name')
                ->orderBy('name')
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'code' => $project->code,
                        'project_number' => $project->project_number,
                        'name' => $project->name,
                        'display_name' => $project->name . ($project->project_number ? " ({$project->project_number})" : ''),
                        'display_number' => $project->project_number . ($project->name ? " - {$project->name}" : ''),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $projects,
                'message' => 'Projects retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document category options.
     */
    public function getDocumentCategories(): JsonResponse
    {
        $categories = [
            ['value' => 'contract', 'label' => 'Contract'],
            ['value' => 'specification', 'label' => 'Specification'],
            ['value' => 'drawing', 'label' => 'Drawing'],
            ['value' => 'report', 'label' => 'Report'],
            ['value' => 'invoice', 'label' => 'Invoice'],
            ['value' => 'correspondence', 'label' => 'Correspondence'],
            ['value' => 'other', 'label' => 'Other'],
        ];

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Document categories retrieved successfully'
        ]);
    }

    /**
     * Get document status options.
     */
    public function getStatusOptions(): JsonResponse
    {
        $statuses = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'archived', 'label' => 'Archived'],
            ['value' => 'deleted', 'label' => 'Deleted'],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses,
            'message' => 'Document statuses retrieved successfully'
        ]);
    }

    /**
     * Generate next document number for a project.
     */
    public function generateDocumentNumber(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id'
            ]);

            $user = Auth::user();
            $companyId = $user->company_id;

            // Verify project belongs to user's company
            $project = Project::where('id', $request->project_id)
                ->where('company_id', $companyId)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or does not belong to your company'
                ], 404);
            }

            $lastDocument = ProjectDocument::where('project_id', $request->project_id)
                ->orderBy('document_number', 'desc')
                ->first();

            $nextNumber = $lastDocument ? ($lastDocument->document_number + 1) : 1;

            return response()->json([
                'success' => true,
                'data' => [
                    'project_id' => $project->id,
                    'document_number' => $nextNumber
                ],
                'message' => 'Document number generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating document number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents for a specific project.
     */
    public function getProjectDocuments(Request $request, $projectId): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Verify project belongs to user's company
            $project = Project::where('id', $projectId)
                ->where('company_id', $companyId)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or does not belong to your company'
                ], 404);
            }

            $documents = ProjectDocument::with(['creator', 'updater'])
                ->where('project_id', $projectId)
                ->active()
                ->orderBy('document_number', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Project documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a document file.
     */
    public function downloadDocument($id): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        try {
            $user =Auth::user();
            $companyId = $user->company_id;

            $document = ProjectDocument::forCompany($companyId)->findOrFail($id);

            if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            $filePath = Storage::disk('public')->path($document->file_path);
            $fileName = $document->file_name ?: basename($document->file_path);

            return response()->download($filePath, $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents by category.
     */
    public function getDocumentsByCategory(Request $request, $category): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $validCategories = ['contract', 'specification', 'drawing', 'report', 'invoice', 'correspondence', 'other'];

            if (!in_array($category, $validCategories)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document category'
                ], 400);
            }

            $documents = ProjectDocument::with(['project', 'creator', 'updater'])
                ->forCompany($companyId)
                ->byCategory($category)
                ->active()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => "Documents in category '{$category}' retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving documents by category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for documents.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            $query = ProjectDocument::with(['project', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply advanced search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            $documents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Documents search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents by specific field value.
     */
    public function getDocumentsByField(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $field = $request->get('field');
            $value = $request->get('value');
            $perPage = $request->get('per_page', 15);

            if (!$field || !$value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field and value parameters are required'
                ], 400);
            }

            $allowedFields = [
                'document_number', 'project_id', 'project_number', 'project_name',
                'title', 'document_category', 'status', 'file_name', 'file_type',
                'upload_date', 'version', 'description'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            $query = ProjectDocument::with(['project', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply field filter
            if (in_array($field, ['document_number', 'project_id'])) {
                $query->where($field, '=', $value);
            } elseif ($field === 'upload_date') {
                $query->whereDate($field, '=', $value);
            } else {
                $query->where($field, 'like', "%{$value}%");
            }

            $documents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => "Documents filtered by {$field} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unique values for a specific field.
     */
    public function getFieldValues(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $field = $request->get('field');

            if (!$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field parameter is required'
                ], 400);
            }

            $allowedFields = [
                'document_number', 'project_number', 'project_name', 'title',
                'document_category', 'status', 'file_name', 'file_type',
                'upload_date', 'version', 'description'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            $values = ProjectDocument::forCompany($companyId)
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->distinct()
                ->pluck($field)
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $values,
                'message' => "Unique values for {$field} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving field values: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for documents.
     */
    public function getSortableFields(): JsonResponse
    {
        $fields = [
            ['field' => 'id', 'label' => 'ID'],
            ['field' => 'document_number', 'label' => 'Document Number'],
            ['field' => 'project_number', 'label' => 'Project Number'],
            ['field' => 'project_name', 'label' => 'Project Name'],
            ['field' => 'title', 'label' => 'Document Title'],
            ['field' => 'document_category', 'label' => 'Category'],
            ['field' => 'status', 'label' => 'Status'],
            ['field' => 'file_name', 'label' => 'File Name'],
            ['field' => 'file_type', 'label' => 'File Type'],
            ['field' => 'file_size', 'label' => 'File Size'],
            ['field' => 'upload_date', 'label' => 'Upload Date'],
            ['field' => 'version', 'label' => 'Version'],
            ['field' => 'created_at', 'label' => 'Created Date'],
            ['field' => 'updated_at', 'label' => 'Updated Date'],
        ];

        return response()->json([
            'success' => true,
            'data' => $fields,
            'message' => 'Sortable fields retrieved successfully'
        ]);
    }

    /**
     * Sort documents by specified field and order.
     */
    public function sortDocuments(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $query = ProjectDocument::with(['project', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply sorting
            $this->applySorting($query, $request);

            $documents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => "Documents sorted by {$sortBy} ({$sortOrder}) successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sorting documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted document.
     */
    public function restore($id): JsonResponse
    {
        try {
            $user =Auth::user();
            $companyId = $user->company_id;

            $document = ProjectDocument::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            if (!$document->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document is not deleted'
                ], 400);
            }

            $document->restore();
            $document->update(['deleted_by' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Document restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a document.
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $user =Auth::user();
            $companyId = $user->company_id;

            $document = ProjectDocument::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            // Delete file if exists
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Document permanently deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed documents.
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $documents = ProjectDocument::onlyTrashed()
                ->with(['project', 'creator', 'updater', 'deleter'])
                ->forCompany($companyId)
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Trashed documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply search filters to the query.
     */
    private function applySearchFilters($query, Request $request): void
    {
        // General search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('project_number', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($projectQuery) use ($search) {
                      $projectQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('project_number', 'like', "%{$search}%");
                  });
            });
        }

        // Specific field searches
        if ($request->has('project_number') && !empty($request->project_number)) {
            $query->where('project_number', 'like', "%{$request->project_number}%");
        }

        if ($request->has('document_title') && !empty($request->document_title)) {
            $query->where('title', 'like', "%{$request->document_title}%");
        }

        if ($request->has('document_category') && !empty($request->document_category)) {
            $query->where('document_category', $request->document_category);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('file_type') && !empty($request->file_type)) {
            $query->where('file_type', 'like', "%{$request->file_type}%");
        }

        // Date range filters
        if ($request->has('upload_date_from') && !empty($request->upload_date_from)) {
            $query->whereDate('upload_date', '>=', $request->upload_date_from);
        }

        if ($request->has('upload_date_to') && !empty($request->upload_date_to)) {
            $query->whereDate('upload_date', '<=', $request->upload_date_to);
        }

        if ($request->has('upload_date') && !empty($request->upload_date)) {
            $query->whereDate('upload_date', '=', $request->upload_date);
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = [
            'id', 'document_number', 'project_number', 'project_name', 'title',
            'document_category', 'status', 'file_name', 'file_type', 'file_size',
            'upload_date', 'version', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);

            // Add secondary sorting for consistency
            if ($sortBy !== 'created_at') {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
