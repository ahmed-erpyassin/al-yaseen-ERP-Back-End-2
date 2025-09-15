<?php

namespace Modules\ProjectsManagment\Services;

use Illuminate\Support\Facades\DB;
use Modules\ProjectsManagment\Models\ProjectTask;
use Modules\ProjectsManagment\Models\Project;

class ProjectTaskService
{
    /**
     * Get tasks for a user with filters and pagination.
     */
    public function getTasks($user, array $filters = [], int $perPage = 15)
    {
        $query = ProjectTask::with(['project', 'assignedUser', 'creator', 'updater'])
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
     * Create a new task.
     */
    public function createTask(array $data, $user): ProjectTask
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['fiscal_year_id'] = $data['fiscal_year_id'] ?? $user->fiscal_year_id;

            $task = ProjectTask::create($data);

            // Load relationships for response
            $task->load(['project', 'assignedUser', 'creator']);

            return $task;
        });
    }

    /**
     * Get a task by ID.
     */
    public function getTaskById(int $id, $user): ProjectTask
    {
        return ProjectTask::with([
                'project', 'assignedUser', 'creator', 'updater', 'deleter', 'documents'
            ])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update a task.
     */
    public function updateTask(int $id, array $data, $user): ProjectTask
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $task = ProjectTask::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            $task->update($data);

            return $task->load(['project', 'assignedUser', 'creator', 'updater']);
        });
    }

    /**
     * Delete a task (soft delete).
     */
    public function deleteTask(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $task = ProjectTask::forCompany($user->company_id)->findOrFail($id);

            // Set deleted_by before soft delete
            $task->update(['deleted_by' => $user->id]);

            return $task->delete();
        });
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restoreTask(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $task = ProjectTask::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $task->restore();

            if ($result) {
                $task->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a task.
     */
    public function forceDeleteTask(int $id, $user): bool
    {
        $task = ProjectTask::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $task->forceDelete();
    }

    /**
     * Get trashed tasks.
     */
    public function getTrashedTasks($user, int $perPage = 15)
    {
        return ProjectTask::onlyTrashed()
            ->with(['project', 'assignedUser', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search tasks with advanced filters.
     */
    public function searchTasks($user, array $searchParams, int $perPage = 15)
    {
        $query = ProjectTask::with(['project', 'assignedUser', 'creator', 'updater'])
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
     * Get tasks by project.
     */
    public function getTasksByProject(int $projectId, $user, int $perPage = 15)
    {
        return ProjectTask::with(['project', 'assignedUser', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get tasks by status.
     */
    public function getTasksByStatus(string $status, $user, int $perPage = 15)
    {
        return ProjectTask::with(['project', 'assignedUser', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get tasks assigned to user.
     */
    public function getTasksAssignedToUser(int $userId, $user, int $perPage = 15)
    {
        return ProjectTask::with(['project', 'assignedUser', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('assigned_to', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update task status.
     */
    public function updateTaskStatus(int $id, string $status, $user): ProjectTask
    {
        return DB::transaction(function () use ($id, $status, $user) {
            $task = ProjectTask::forCompany($user->company_id)->findOrFail($id);

            $task->update([
                'status' => $status,
                'updated_by' => $user->id
            ]);

            return $task->load(['project', 'assignedUser', 'creator', 'updater']);
        });
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return ProjectTask::forCompany($user->company_id)
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

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['due_date_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_date_from']);
        }

        if (!empty($filters['due_date_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_date_to']);
        }

        if (!empty($filters['general_search'])) {
            $search = $filters['general_search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
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
                  ->orWhere('notes', 'like', "%{$search}%")
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
            'id', 'title', 'status', 'priority', 'due_date', 'progress',
            'estimated_hours', 'actual_hours', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
