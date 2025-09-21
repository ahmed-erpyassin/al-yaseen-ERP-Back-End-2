<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\ProjectsManagment\Models\ProjectResource;
use Modules\ProjectsManagment\Models\Project;
use Modules\Inventory\Models\Supplier;
use Modules\ProjectsManagment\Http\Requests\StoreResourceRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateResourceRequest;

/**
 * @group Project Management / Resources
 *
 * APIs for managing project resources, including supplier allocation, resource tracking, and allocation calculations.
 */

class ResourceController extends Controller
{
    /**
     * List Project Resources
     *
     * Retrieve a paginated list of project resources with comprehensive filtering and search capabilities.
     *
     * @queryParam project_id integer Filter by project ID. Example: 1
     * @queryParam supplier_id integer Filter by supplier ID. Example: 1
     * @queryParam resource_type string Filter by resource type. Example: material
     * @queryParam status string Filter by resource status. Example: allocated
     * @queryParam supplier_number string Search by supplier number. Example: SUP-001
     * @queryParam supplier_name string Search by supplier name. Example: ABC Supplier
     * @queryParam project_number string Search by project number. Example: PRJ-001
     * @queryParam project_name string Search by project name. Example: Website Development
     * @queryParam allocation_from numeric Filter resources with allocation from this value. Example: 1000.00
     * @queryParam allocation_to numeric Filter resources with allocation to this value. Example: 5000.00
     * @queryParam date_from string Filter by date from (Y-m-d format). Example: 2024-01-01
     * @queryParam date_to string Filter by date to (Y-m-d format). Example: 2024-12-31
     * @queryParam sort_field string Field to sort by. Example: created_at
     * @queryParam sort_direction string Sort direction (asc/desc). Example: desc
     * @queryParam per_page integer Number of items per page (default: 15). Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "project": {
     *         "id": 1,
     *         "project_number": "PRJ-001",
     *         "project_name": "Website Development"
     *       },
     *       "supplier": {
     *         "id": 1,
     *         "supplier_number": "SUP-001",
     *         "supplier_name": "ABC Supplier"
     *       },
     *       "resource_type": "material",
     *       "allocation": 2500.00,
     *       "status": "allocated",
     *       "created_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ],
     *   "message": "Resources retrieved successfully"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving resources: Database connection failed"
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            // $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
            //     ->forCompany($companyId);

            $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater']);
                
                
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

            // Apply advanced search filters
            $this->applySearchFilters($query, $request);

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
            $user = Auth::user();
            // $companyId = $user->company_id;

            // $resource = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
            //     ->forCompany($companyId)
            //     ->findOrFail($id);


            $resource = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
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
            $user = Auth::user();
            // $companyId = $user->company_id;

            // $resource = ProjectResource::forCompany($companyId)->findOrFail($id);
            $resource = ProjectResource::findOrFail($id);

            // Store original data for audit trail
            $originalData = $resource->toArray();

            // Update the resource
            $validatedData = $request->validated();
            $validatedData['updated_by'] = $user->id;

            $resource->update($validatedData);

            // Load relationships for response
            $resource->load(['project', 'supplier', 'creator', 'updater']);

            // Log the update activity (optional - can be implemented later)
            // $this->logResourceActivity($resource, 'updated', $originalData, $resource->toArray());

            return response()->json([
                'success' => true,
                'data' => $resource,
                'message' => 'Resource updated successfully',
                'updated_fields' => array_keys($validatedData)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found or does not belong to your company'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
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
            $user = Auth::user();
            // $companyId = $user->company_id;

            // $resource = ProjectResource::forCompany($companyId)->findOrFail($id);
            $resource = ProjectResource::findOrFail($id);


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
            $user = Auth::user();
            // $companyId = $user->company_id;

            $suppliers = Supplier::where('active', true)
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
            $user = Auth::user();
            // $companyId = $user->company_id;

            $projects = Project::where('status', '!=', 'cancelled')
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

            $user = Auth::user();
            // $companyId = $user->company_id;

            // $project = Project::where('id', $request->project_id)
            //     ->where('company_id', $companyId)
            //     ->first();

            $project = Project::where('id', $request->project_id)
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

            $user = Auth::user();
            // $companyId = $user->company_id;

            // $project = Project::where('id', $request->project_id)
            //     ->where('company_id', $companyId)
            //     ->first();

            $project = Project::where('id', $request->project_id)
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
            $user = Auth::user();
            // $companyId = $user->company_id;

            // Verify project belongs to user's company
            // $project = Project::where('id', $projectId)
            //     ->where('company_id', $companyId)
            //     ->first();

            $project = Project::where('id', $projectId)
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
            $user = Auth::user();
            // $companyId = $user->company_id;

            // Verify supplier belongs to user's company
            // $supplier = Supplier::where('id', $supplierId)
            //     ->where('company_id', $companyId)
            //     ->first();

            $supplier = Supplier::where('id', $supplierId)
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

    /**
     * Advanced search for resources.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            // $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
            //     ->forCompany($companyId);

            $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater']);                

            // Apply advanced search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            $resources = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $resources,
                'message' => 'Resources search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching resources: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resources by specific field value.
     */
    public function getResourcesByField(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;

            $field = $request->get('field');
            $value = $request->get('value');
            $perPage = $request->get('per_page', 15);

            if (!$field || !$value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field and value parameters are required'
                ], 400);
            }

            $allowedFields = [
                'supplier_id', 'project_id', // Use IDs instead of redundant fields
                'role', 'resource_type', 'status',
                'allocation_percentage', 'allocation_value'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            // $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
            //     ->forCompany($companyId);

            $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater']);

            // Apply field filter
            if (in_array($field, ['allocation_percentage', 'allocation_value'])) {
                $query->where($field, '=', $value);
            } else {
                $query->where($field, 'like', "%{$value}%");
            }

            $resources = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $resources,
                'message' => "Resources filtered by {$field} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering resources: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unique values for a specific field.
     */
    public function getFieldValues(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;

            $field = $request->get('field');

            if (!$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field parameter is required'
                ], 400);
            }

            $allowedFields = [
                'supplier_id', 'project_id', // Use IDs instead of redundant fields
                'role', 'resource_type', 'status', 'allocation_percentage', 'allocation_value'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            $values = ProjectResource::whereNotNull($field)
                ->where($field, '!=', '')
                ->distinct()
                ->pluck($field)
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $values,
                'message' => "Unique values for {$field} retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving field values: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for resources.
     */
    public function getSortableFields(): JsonResponse
    {
        $fields = [
            ['field' => 'id', 'label' => 'ID'],
            ['field' => 'supplier_id', 'label' => 'Supplier ID'],
            ['field' => 'project_id', 'label' => 'Project ID'],
            ['field' => 'role', 'label' => 'Role'],
            ['field' => 'resource_type', 'label' => 'Resource Type'],
            ['field' => 'status', 'label' => 'Status'],
            ['field' => 'allocation_percentage', 'label' => 'Allocation Percentage'],
            ['field' => 'allocation_value', 'label' => 'Allocation Value'],
            ['field' => 'created_at', 'label' => 'Created Date'],
            ['field' => 'updated_at', 'label' => 'Updated Date'],
        ];

        return response()->json([
            'success' => true,
            'data' => $fields,
            'message' => 'Sortable fields retrieved successfully'
        ]);
    }

    /**
     * Sort resources by specified field and order.
     */
    public function sortResources(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater'])
            //     ->forCompany($companyId);

            $query = ProjectResource::with(['project', 'supplier', 'creator', 'updater']);

            // Apply sorting
            $this->applySorting($query, $request);

            $resources = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $resources,
                'message' => "Resources sorted by {$sortBy} ({$sortOrder}) successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sorting resources: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply search filters to the query.
     */
    private function applySearchFilters($query, Request $request): void
    {
        // General search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('role', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('allocation_percentage', 'like', "%{$search}%")
                  ->orWhere('allocation_value', 'like', "%{$search}%")
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

        // Specific field searches - Use relationships instead of redundant fields
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('project_id') && !empty($request->project_id)) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', 'like', "%{$request->role}%");
        }

        if ($request->has('allocation') && !empty($request->allocation)) {
            $allocation = $request->allocation;
            $query->where(function ($q) use ($allocation) {
                $q->where('allocation_percentage', 'like', "%{$allocation}%")
                  ->orWhere('allocation_value', 'like', "%{$allocation}%")
                  ->orWhere('allocation', 'like', "%{$allocation}%");
            });
        }

        // Range searches for allocation
        if ($request->has('allocation_percentage_min') && !empty($request->allocation_percentage_min)) {
            $query->where('allocation_percentage', '>=', $request->allocation_percentage_min);
        }

        if ($request->has('allocation_percentage_max') && !empty($request->allocation_percentage_max)) {
            $query->where('allocation_percentage', '<=', $request->allocation_percentage_max);
        }

        if ($request->has('allocation_value_min') && !empty($request->allocation_value_min)) {
            $query->where('allocation_value', '>=', $request->allocation_value_min);
        }

        if ($request->has('allocation_value_max') && !empty($request->allocation_value_max)) {
            $query->where('allocation_value', '<=', $request->allocation_value_max);
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = [
            'id', 'supplier_id', 'project_id', 'role', 'allocation_percentage',
            'allocation_value', 'status', 'resource_type', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Add secondary sorting for consistency
        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restore($id): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;

            $resource = ProjectResource::withTrashed()
                ->findOrFail($id);

            // $resource = ProjectResource::withTrashed()
            //     ->where('company_id', $companyId)
            //     ->findOrFail($id);                

            if (!$resource->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource is not deleted'
                ], 400);
            }

            $resource->restore();
            $resource->update(['deleted_by' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Resource restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring resource: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a resource.
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;

            // $resource = ProjectResource::withTrashed()
            //     ->where('company_id', $companyId)
            //     ->findOrFail($id);

            $resource = ProjectResource::withTrashed()
                ->findOrFail($id);
                
                
            $resource->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Resource permanently deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting resource: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed (soft-deleted) resources.
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            // $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $resources = ProjectResource::onlyTrashed()
                ->with(['project', 'supplier', 'creator', 'updater', 'deleter'])
                // ->where('company_id', $companyId)
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $resources,
                'message' => 'Trashed resources retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed resources: ' . $e->getMessage()
            ], 500);
        }
    }
}
