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

            // Advanced search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhere('milestone_number', 'like', "%{$search}%")
                      ->orWhereHas('project', function ($projectQuery) use ($search) {
                          $projectQuery->where('name', 'like', "%{$search}%")
                                      ->orWhere('code', 'like', "%{$search}%")
                                      ->orWhere('project_number', 'like', "%{$search}%");
                      });
                });
            }

            // Search by milestone number
            if ($request->has('milestone_number') && !empty($request->milestone_number)) {
                $query->where('milestone_number', $request->milestone_number);
            }

            // Search by project number
            if ($request->has('project_number') && !empty($request->project_number)) {
                $query->whereHas('project', function ($projectQuery) use ($request) {
                    $projectQuery->where('project_number', 'like', "%{$request->project_number}%");
                });
            }

            // Search by milestone name
            if ($request->has('milestone_name') && !empty($request->milestone_name)) {
                $query->where('name', 'like', "%{$request->milestone_name}%");
            }

            // Search by start date (exact date)
            if ($request->has('start_date') && !empty($request->start_date)) {
                $query->whereDate('start_date', $request->start_date);
            }

            // Search by start date range (from/to)
            if ($request->has('start_date_from') && !empty($request->start_date_from)) {
                $query->whereDate('start_date', '>=', $request->start_date_from);
            }
            if ($request->has('start_date_to') && !empty($request->start_date_to)) {
                $query->whereDate('start_date', '<=', $request->start_date_to);
            }

            // Search by end date (exact date)
            if ($request->has('end_date') && !empty($request->end_date)) {
                $query->whereDate('end_date', $request->end_date);
            }

            // Search by end date range (from/to)
            if ($request->has('end_date_from') && !empty($request->end_date_from)) {
                $query->whereDate('end_date', '>=', $request->end_date_from);
            }
            if ($request->has('end_date_to') && !empty($request->end_date_to)) {
                $query->whereDate('end_date', '<=', $request->end_date_to);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // All sortable fields from project_milestones table
            $allowedSortFields = [
                'id', 'milestone_number', 'name', 'description', 'start_date', 'end_date',
                'status', 'progress', 'notes', 'created_at', 'updated_at',
                'user_id', 'company_id', 'branch_id', 'fiscal_year_id', 'project_id',
                'created_by', 'updated_by', 'deleted_by'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // Default sorting
                $query->orderBy('created_at', 'desc');
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
     * Restore a soft-deleted milestone.
     */
    public function restore($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $milestone = ProjectMilestone::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            $milestone->restore();
            $milestone->update(['deleted_by' => null]);

            return response()->json([
                'success' => true,
                'data' => $milestone,
                'message' => 'Milestone restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring milestone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a milestone.
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $milestone = ProjectMilestone::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            $milestone->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Milestone permanently deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting milestone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed (soft-deleted) milestones.
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $milestones = ProjectMilestone::onlyTrashed()
                ->with(['project', 'creator', 'updater', 'deleter'])
                ->forCompany($companyId)
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $milestones,
                'message' => 'Trashed milestones retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed milestones: ' . $e->getMessage()
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

    /**
     * Advanced search for milestones.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $query = ProjectMilestone::with(['project', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply all search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            $milestones = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $milestones,
                'message' => 'Milestone search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching milestones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get milestones filtered by specific field value.
     */
    public function getMilestonesByField(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $field = $request->get('field');
            $value = $request->get('value');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 15);

            if (!$field || !$value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field and value parameters are required'
                ], 400);
            }

            $query = ProjectMilestone::with(['project', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply field filter
            $allowedFields = [
                'milestone_number', 'name', 'status', 'progress', 'project_id',
                'start_date', 'end_date', 'created_by', 'updated_by'
            ];

            if (in_array($field, $allowedFields)) {
                if (in_array($field, ['start_date', 'end_date'])) {
                    $query->whereDate($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }

            // Apply sorting
            $this->applySorting($query, $request);

            $milestones = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $milestones,
                'message' => "Milestones filtered by {$field} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering milestones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all field values for dropdown filtering.
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

            $allowedFields = [
                'milestone_number', 'name', 'status', 'progress', 'project_id',
                'start_date', 'end_date'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            $values = ProjectMilestone::forCompany($companyId)
                ->select($field)
                ->distinct()
                ->whereNotNull($field)
                ->orderBy($field)
                ->pluck($field)
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $values,
                'message' => "Field values for {$field} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving field values: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields list.
     */
    public function getSortableFields(): JsonResponse
    {
        $sortableFields = [
            ['field' => 'id', 'label' => 'ID'],
            ['field' => 'milestone_number', 'label' => 'Milestone Number'],
            ['field' => 'name', 'label' => 'Milestone Name'],
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'start_date', 'label' => 'Start Date'],
            ['field' => 'end_date', 'label' => 'End Date'],
            ['field' => 'status', 'label' => 'Status'],
            ['field' => 'progress', 'label' => 'Progress'],
            ['field' => 'notes', 'label' => 'Notes'],
            ['field' => 'created_at', 'label' => 'Created Date'],
            ['field' => 'updated_at', 'label' => 'Updated Date'],
            ['field' => 'project_id', 'label' => 'Project'],
            ['field' => 'created_by', 'label' => 'Created By'],
            ['field' => 'updated_by', 'label' => 'Updated By'],
        ];

        return response()->json([
            'success' => true,
            'data' => $sortableFields,
            'message' => 'Sortable fields retrieved successfully'
        ]);
    }

    /**
     * Sort milestones by specified field and order.
     */
    public function sortMilestones(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $query = ProjectMilestone::with(['project', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply sorting
            $this->applySorting($query, $request);

            $milestones = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $milestones,
                'message' => 'Milestones sorted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sorting milestones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply search filters to query.
     */
    private function applySearchFilters($query, $request)
    {
        // General search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('milestone_number', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($projectQuery) use ($search) {
                      $projectQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('code', 'like', "%{$search}%")
                                  ->orWhere('project_number', 'like', "%{$search}%");
                  });
            });
        }

        // Specific field searches
        if ($request->has('milestone_number') && !empty($request->milestone_number)) {
            $query->where('milestone_number', $request->milestone_number);
        }

        if ($request->has('project_number') && !empty($request->project_number)) {
            $query->whereHas('project', function ($projectQuery) use ($request) {
                $projectQuery->where('project_number', 'like', "%{$request->project_number}%");
            });
        }

        if ($request->has('milestone_name') && !empty($request->milestone_name)) {
            $query->where('name', 'like', "%{$request->milestone_name}%");
        }

        // Date filters
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('start_date', $request->start_date);
        }

        if ($request->has('start_date_from') && !empty($request->start_date_from)) {
            $query->whereDate('start_date', '>=', $request->start_date_from);
        }

        if ($request->has('start_date_to') && !empty($request->start_date_to)) {
            $query->whereDate('start_date', '<=', $request->start_date_to);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('end_date', $request->end_date);
        }

        if ($request->has('end_date_from') && !empty($request->end_date_from)) {
            $query->whereDate('end_date', '>=', $request->end_date_from);
        }

        if ($request->has('end_date_to') && !empty($request->end_date_to)) {
            $query->whereDate('end_date', '<=', $request->end_date_to);
        }

        // Other filters
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id') && !empty($request->project_id)) {
            $query->where('project_id', $request->project_id);
        }
    }

    /**
     * Apply sorting to query.
     */
    private function applySorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = [
            'id', 'milestone_number', 'name', 'description', 'start_date', 'end_date',
            'status', 'progress', 'notes', 'created_at', 'updated_at',
            'user_id', 'company_id', 'branch_id', 'fiscal_year_id', 'project_id',
            'created_by', 'updated_by', 'deleted_by'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
