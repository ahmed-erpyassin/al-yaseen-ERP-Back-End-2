<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Models\ProjectResource;
use Modules\ProjectsManagment\Models\Project;
use Modules\Inventory\Models\Supplier;
use Modules\ProjectsManagment\Http\Requests\StoreResourceRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateResourceRequest;

class ResourceController extends Controller
{
    /**
     * Display a listing of resources.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply filters
            if ($request->has('project_id') && !empty($request->project_id)) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('supplier_id') && !empty($request->supplier_id)) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('resource_type') && !empty($request->resource_type)) {
                $query->where('resource_type', $request->resource_type);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('role', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhere('supplier_name', 'like', "%{$search}%")
                      ->orWhere('supplier_number', 'like', "%{$search}%")
                      ->orWhere('project_name', 'like', "%{$search}%")
                      ->orWhere('project_number', 'like', "%{$search}%")
                      ->orWhereHas('project', function ($projectQuery) use ($search) {
                          $projectQuery->where('name', 'like', "%{$search}%")
                                      ->orWhere('code', 'like', "%{$search}%")
                                      ->orWhere('project_number', 'like', "%{$search}%");
                      })
                      ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                          $supplierQuery->where('supplier_name_ar', 'like', "%{$search}%")
                                       ->orWhere('supplier_name_en', 'like', "%{$search}%")
                                       ->orWhere('supplier_code', 'like', "%{$search}%");
                      });
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSortFields = [
                'id', 'role', 'allocation_percentage', 'allocation_value', 'status',
                'resource_type', 'created_at', 'updated_at', 'project_id', 'supplier_id'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $resources = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $resources,
                'message' => 'Resources retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving resources: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreResourceRequest $request): JsonResponse
    {
        try {
            $resource = ProjectResource::create($request->validated());

            $resource->load(['project', 'supplier', 'creator']);

            return response()->json([
                'success' => true,
                'data' => $resource,
                'message' => 'Resource created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating resource: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $resource = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
                ->forCompany($companyId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $resource,
                'message' => 'Resource retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving resource: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateResourceRequest $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $resource = ProjectResource::forCompany($companyId)->findOrFail($id);
            $resource->update($request->validated());

            $resource->load(['project', 'supplier']);

            return response()->json([
                'success' => true,
                'data' => $resource,
                'message' => 'Resource updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating resource: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $resource = ProjectResource::forCompany($companyId)->findOrFail($id);

            // Set deleted_by before soft delete
            $resource->update(['deleted_by' => $user->id]);
            $resource->delete();

            return response()->json([
                'success' => true,
                'message' => 'Resource deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting resource: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suppliers for dropdown (with supplier number and name).
     */
    public function getSuppliers(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $suppliers = Supplier::where('company_id', $companyId)
                ->where('active', true)
                ->select('id', 'supplier_code', 'supplier_name_ar', 'supplier_name_en', 'contact_person', 'phone', 'email')
                ->orderBy('supplier_name_ar')
                ->get()
                ->map(function ($supplier) {
                    return [
                        'id' => $supplier->id,
                        'supplier_code' => $supplier->supplier_code,
                        'supplier_number' => $supplier->supplier_code, // Alias for consistency
                        'supplier_name_ar' => $supplier->supplier_name_ar,
                        'supplier_name_en' => $supplier->supplier_name_en,
                        'supplier_name' => $supplier->supplier_name_ar ?: $supplier->supplier_name_en,
                        'contact_person' => $supplier->contact_person,
                        'phone' => $supplier->phone,
                        'email' => $supplier->email,
                        'display_name' => ($supplier->supplier_name_ar ?: $supplier->supplier_name_en) . ($supplier->supplier_code ? " ({$supplier->supplier_code})" : ''),
                        'display_number' => $supplier->supplier_code . ($supplier->supplier_name_ar ? " - {$supplier->supplier_name_ar}" : ($supplier->supplier_name_en ? " - {$supplier->supplier_name_en}" : '')),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'message' => 'Suppliers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving suppliers: ' . $e->getMessage()
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
                ->select('id', 'code', 'project_number', 'name', 'project_value', 'currency_price')
                ->orderBy('name')
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'code' => $project->code,
                        'project_number' => $project->project_number,
                        'name' => $project->name,
                        'project_value' => $project->project_value,
                        'currency_price' => $project->currency_price,
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
     * Get resource type options.
     */
    public function getResourceTypes(): JsonResponse
    {
        $types = [
            ['value' => 'supplier', 'label' => 'Supplier'],
            ['value' => 'internal', 'label' => 'Internal'],
            ['value' => 'contractor', 'label' => 'Contractor'],
            ['value' => 'consultant', 'label' => 'Consultant'],
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
            'message' => 'Resource types retrieved successfully'
        ]);
    }

    /**
     * Get resource status options.
     */
    public function getStatusOptions(): JsonResponse
    {
        $statuses = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
            ['value' => 'completed', 'label' => 'Completed'],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses,
            'message' => 'Resource statuses retrieved successfully'
        ]);
    }

    /**
     * Calculate allocation based on project value and percentage.
     */
    public function calculateAllocation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'allocation_percentage' => 'required|numeric|min:0|max:100'
            ]);

            $user = $request->user();
            $companyId = $user->company_id;

            $project = Project::where('id', $request->project_id)
                ->where('company_id', $companyId)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or does not belong to your company'
                ], 404);
            }

            if (!$project->project_value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project value is not set for this project'
                ], 400);
            }

            $allocationValue = ($request->allocation_percentage / 100) * $project->project_value;

            return response()->json([
                'success' => true,
                'data' => [
                    'project_id' => $project->id,
                    'project_value' => $project->project_value,
                    'allocation_percentage' => $request->allocation_percentage,
                    'allocation_value' => round($allocationValue, 2)
                ],
                'message' => 'Allocation calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating allocation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate allocation percentage based on project value and allocation value.
     */
    public function calculateAllocationPercentage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'allocation_value' => 'required|numeric|min:0'
            ]);

            $user = $request->user();
            $companyId = $user->company_id;

            $project = Project::where('id', $request->project_id)
                ->where('company_id', $companyId)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or does not belong to your company'
                ], 404);
            }

            if (!$project->project_value || $project->project_value == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project value is not set or is zero for this project'
                ], 400);
            }

            $allocationPercentage = ($request->allocation_value / $project->project_value) * 100;

            return response()->json([
                'success' => true,
                'data' => [
                    'project_id' => $project->id,
                    'project_value' => $project->project_value,
                    'allocation_value' => $request->allocation_value,
                    'allocation_percentage' => round($allocationPercentage, 2)
                ],
                'message' => 'Allocation percentage calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating allocation percentage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resources for a specific project.
     */
    public function getProjectResources(Request $request, $projectId): JsonResponse
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

            $resources = ProjectResource::with(['supplier', 'creator', 'updater'])
                ->where('project_id', $projectId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $resources,
                'message' => 'Project resources retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project resources: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resources for a specific supplier.
     */
    public function getSupplierResources(Request $request, $supplierId): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            // Verify supplier belongs to user's company
            $supplier = Supplier::where('id', $supplierId)
                ->where('company_id', $companyId)
                ->first();

            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found or does not belong to your company'
                ], 404);
            }

            $resources = ProjectResource::with(['project', 'creator', 'updater'])
                ->where('supplier_id', $supplierId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $resources,
                'message' => 'Supplier resources retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving supplier resources: ' . $e->getMessage()
            ], 500);
        }
    }
}
