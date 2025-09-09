<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Models\ProjectTask;
use Modules\ProjectsManagment\Models\TaskDocument;
use Modules\ProjectsManagment\Models\Project;
use Modules\ProjectsManagment\Http\Requests\StoreTaskRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateTaskRequest;
use Modules\Users\Models\User;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            // Get filter parameters
            $projectId = $request->get('project_id');
            $assignedTo = $request->get('assigned_to');
            $status = $request->get('status');
            $perPage = $request->get('per_page', 15);

            // Build query
            $query = ProjectTask::with([
                'project', 'milestone', 'assignedUser', 'creator', 'documents'
            ])->forCompany($companyId);

            // Apply filters
            if ($projectId) {
                $query->forProject($projectId);
            }

            if ($assignedTo) {
                $query->assignedTo($assignedTo);
            }

            if ($status) {
                $query->byStatus($status);
            }

            $tasks = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'Tasks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Create the task
            $task = ProjectTask::create($data);

            // Load relationships for response
            $task->load([
                'project', 'milestone', 'assignedUser', 'creator'
            ]);

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified task.
     */
    public function show($id): JsonResponse
    {
        try {
            $task = ProjectTask::with([
                'project', 'milestone', 'assignedUser', 'creator', 'updater', 'documents'
            ])->findOrFail($id);

            // Check company access
            $user = request()->user();
            if ($task->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this task'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving task: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, $id): JsonResponse
    {
        try {
            $task = ProjectTask::findOrFail($id);

            // Check company access
            $user = $request->user();
            if ($task->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this task'
                ], 403);
            }

            $data = $request->validated();
            $task->update($data);

            // Load relationships for response
            $task->load([
                'project', 'milestone', 'assignedUser', 'creator', 'updater'
            ]);

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified task.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $task = ProjectTask::findOrFail($id);

            // Check company access
            $user = request()->user();
            if ($task->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this task'
                ], 403);
            }

            // Set deleted_by before soft delete
            $task->deleted_by = $user->id;
            $task->save();
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employees for assignment dropdown.
     */
    public function getEmployees(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $employees = User::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'first_name', 'second_name', 'email', 'phone')
                ->orderBy('first_name')
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => trim($employee->first_name . ' ' . $employee->second_name),
                        'email' => $employee->email,
                        'phone' => $employee->phone,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Employees retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get task status options.
     */
    public function getTaskStatuses(): JsonResponse
    {
        $statuses = [
            ['value' => 'to_do', 'label' => 'To Do'],
            ['value' => 'in_progress', 'label' => 'In Progress'],
            ['value' => 'done', 'label' => 'Done'],
            ['value' => 'blocked', 'label' => 'Blocked'],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses,
            'message' => 'Task statuses retrieved successfully'
        ]);
    }

    /**
     * Get task priority options.
     */
    public function getTaskPriorities(): JsonResponse
    {
        $priorities = [
            ['value' => 'low', 'label' => 'Low'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'high', 'label' => 'High'],
            ['value' => 'urgent', 'label' => 'Urgent'],
        ];

        return response()->json([
            'success' => true,
            'data' => $priorities,
            'message' => 'Task priorities retrieved successfully'
        ]);
    }

    /**
     * Get tasks for a specific project.
     */
    public function getProjectTasks(Request $request, $projectId): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            // Verify project access
            $project = Project::where('id', $projectId)
                ->where('company_id', $companyId)
                ->firstOrFail();

            $tasks = ProjectTask::with([
                'milestone', 'assignedUser', 'creator'
            ])
            ->forProject($projectId)
            ->forCompany($companyId)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'project' => $project,
                'message' => 'Project tasks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload document for a task.
     */
    public function uploadDocument(Request $request, $taskId): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            $user = $request->user();
            $task = ProjectTask::where('id', $taskId)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            // Handle file upload
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('task_documents', $fileName, 'public');

            // Create document record
            $document = TaskDocument::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id ?? 1,
                'fiscal_year_id' => $user->fiscal_year_id ?? 1,
                'project_id' => $task->project_id,
                'task_id' => $task->id,
                'title' => $request->title,
                'description' => $request->description,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Document uploaded successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents for a task.
     */
    public function getTaskDocuments($taskId): JsonResponse
    {
        try {
            $user = request()->user();
            $task = ProjectTask::where('id', $taskId)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            $documents = TaskDocument::forTask($taskId)
                ->forCompany($user->company_id)
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Task documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving task documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a task document.
     */
    public function deleteDocument($documentId): JsonResponse
    {
        try {
            $user = request()->user();
            $document = TaskDocument::where('id', $documentId)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            // Delete file from storage
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Soft delete the document record
            $document->deleted_by = $user->id;
            $document->save();
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
}
