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

            $projectRisk = ProjectRisk::forCompany($companyId)->findOrFail($id);
            
            $data = $request->validated();
            $data['updated_by'] = $user->id;

            $projectRisk->update($data);
            $projectRisk->load(['project', 'assignedEmployee', 'creator', 'updater']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ProjectRiskResource($projectRisk),
                'message' => 'Project risk updated successfully'
            ]);
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
            $projectRisk->update(['deleted_by' => $user->id]);
            $projectRisk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project risk deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting project risk: ' . $e->getMessage()
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
}
