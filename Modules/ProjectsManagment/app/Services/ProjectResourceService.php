<?php

namespace Modules\ProjectsManagment\Services;

use Illuminate\Support\Facades\DB;
use Modules\ProjectsManagment\Models\ProjectResource;
use Modules\ProjectsManagment\Models\Project;
use Modules\Inventory\Models\Supplier;

class ProjectResourceService
{
    /**
     * Get resources for a user with filters and pagination.
     */
    public function getResources($user, array $filters = [], int $perPage = 15)
    {
        $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
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
     * Create a new resource.
     */
    public function createResource(array $data, $user): ProjectResource
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['fiscal_year_id'] = $data['fiscal_year_id'] ?? $user->fiscal_year_id;

            $resource = ProjectResource::create($data);

            // Load relationships for response
            $resource->load(['project', 'supplier', 'creator']);

            return $resource;
        });
    }

    /**
     * Get a resource by ID.
     */
    public function getResourceById(int $id, $user): ProjectResource
    {
        return ProjectResource::with(['project', 'supplier', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update a resource.
     */
    public function updateResource(int $id, array $data, $user): ProjectResource
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $resource = ProjectResource::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            $resource->update($data);

            return $resource->load(['project', 'supplier', 'creator', 'updater']);
        });
    }

    /**
     * Delete a resource (soft delete).
     */
    public function deleteResource(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $resource = ProjectResource::forCompany($user->company_id)->findOrFail($id);

            // Set deleted_by before soft delete
            $resource->update(['deleted_by' => $user->id]);

            return $resource->delete();
        });
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restoreResource(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $resource = ProjectResource::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $resource->restore();

            if ($result) {
                $resource->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a resource.
     */
    public function forceDeleteResource(int $id, $user): bool
    {
        $resource = ProjectResource::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $resource->forceDelete();
    }

    /**
     * Get trashed resources.
     */
    public function getTrashedResources($user, int $perPage = 15)
    {
        return ProjectResource::onlyTrashed()
            ->with(['project', 'supplier', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search resources with advanced filters.
     */
    public function searchResources($user, array $searchParams, int $perPage = 15)
    {
        $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
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
     * Get resources by project.
     */
    public function getResourcesByProject(int $projectId, $user, int $perPage = 15)
    {
        return ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get resources by supplier.
     */
    public function getResourcesBySupplier(int $supplierId, $user, int $perPage = 15)
    {
        return ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Calculate allocation percentage.
     */
    public function calculateAllocationPercentage(array $data): array
    {
        if (!isset($data['allocation_value']) || !isset($data['project_id'])) {
            return ['allocation_percentage' => 0];
        }

        $project = Project::find($data['project_id']);
        if (!$project || !$project->project_value) {
            return ['allocation_percentage' => 0];
        }

        $percentage = ($data['allocation_value'] / $project->project_value) * 100;

        return [
            'allocation_percentage' => round($percentage, 2),
            'project_value' => $project->project_value
        ];
    }

    /**
     * Calculate allocation value.
     */
    public function calculateAllocationValue(array $data): array
    {
        if (!isset($data['allocation_percentage']) || !isset($data['project_id'])) {
            return ['allocation_value' => 0];
        }

        $project = Project::find($data['project_id']);
        if (!$project || !$project->project_value) {
            return ['allocation_value' => 0];
        }

        $value = ($data['allocation_percentage'] / 100) * $project->project_value;

        return [
            'allocation_value' => round($value, 2),
            'project_value' => $project->project_value
        ];
    }

    /**
     * Get suppliers for dropdown.
     */
    public function getSuppliersForDropdown($user): array
    {
        return Supplier::where('company_id', $user->company_id)
            ->select('id', 'supplier_number', 'supplier_name')
            ->orderBy('supplier_name')
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'supplier_number' => $supplier->supplier_number,
                    'supplier_name' => $supplier->supplier_name,
                    'display_name' => $supplier->supplier_number . ' - ' . $supplier->supplier_name
                ];
            })
            ->toArray();
    }

    /**
     * Get projects for dropdown.
     */
    public function getProjectsForDropdown($user): array
    {
        return Project::where('company_id', $user->company_id)
            ->select('id', 'project_number', 'name', 'project_value')
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'project_number' => $project->project_number,
                    'name' => $project->name,
                    'display_name' => $project->project_number . ' - ' . $project->name,
                    'project_value' => $project->project_value
                ];
            })
            ->toArray();
    }

    /**
     * Get resource type options.
     */
    public function getResourceTypeOptions(): array
    {
        return [
            ['value' => 'supplier', 'label' => 'Supplier'],
            ['value' => 'internal', 'label' => 'Internal'],
            ['value' => 'contractor', 'label' => 'Contractor'],
            ['value' => 'consultant', 'label' => 'Consultant'],
        ];
    }

    /**
     * Get status options.
     */
    public function getStatusOptions(): array
    {
        return [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
            ['value' => 'completed', 'label' => 'Completed'],
        ];
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return ProjectResource::forCompany($user->company_id)
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

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['resource_type'])) {
            $query->where('resource_type', $filters['resource_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['role'])) {
            $query->where('role', 'like', "%{$filters['role']}%");
        }

        if (!empty($filters['allocation_min'])) {
            $query->where('allocation_value', '>=', $filters['allocation_min']);
        }

        if (!empty($filters['allocation_max'])) {
            $query->where('allocation_value', '<=', $filters['allocation_max']);
        }

        if (!empty($filters['general_search'])) {
            $search = $filters['general_search'];
            $query->where(function ($q) use ($search) {
                $q->where('role', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('allocation', 'like', "%{$search}%");
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
                $q->where('role', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('allocation', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($projectQuery) use ($search) {
                      $projectQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('project_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                      $supplierQuery->where('supplier_name', 'like', "%{$search}%")
                                   ->orWhere('supplier_number', 'like', "%{$search}%");
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
            'id', 'project_id', 'supplier_id', 'role', 'allocation_value',
            'allocation_percentage', 'resource_type', 'status', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
