<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\ProjectsManagment\Models\ProjectRisk;
use Modules\ProjectsManagment\Models\Project;
use Modules\HumanResources\Models\Employee;
use Modules\ProjectsManagment\Http\Requests\StoreProjectRiskRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateProjectRiskRequest;
use Modules\ProjectsManagment\Http\Resources\ProjectRiskResource;
use Modules\ProjectsManagment\Services\ProjectRiskService;

class ProjectRiskController extends Controller
{
    protected $projectRiskService;

    public function __construct(ProjectRiskService $projectRiskService)
    {
        $this->projectRiskService = $projectRiskService;
    }

    /**
     * Display a listing of project risks.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $projectRisks = $this->projectRiskService->getProjectRisks($companyId, $request->all());

            return response()->json([
                'success' => true,
                'data' => $projectRisks,
                'message' => 'Project risks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created project risk.
     */
    public function store(StoreProjectRiskRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $data = $request->validated();

            // Add system fields
            $data['user_id'] = $user->id;
            $data['company_id'] = $user->company_id;
            $data['branch_id'] = $user->branch_id;
            $data['fiscal_year_id'] = $user->fiscal_year_id;
            $data['created_by'] = $user->id;

            $projectRisk = ProjectRisk::create($data);
            $projectRisk->load(['project', 'assignedEmployee', 'creator']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ProjectRiskResource($projectRisk),
                'message' => 'Project risk created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating project risk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified project risk.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisk = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new ProjectRiskResource($projectRisk),
                'message' => 'Project risk retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project risk: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified project risk.
     */
    public function update(UpdateProjectRiskRequest $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            // Find the project risk with relationships
            $projectRisk = ProjectRisk::with(['project', 'assignedEmployee', 'creator'])
                ->forCompany($companyId)
                ->findOrFail($id);

            // Check if the record is soft deleted
            if ($projectRisk->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update a deleted project risk. Please restore it first.'
                ], 422);
            }

            // Get validated data
            $data = $request->validated();

            // Add system fields
            $data['updated_by'] = $user->id;

            // Store original values for comparison
            $originalData = $projectRisk->only([
                'project_id', 'title', 'description', 'impact',
                'probability', 'mitigation_plan', 'status', 'assigned_to'
            ]);

            // Update the project risk
            $projectRisk->update($data);

            // Reload relationships
            $projectRisk->load(['project', 'assignedEmployee', 'creator', 'updater']);

            // Log changes if needed (you can implement audit logging here)
            $changes = array_diff_assoc($data, $originalData);
            if (!empty($changes)) {
                // You can log the changes here for audit purposes
                \Log::info('Project Risk Updated', [
                    'risk_id' => $projectRisk->id,
                    'updated_by' => $user->id,
                    'changes' => $changes,
                    'original' => $originalData
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ProjectRiskResource($projectRisk),
                'message' => 'Project risk updated successfully',
                'changes_made' => !empty($changes),
                'updated_fields' => array_keys($changes)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Project risk not found or does not belong to your company'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating project risk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified project risk (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisk = ProjectRisk::forCompany($companyId)->findOrFail($id);

            // Check if already deleted
            if ($projectRisk->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project risk is already deleted'
                ], 422);
            }

            // Update deleted_by before soft delete
            $projectRisk->update(['deleted_by' => $user->id]);
            $projectRisk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project risk deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Project risk not found or does not belong to your company'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting project risk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted project risk.
     */
    public function restore($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisk = ProjectRisk::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            if (!$projectRisk->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project risk is not deleted'
                ], 422);
            }

            $projectRisk->restore();
            $projectRisk->update([
                'deleted_by' => null,
                'updated_by' => $user->id
            ]);

            $projectRisk->load(['project', 'assignedEmployee', 'creator', 'updater']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ProjectRiskResource($projectRisk),
                'message' => 'Project risk restored successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Project risk not found or does not belong to your company'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error restoring project risk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a project risk.
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisk = ProjectRisk::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            $projectRisk->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project risk permanently deleted'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Project risk not found or does not belong to your company'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting project risk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed (soft-deleted) project risks.
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $query = ProjectRisk::onlyTrashed()
                ->with(['project', 'assignedEmployee', 'creator', 'updater', 'deleter'])
                ->forCompany($companyId);

            // Apply sorting
            $query = $this->applySorting($query, $request->all());

            $perPage = $request->get('per_page', 15);
            $trashedRisks = $query->paginate($perPage);

            // Transform using resource
            $trashedRisks->getCollection()->transform(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $trashedRisks,
                'message' => 'Trashed project risks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get projects for dropdown with bidirectional linking.
     */
    public function getProjects(): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projects = Project::where('company_id', $companyId)
                ->select('id', 'project_number', 'name')
                ->orderBy('project_number')
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'project_number' => $project->project_number,
                        'name' => $project->name,
                        'display_name' => $project->project_number . ' - ' . $project->name,
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
     * Get employees for dropdown.
     */
    public function getEmployees(): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $employees = Employee::where('company_id', $companyId)
                ->select('id', 'employee_number', 'first_name', 'second_name', 'third_name')
                ->orderBy('first_name')
                ->get()
                ->map(function ($employee) {
                    $fullName = trim($employee->first_name . ' ' . $employee->second_name . ' ' . $employee->third_name);
                    return [
                        'id' => $employee->id,
                        'employee_number' => $employee->employee_number,
                        'full_name' => $fullName,
                        'display_name' => $employee->employee_number . ' - ' . $fullName,
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
     * Get impact options for dropdown.
     */
    public function getImpactOptions(): JsonResponse
    {
        try {
            $options = ProjectRisk::getImpactOptions();

            return response()->json([
                'success' => true,
                'data' => $options,
                'message' => 'Impact options retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving impact options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get probability options for dropdown.
     */
    public function getProbabilityOptions(): JsonResponse
    {
        try {
            $options = ProjectRisk::getProbabilityOptions();

            return response()->json([
                'success' => true,
                'data' => $options,
                'message' => 'Probability options retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving probability options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status options for dropdown.
     */
    public function getStatusOptions(): JsonResponse
    {
        try {
            $options = ProjectRisk::getStatusOptions();

            return response()->json([
                'success' => true,
                'data' => $options,
                'message' => 'Status options retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving status options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for project risks.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $query = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply search filters
            $query = $this->applySearchFilters($query, $request->all());

            // Apply sorting
            $query = $this->applySorting($query, $request->all());

            $perPage = $request->get('per_page', 15);
            $projectRisks = $query->paginate($perPage);

            // Transform using resource
            $projectRisks->getCollection()->transform(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $projectRisks,
                'message' => 'Project risks search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project risks filtered by specific field.
     */
    public function getProjectRisksByField(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $field = $request->get('field');
            $value = $request->get('value');

            if (!$field || !$value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field and value parameters are required'
                ], 400);
            }

            $query = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply field-specific filtering
            $query = $this->applyFieldFilter($query, $field, $value);

            // Apply sorting
            $query = $this->applySorting($query, $request->all());

            $perPage = $request->get('per_page', 15);
            $projectRisks = $query->paginate($perPage);

            // Transform using resource
            $projectRisks->getCollection()->transform(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $projectRisks,
                'message' => 'Project risks filtered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply search filters to the query.
     */
    private function applySearchFilters($query, array $filters)
    {
        // General search across multiple fields
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('mitigation_plan', 'like', "%{$search}%")
                  ->orWhere('impact', 'like', "%{$search}%")
                  ->orWhere('probability', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($projectQuery) use ($search) {
                      $projectQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('project_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('assignedEmployee', function ($employeeQuery) use ($search) {
                      $employeeQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('second_name', 'like', "%{$search}%")
                                   ->orWhere('third_name', 'like', "%{$search}%")
                                   ->orWhere('employee_number', 'like', "%{$search}%");
                  });
            });
        }

        // Specific field searches
        if (!empty($filters['project_number'])) {
            $query->whereHas('project', function ($projectQuery) use ($filters) {
                $projectQuery->where('project_number', 'like', "%{$filters['project_number']}%");
            });
        }

        if (!empty($filters['project_name'])) {
            $query->whereHas('project', function ($projectQuery) use ($filters) {
                $projectQuery->where('name', 'like', "%{$filters['project_name']}%");
            });
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'like', "%{$filters['title']}%");
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'like', "%{$filters['description']}%");
        }

        if (!empty($filters['impact'])) {
            $query->where('impact', $filters['impact']);
        }

        if (!empty($filters['probability'])) {
            $query->where('probability', $filters['probability']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['assigned_employee'])) {
            $query->whereHas('assignedEmployee', function ($employeeQuery) use ($filters) {
                $employeeQuery->where('first_name', 'like', "%{$filters['assigned_employee']}%")
                             ->orWhere('second_name', 'like', "%{$filters['assigned_employee']}%")
                             ->orWhere('third_name', 'like', "%{$filters['assigned_employee']}%")
                             ->orWhere('employee_number', 'like', "%{$filters['assigned_employee']}%");
            });
        }

        return $query;
    }

    /**
     * Apply field-specific filtering.
     */
    private function applyFieldFilter($query, string $field, $value)
    {
        switch ($field) {
            case 'project_number':
                $query->whereHas('project', function ($projectQuery) use ($value) {
                    $projectQuery->where('project_number', 'like', "%{$value}%");
                });
                break;
            case 'project_name':
                $query->whereHas('project', function ($projectQuery) use ($value) {
                    $projectQuery->where('name', 'like', "%{$value}%");
                });
                break;
            case 'title':
                $query->where('title', 'like', "%{$value}%");
                break;
            case 'description':
                $query->where('description', 'like', "%{$value}%");
                break;
            case 'impact':
                $query->where('impact', $value);
                break;
            case 'probability':
                $query->where('probability', $value);
                break;
            case 'status':
                $query->where('status', $value);
                break;
            case 'assigned_employee':
                $query->whereHas('assignedEmployee', function ($employeeQuery) use ($value) {
                    $employeeQuery->where('first_name', 'like', "%{$value}%")
                                 ->orWhere('second_name', 'like', "%{$value}%")
                                 ->orWhere('third_name', 'like', "%{$value}%")
                                 ->orWhere('employee_number', 'like', "%{$value}%");
                });
                break;
            case 'assigned_to':
                $query->where('assigned_to', $value);
                break;
            default:
                // For other direct fields
                if (in_array($field, ['id', 'mitigation_plan', 'created_at', 'updated_at'])) {
                    if (in_array($field, ['created_at', 'updated_at'])) {
                        $query->whereDate($field, $value);
                    } else {
                        $query->where($field, 'like', "%{$value}%");
                    }
                }
                break;
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
            'created_at', 'updated_at', 'assigned_to'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Handle relationship sorting
            switch ($sortBy) {
                case 'project_number':
                    $query->join('projects', 'project_risks.project_id', '=', 'projects.id')
                          ->orderBy('projects.project_number', $sortDirection)
                          ->select('project_risks.*');
                    break;
                case 'project_name':
                    $query->join('projects', 'project_risks.project_id', '=', 'projects.id')
                          ->orderBy('projects.name', $sortDirection)
                          ->select('project_risks.*');
                    break;
                case 'assigned_employee':
                    $query->leftJoin('employees', 'project_risks.assigned_to', '=', 'employees.id')
                          ->orderBy('employees.first_name', $sortDirection)
                          ->select('project_risks.*');
                    break;
                default:
                    // Default sorting
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        return $query;
    }

    /**
     * Get field values for dropdown filtering.
     */
    public function getFieldValues(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $field = $request->get('field');

            if (!$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field parameter is required'
                ], 400);
            }

            $values = [];

            switch ($field) {
                case 'impact':
                    $values = ProjectRisk::getImpactOptions();
                    break;
                case 'probability':
                    $values = ProjectRisk::getProbabilityOptions();
                    break;
                case 'status':
                    $values = ProjectRisk::getStatusOptions();
                    break;
                case 'title':
                    $values = ProjectRisk::forCompany($companyId)
                        ->distinct()
                        ->pluck('title')
                        ->filter()
                        ->map(function ($title) {
                            return ['value' => $title, 'label' => $title];
                        })
                        ->values();
                    break;
                case 'project_number':
                    $values = Project::where('company_id', $companyId)
                        ->distinct()
                        ->pluck('project_number')
                        ->filter()
                        ->map(function ($number) {
                            return ['value' => $number, 'label' => $number];
                        })
                        ->values();
                    break;
                case 'project_name':
                    $values = Project::where('company_id', $companyId)
                        ->distinct()
                        ->pluck('name')
                        ->filter()
                        ->map(function ($name) {
                            return ['value' => $name, 'label' => $name];
                        })
                        ->values();
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid field specified'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $values,
                'message' => 'Field values retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving field values: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for project risks.
     */
    public function getSortableFields(): JsonResponse
    {
        try {
            $sortableFields = [
                ['field' => 'id', 'label' => 'ID'],
                ['field' => 'title', 'label' => 'Risk Title'],
                ['field' => 'impact', 'label' => 'Impact'],
                ['field' => 'probability', 'label' => 'Probability'],
                ['field' => 'status', 'label' => 'Status'],
                ['field' => 'project_number', 'label' => 'Project Number'],
                ['field' => 'project_name', 'label' => 'Project Name'],
                ['field' => 'assigned_employee', 'label' => 'Assigned Employee'],
                ['field' => 'created_at', 'label' => 'Created Date'],
                ['field' => 'updated_at', 'label' => 'Updated Date'],
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
     * Sort project risks by specified field and direction.
     */
    public function sortProjectRisks(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $query = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply sorting
            $query = $this->applySorting($query, [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection
            ]);

            $perPage = $request->get('per_page', 15);
            $projectRisks = $query->paginate($perPage);

            // Transform using resource
            $projectRisks->getCollection()->transform(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $projectRisks,
                'message' => 'Project risks sorted successfully',
                'sort_info' => [
                    'sorted_by' => $sortBy,
                    'direction' => $sortDirection
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sorting project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project risk statistics and summary data.
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $statistics = $this->projectRiskService->getProjectRiskStatistics($companyId);

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Project risk statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project risks by specific project.
     */
    public function getProjectRisks($projectId): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisks = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId)
                ->forProject($projectId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform using resource
            $transformedRisks = $projectRisks->map(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedRisks,
                'message' => 'Project risks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project risks by status.
     */
    public function getByStatus($status): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisks = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId)
                ->byStatus($status)
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform using resource
            $transformedRisks = $projectRisks->map(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedRisks,
                'message' => 'Project risks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project risks by impact level.
     */
    public function getByImpact($impact): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisks = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId)
                ->byImpact($impact)
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform using resource
            $transformedRisks = $projectRisks->map(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedRisks,
                'message' => 'Project risks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project risks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project risks by probability level.
     */
    public function getByProbability($probability): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectRisks = ProjectRisk::with(['project', 'assignedEmployee', 'creator', 'updater'])
                ->forCompany($companyId)
                ->byProbability($probability)
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform using resource
            $transformedRisks = $projectRisks->map(function ($projectRisk) {
                return new ProjectRiskResource($projectRisk);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedRisks,
                'message' => 'Project risks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project risks: ' . $e->getMessage()
            ], 500);
        }
    }
}
