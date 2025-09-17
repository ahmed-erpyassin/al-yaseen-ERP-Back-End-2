<?php

namespace Modules\ProjectsManagment\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\ProjectsManagment\Models\ProjectRisk;
use Modules\ProjectsManagment\Models\Project;
use Modules\HumanResources\Models\Employee;
use Modules\ProjectsManagment\Http\Resources\ProjectRiskResource;

class ProjectRiskService
{
    /**
     * Get paginated project risks with filtering and relationships.
     */
    public function getProjectRisks(int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
            ->forCompany($companyId);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query = $this->applySorting($query, $filters);

        $perPage = $filters['per_page'] ?? 15;
        $projectRisks = $query->paginate($perPage);

        // Transform using resource
        $projectRisks->getCollection()->transform(function ($projectRisk) {
            return new ProjectRiskResource($projectRisk);
        });

        return $projectRisks;
    }

    /**
     * Create a new project risk.
     */
    public function createProjectRisk(array $data): ProjectRisk
    {
        return ProjectRisk::create($data);
    }

    /**
     * Update an existing project risk.
     */
    public function updateProjectRisk(ProjectRisk $projectRisk, array $data): ProjectRisk
    {
        $projectRisk->update($data);
        return $projectRisk->fresh(['project', 'assignedEmployee', 'creator', 'updater']);
    }

    /**
     * Get projects for dropdown with bidirectional linking.
     */
    public function getProjectsForDropdown(int $companyId): array
    {
        return Project::where('company_id', $companyId)
            ->select('id', 'project_number', 'name')
            ->orderBy('project_number')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'project_number' => $project->project_number,
                    'name' => $project->name,
                    'display_name' => $project->project_number . ' - ' . $project->name,
                    // For bidirectional linking
                    'search_text' => $project->project_number . ' ' . $project->name,
                ];
            })
            ->toArray();
    }

    /**
     * Get employees for dropdown.
     */
    public function getEmployeesForDropdown(int $companyId): array
    {
        return Employee::where('company_id', $companyId)
            ->select('id', 'employee_number', 'first_name', 'second_name', 'third_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                $fullName = trim($employee->first_name . ' ' . $employee->second_name . ' ' . $employee->third_name);
                return [
                    'id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'first_name' => $employee->first_name,
                    'second_name' => $employee->second_name,
                    'third_name' => $employee->third_name,
                    'full_name' => $fullName,
                    'display_name' => $employee->employee_number . ' - ' . $fullName,
                    // For search functionality
                    'search_text' => $employee->employee_number . ' ' . $fullName,
                ];
            })
            ->toArray();
    }

    /**
     * Get project risk statistics.
     */
    public function getProjectRiskStatistics(int $companyId, int $projectId = null): array
    {
        $query = ProjectRisk::forCompany($companyId);
        
        if ($projectId) {
            $query->forProject($projectId);
        }

        $total = $query->count();
        $byStatus = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byImpact = $query->selectRaw('impact, COUNT(*) as count')
            ->groupBy('impact')
            ->pluck('count', 'impact')
            ->toArray();

        $byProbability = $query->selectRaw('probability, COUNT(*) as count')
            ->groupBy('probability')
            ->pluck('count', 'probability')
            ->toArray();

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'by_impact' => $byImpact,
            'by_probability' => $byProbability,
            'high_risk_count' => $this->getHighRiskCount($companyId, $projectId),
        ];
    }

    /**
     * Search project risks.
     */
    public function searchProjectRisks(int $companyId, string $search, array $filters = []): LengthAwarePaginator
    {
        $query = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
            ->forCompany($companyId)
            ->search($search);

        // Apply additional filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query = $this->applySorting($query, $filters);

        $perPage = $filters['per_page'] ?? 15;
        $projectRisks = $query->paginate($perPage);

        // Transform using resource
        $projectRisks->getCollection()->transform(function ($projectRisk) {
            return new ProjectRiskResource($projectRisk);
        });

        return $projectRisks;
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['project_id'])) {
            $query->forProject($filters['project_id']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['impact'])) {
            $query->byImpact($filters['impact']);
        }

        if (!empty($filters['probability'])) {
            $query->byProbability($filters['probability']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->assignedTo($filters['assigned_to']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, array $filters)
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Define allowed sort fields
        $allowedSortFields = [
            'id', 'title', 'impact', 'probability', 'status', 
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    /**
     * Get count of high-risk items.
     */
    private function getHighRiskCount(int $companyId, int $projectId = null): int
    {
        $query = ProjectRisk::forCompany($companyId);
        
        if ($projectId) {
            $query->forProject($projectId);
        }

        return $query->where(function ($q) {
            $q->where('impact', 'high')
              ->orWhere('probability', 'high')
              ->orWhere(function ($subQ) {
                  $subQ->where('impact', 'medium')
                       ->where('probability', 'medium');
              });
        })->count();
    }

    /**
     * Get dropdown options for risk fields.
     */
    public function getDropdownOptions(): array
    {
        return [
            'impact' => ProjectRisk::getImpactOptions(),
            'probability' => ProjectRisk::getProbabilityOptions(),
            'status' => ProjectRisk::getStatusOptions(),
        ];
    }

    /**
     * Find project by number or name for bidirectional linking.
     */
    public function findProjectByNumberOrName(int $companyId, string $search): ?Project
    {
        return Project::where('company_id', $companyId)
            ->where(function ($query) use ($search) {
                $query->where('project_number', $search)
                      ->orWhere('name', 'like', "%{$search}%");
            })
            ->first();
    }

    /**
     * Find employee by number or name.
     */
    public function findEmployeeByNumberOrName(int $companyId, string $search): ?Employee
    {
        return Employee::where('company_id', $companyId)
            ->where(function ($query) use ($search) {
                $query->where('employee_number', $search)
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('second_name', 'like', "%{$search}%")
                      ->orWhere('third_name', 'like', "%{$search}%");
            })
            ->first();
    }
}
