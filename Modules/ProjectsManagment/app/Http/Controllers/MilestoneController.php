<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Models\ProjectMilestone;
use Modules\ProjectsManagment\Models\Project;
use Modules\ProjectsManagment\Http\Requests\StoreMilestoneRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateMilestoneRequest;

class MilestoneController extends Controller
{
    /**
     * Display a listing of milestones.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            $query = ProjectMilestone::with(['project'])
                ->forCompany($companyId);

            // Apply filters
            if ($request->has('project_id') && !empty($request->project_id)) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('project', function ($projectQuery) use ($search) {
                          $projectQuery->where('name', 'like', "%{$search}%")
                                      ->orWhere('code', 'like', "%{$search}%")
                                      ->orWhere('project_number', 'like', "%{$search}%");
                      });
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['id', 'milestone_number', 'name', 'start_date', 'end_date', 'status', 'progress', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $milestones = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $milestones,
                'message' => 'Milestones retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving milestones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created milestone.
     */
    public function store(StoreMilestoneRequest $request): JsonResponse
    {
        try {
            $milestone = ProjectMilestone::create($request->validated());

            $milestone->load(['project']);

            return response()->json([
                'success' => true,
                'data' => $milestone,
                'message' => 'Milestone created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating milestone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified milestone.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $milestone = ProjectMilestone::with(['project', 'creator', 'updater'])
                ->forCompany($companyId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $milestone,
                'message' => 'Milestone retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving milestone: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified milestone.
     */
    public function update(UpdateMilestoneRequest $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $milestone = ProjectMilestone::forCompany($companyId)->findOrFail($id);
            $milestone->update($request->validated());

            $milestone->load(['project']);

            return response()->json([
                'success' => true,
                'data' => $milestone,
                'message' => 'Milestone updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating milestone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified milestone (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $milestone = ProjectMilestone::forCompany($companyId)->findOrFail($id);
            
            // Set deleted_by before soft delete
            $milestone->update(['deleted_by' => $user->id]);
            $milestone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Milestone deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting milestone: ' . $e->getMessage()
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
     * Get milestone status options.
     */
    public function getStatusOptions(): JsonResponse
    {
        $statuses = [
            ['value' => 'not_started', 'label' => 'Not Started'],
            ['value' => 'in_progress', 'label' => 'In Progress'],
            ['value' => 'completed', 'label' => 'Completed'],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses,
            'message' => 'Milestone statuses retrieved successfully'
        ]);
    }

    /**
     * Generate next milestone number for a project.
     */
    public function generateMilestoneNumber(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id'
            ]);

            $user = $request->user();
            $companyId = $user->company_id;
            $projectId = $request->project_id;

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

            $lastMilestone = ProjectMilestone::where('project_id', $projectId)
                ->orderBy('milestone_number', 'desc')
                ->first();

            $nextNumber = $lastMilestone ? ($lastMilestone->milestone_number + 1) : 1;

            return response()->json([
                'success' => true,
                'data' => ['milestone_number' => $nextNumber],
                'message' => 'Next milestone number generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating milestone number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get milestones for a specific project.
     */
    public function getProjectMilestones(Request $request, $projectId): JsonResponse
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

            $milestones = ProjectMilestone::where('project_id', $projectId)
                ->orderBy('milestone_number')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $milestones,
                'message' => 'Project milestones retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project milestones: ' . $e->getMessage()
            ], 500);
        }
    }
}
