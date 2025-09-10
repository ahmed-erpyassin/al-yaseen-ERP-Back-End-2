<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Models\ProjectDocument;
use Modules\ProjectsManagment\Models\Project;
use Modules\ProjectsManagment\Http\Requests\StoreDocumentRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateDocumentRequest;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
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
            $user = request()->user();
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
            $user = $request->user();
            $companyId = $user->company_id;

            $document = ProjectDocument::forCompany($companyId)->findOrFail($id);
            $validatedData = $request->validated();

            // Handle file upload if new file is provided
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

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

            $document->update($validatedData);
            $document->load(['project', 'creator', 'updater']);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Document updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified document (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();
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
            $user = $request->user();
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

            $user = $request->user();
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
            $user = $request->user();
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
            $user = request()->user();
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
            $user = $request->user();
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
}
