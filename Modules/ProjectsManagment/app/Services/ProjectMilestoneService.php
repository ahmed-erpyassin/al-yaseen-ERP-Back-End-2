<?php

namespace Modules\ProjectsManagment\Services;

use Illuminate\Support\Facades\DB;
use Modules\ProjectsManagment\Models\ProjectMilestone;

class ProjectMilestoneService
{
    /**
     * Get milestones for a user with filters and pagination.
     */
    public function getMilestones($user, array $filters = [], int $perPage = 15)
    {
        $query = ProjectMilestone::with(['project', 'creator', 'updater'])
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
     * Create a new milestone.
     */
    public function createMilestone(array $data, $user): ProjectMilestone
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['fiscal_year_id'] = $data['fiscal_year_id'] ?? $user->fiscal_year_id;

            $milestone = ProjectMilestone::create($data);

            // Load relationships for response
            $milestone->load(['project', 'creator']);

            return $milestone;
        });
    }

    /**
     * Get a milestone by ID.
     */
    public function getMilestoneById(int $id, $user): ProjectMilestone
    {
        return ProjectMilestone::with(['project', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update a milestone.
     */
    public function updateMilestone(int $id, array $data, $user): ProjectMilestone
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $milestone = ProjectMilestone::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            $milestone->update($data);

            return $milestone->load(['project', 'creator', 'updater']);
        });
    }

    /**
     * Delete a milestone (soft delete).
     */
    public function deleteMilestone(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $milestone = ProjectMilestone::forCompany($user->company_id)->findOrFail($id);

            // Set deleted_by before soft delete
            $milestone->update(['deleted_by' => $user->id]);

            return $milestone->delete();
        });
    }

    /**
     * Restore a soft-deleted milestone.
     */
    public function restoreMilestone(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $milestone = ProjectMilestone::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $milestone->restore();

            if ($result) {
                $milestone->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a milestone.
     */
    public function forceDeleteMilestone(int $id, $user): bool
    {
        $milestone = ProjectMilestone::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $milestone->forceDelete();
    }

    /**
     * Get trashed milestones.
     */
    public function getTrashedMilestones($user, int $perPage = 15)
    {
        return ProjectMilestone::onlyTrashed()
            ->with(['project', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search milestones with advanced filters.
     */
    public function searchMilestones($user, array $searchParams, int $perPage = 15)
    {
        $query = ProjectMilestone::with(['project', 'creator', 'updater'])
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
     * Get milestones by project.
     */
    public function getMilestonesByProject(int $projectId, $user, int $perPage = 15)
    {
        return ProjectMilestone::with(['project', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('project_id', $projectId)
            ->orderBy('due_date', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get milestones by status.
     */
    public function getMilestonesByStatus(string $status, $user, int $perPage = 15)
    {
        return ProjectMilestone::with(['project', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('status', $status)
            ->orderBy('due_date', 'asc')
            ->paginate($perPage);
    }

    /**
     * Update milestone status.
     */
    public function updateMilestoneStatus(int $id, string $status, $user): ProjectMilestone
    {
        return DB::transaction(function () use ($id, $status, $user) {
            $milestone = ProjectMilestone::forCompany($user->company_id)->findOrFail($id);

            $milestone->update([
                'status' => $status,
                'updated_by' => $user->id
            ]);

            return $milestone->load(['project', 'creator', 'updater']);
        });
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return ProjectMilestone::forCompany($user->company_id)
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

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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
                  ->orWhere('description', 'like', "%{$search}%");
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
            'id', 'title', 'status', 'due_date', 'completion_percentage',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
