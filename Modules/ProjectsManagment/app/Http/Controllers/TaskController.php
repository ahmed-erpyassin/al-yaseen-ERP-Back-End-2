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
use App\Models\Employee;
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

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($perPage);

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
     * Advanced search for tasks.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'due_date' => 'nullable|date',
                'due_date_from' => 'nullable|date',
                'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
                'created_by' => 'nullable|integer|exists:users,id',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'status' => 'nullable|in:to_do,in_progress,done,blocked',
                'project_id' => 'nullable|integer|exists:projects,id',
                'search_term' => 'nullable|string|max:255',
                'sort_by' => 'nullable|string|in:id,task_name,title,status,priority,due_date,created_at,updated_at,progress',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query with relationships
            $query = ProjectTask::with([
                'project', 'milestone', 'assignedUser', 'creator', 'updater', 'documents'
            ])->forCompany($companyId);

            // Search by exact due date
            if ($request->filled('due_date')) {
                $query->whereDate('due_date', $request->due_date);
            }

            // Search by due date range
            if ($request->filled('due_date_from')) {
                $query->whereDate('due_date', '>=', $request->due_date_from);
            }
            if ($request->filled('due_date_to')) {
                $query->whereDate('due_date', '<=', $request->due_date_to);
            }

            // Search by created by (who gave the task)
            if ($request->filled('created_by')) {
                $query->where('created_by', $request->created_by);
            }

            // Search by assigned to (to whom the task was given)
            if ($request->filled('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }

            // Search by priority
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Search by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by project
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            // General search term (searches across multiple fields)
            if ($request->filled('search_term')) {
                $searchTerm = $request->search_term;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('task_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('notes', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('project', function ($projectQuery) use ($searchTerm) {
                          $projectQuery->where('name', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('assignedUser', function ($userQuery) use ($searchTerm) {
                          $userQuery->where('name', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'search_criteria' => $request->only([
                    'due_date', 'due_date_from', 'due_date_to', 'created_by',
                    'assigned_to', 'priority', 'status', 'project_id', 'search_term'
                ]),
                'message' => 'Task search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tasks assigned to the current user (My Tasks).
     */
    public function myTasks(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);

            $query = ProjectTask::with([
                'project', 'milestone', 'creator', 'documents'
            ])->where('company_id', $user->company_id)
              ->where('assigned_to', $user->id);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'due_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'My tasks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving my tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tasks due today (Daily Due Date).
     */
    public function dailyDueTasks(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);
            $today = now()->toDateString();

            $query = ProjectTask::with([
                'project', 'milestone', 'assignedUser', 'creator', 'documents'
            ])->where('company_id', $user->company_id)
              ->whereDate('due_date', $today)
              ->whereNotIn('status', ['done']); // Exclude completed tasks

            // Apply sorting
            $sortBy = $request->get('sort_by', 'priority');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'total_due_today' => $tasks->total(),
                'message' => 'Daily due tasks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving daily due tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue tasks (tasks past due date but not completed).
     */
    public function overdueTasks(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);
            $today = now()->toDateString();

            $query = ProjectTask::with([
                'project', 'milestone', 'assignedUser', 'creator', 'documents'
            ])->where('company_id', $user->company_id)
              ->whereDate('due_date', '<', $today)
              ->whereNotIn('status', ['done']); // Exclude completed tasks

            // Apply sorting (most overdue first)
            $sortBy = $request->get('sort_by', 'due_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($perPage);

            // Calculate days overdue for each task
            $tasks->getCollection()->transform(function ($task) use ($today) {
                $dueDate = \Carbon\Carbon::parse($task->due_date);
                $todayDate = \Carbon\Carbon::parse($today);
                $task->days_overdue = $todayDate->diffInDays($dueDate);
                return $task;
            });

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'total_overdue' => $tasks->total(),
                'message' => 'Overdue tasks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving overdue tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tasks filtered by specific field value (Dynamic Field Selection).
     */
    public function getTasksByField(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'field' => 'required|string|in:status,priority,assigned_to,created_by,project_id,milestone_id,due_date',
                'value' => 'required',
                'per_page' => 'nullable|integer|min:1|max:100',
                'sort_by' => 'nullable|string',
                'sort_order' => 'nullable|in:asc,desc'
            ]);

            $user = $request->user();
            $field = $request->field;
            $value = $request->value;
            $perPage = $request->get('per_page', 15);

            $query = ProjectTask::with([
                'project', 'milestone', 'assignedUser', 'creator', 'updater', 'documents'
            ])->where('company_id', $user->company_id);

            // Apply field filter
            if ($field === 'due_date') {
                $query->whereDate($field, $value);
            } else {
                $query->where($field, $value);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($perPage);

            // Get field display information
            $fieldInfo = $this->getFieldDisplayInfo($field, $value);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'filter_info' => [
                    'field' => $field,
                    'value' => $value,
                    'display_name' => $fieldInfo['display_name'],
                    'field_label' => $fieldInfo['field_label']
                ],
                'message' => "Tasks filtered by {$fieldInfo['field_label']} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving filtered tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for tasks.
     */
    public function getSortableFields(): JsonResponse
    {
        try {
            $sortableFields = [
                'id' => 'Task ID',
                'task_name' => 'Task Name',
                'title' => 'Title',
                'status' => 'Status',
                'priority' => 'Priority',
                'progress' => 'Progress',
                'due_date' => 'Due Date',
                'start_date' => 'Start Date',
                'created_at' => 'Created Date',
                'updated_at' => 'Updated Date',
                'estimated_hours' => 'Estimated Hours',
                'actual_hours' => 'Actual Hours'
            ];

            return response()->json([
                'success' => true,
                'data' => $sortableFields,
                'message' => 'Sortable fields retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving sortable fields: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sort tasks by specified field and order.
     */
    public function sortTasks(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sort_by' => 'required|string|in:id,task_name,title,status,priority,progress,due_date,start_date,created_at,updated_at,estimated_hours,actual_hours',
                'sort_order' => 'required|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $user = $request->user();
            $sortBy = $request->sort_by;
            $sortOrder = $request->sort_order;
            $perPage = $request->get('per_page', 15);

            $query = ProjectTask::with([
                'project', 'milestone', 'assignedUser', 'creator', 'updater', 'documents'
            ])->where('company_id', $user->company_id);

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'sort_info' => [
                    'field' => $sortBy,
                    'order' => $sortOrder,
                    'display_name' => $this->getFieldDisplayName($sortBy)
                ],
                'message' => "Tasks sorted by {$sortBy} ({$sortOrder}) successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sorting tasks: ' . $e->getMessage()
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

            // Set updated_by field
            $data['updated_by'] = $user->id;

            // Handle status change logic
            if (isset($data['status']) && $data['status'] !== $task->status) {
                // Auto-update progress based on status
                if ($data['status'] === 'done' && !isset($data['progress'])) {
                    $data['progress'] = 100;
                } elseif ($data['status'] === 'to_do' && !isset($data['progress'])) {
                    $data['progress'] = 0;
                }
            }

            // Update the task
            $task->update($data);

            // Load relationships for response
            $task->load([
                'project', 'milestone', 'assignedUser', 'creator', 'updater', 'documents'
            ]);

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task updated successfully',
                'changes' => array_keys($data)
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

            // Get employees from the employees table
            $employees = Employee::where('company_id', $companyId)
                ->with('user') // Load related user data
                ->select('id', 'user_id', 'first_name', 'second_name', 'third_name', 'email', 'phone1', 'job_title', 'employee_number')
                ->orderBy('first_name')
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'employee_number' => $employee->employee_number,
                        'name' => trim($employee->first_name . ' ' . $employee->second_name . ' ' . $employee->third_name),
                        'email' => $employee->email,
                        'phone' => $employee->phone1,
                        'job_title' => $employee->job_title,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Employees retrieved successfully from employees table'
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

    /**
     * Get field display information for dynamic filtering.
     */
    private function getFieldDisplayInfo($field, $value)
    {
        $fieldLabels = [
            'status' => 'Status',
            'priority' => 'Priority',
            'assigned_to' => 'Assigned To',
            'created_by' => 'Created By',
            'project_id' => 'Project',
            'milestone_id' => 'Milestone',
            'due_date' => 'Due Date'
        ];

        $displayName = $value;

        // Get display names for specific fields
        switch ($field) {
            case 'status':
                $statusLabels = [
                    'to_do' => 'To Do',
                    'in_progress' => 'In Progress',
                    'done' => 'Done',
                    'blocked' => 'Blocked'
                ];
                $displayName = $statusLabels[$value] ?? $value;
                break;
            case 'priority':
                $priorityLabels = [
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'urgent' => 'Urgent'
                ];
                $displayName = $priorityLabels[$value] ?? $value;
                break;
            case 'assigned_to':
            case 'created_by':
                // You might want to fetch user name here
                $user = \Modules\Users\Models\User::find($value);
                $displayName = $user ? $user->name : "User #{$value}";
                break;
            case 'project_id':
                $project = Project::find($value);
                $displayName = $project ? $project->name : "Project #{$value}";
                break;
            case 'milestone_id':
                $milestone = ProjectMilestone::find($value);
                $displayName = $milestone ? $milestone->name : "Milestone #{$value}";
                break;
        }

        return [
            'field_label' => $fieldLabels[$field] ?? ucfirst($field),
            'display_name' => $displayName
        ];
    }

    /**
     * Get display name for a field.
     */
    private function getFieldDisplayName($field)
    {
        $fieldNames = [
            'id' => 'Task ID',
            'task_name' => 'Task Name',
            'title' => 'Title',
            'status' => 'Status',
            'priority' => 'Priority',
            'progress' => 'Progress',
            'due_date' => 'Due Date',
            'start_date' => 'Start Date',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'estimated_hours' => 'Estimated Hours',
            'actual_hours' => 'Actual Hours'
        ];

        return $fieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
