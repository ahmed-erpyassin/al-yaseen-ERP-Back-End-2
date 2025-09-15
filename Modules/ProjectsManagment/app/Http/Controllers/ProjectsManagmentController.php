<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Services\ProjectService;
use Modules\ProjectsManagment\Http\Resources\ProjectResource;
use Modules\ProjectsManagment\Http\Requests\StoreProjectRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateProjectRequest;

class ProjectsManagmentController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Display a listing of the resource with advanced search and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get search filters from request
            $filters = $request->only([
                'project_number', 'project_name', 'customer_name', 'status',
                'project_manager_name', 'exact_date', 'date_from', 'date_to',
                'start_date_from', 'start_date_to', 'end_date_from', 'end_date_to',
                'general_search', 'sort_field', 'sort_direction'
            ]);

            $perPage = $request->get('per_page', 15);

            // Get projects using service
            $projects = $this->projectService->getProjects($user, $filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => ProjectResource::collection($projects),
                'filters_applied' => $filters,
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
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            // Create project using service
            $project = $this->projectService->createProject($data, $user);

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
                'message' => 'Project created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for projects
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validate search parameters
            $request->validate([
                'project_number' => 'nullable|string|max:255',
                'project_name' => 'nullable|string|max:255',
                'customer_name' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:draft,open,on-hold,cancelled,closed',
                'project_manager_name' => 'nullable|string|max:255',
                'exact_date' => 'nullable|date',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'start_date_from' => 'nullable|date',
                'start_date_to' => 'nullable|date|after_or_equal:start_date_from',
                'end_date_from' => 'nullable|date',
                'end_date_to' => 'nullable|date|after_or_equal:end_date_from',
                'general_search' => 'nullable|string|max:255',
                'sort_by' => 'nullable|string',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            // Get search parameters
            $searchParams = $request->only([
                'project_number', 'project_name', 'customer_name', 'status',
                'project_manager_name', 'exact_date', 'date_from', 'date_to',
                'start_date_from', 'start_date_to', 'end_date_from', 'end_date_to',
                'general_search', 'sort_by', 'sort_order', 'search'
            ]);

            $perPage = $request->get('per_page', 15);

            // Search projects using service
            $projects = $this->projectService->searchProjects($user, $searchParams, $perPage);

            return response()->json([
                'success' => true,
                'data' => ProjectResource::collection($projects),
                'search_criteria' => $searchParams,
                'message' => 'Search completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing search: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource with comprehensive data.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();

            // Get project using service
            $project = $this->projectService->getProjectById($id, $user);

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
                'message' => 'Project retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            // Update project using service
            $project = $this->projectService->updateProject($id, $data, $user);

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
                'message' => 'Project updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get projects by specific field value for dynamic selection display
     */
    public function getProjectsByField(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $request->validate([
                'field' => 'required|string',
                'value' => 'required',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $field = $request->field;
            $value = $request->value;
            $perPage = $request->get('per_page', 15);

            // Get projects by field using service
            $projects = $this->projectService->getProjectsByField($user, $field, $value, $perPage);

            return response()->json([
                'success' => true,
                'data' => $projects,
                'filter_applied' => [
                    'field' => $field,
                    'value' => $value
                ],
                'sorting' => [
                    'field' => $sortField,
                    'direction' => $sortDirection
                ],
                'message' => 'Projects filtered by ' . $field . ' retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unique values for a specific field for dropdown/selection
     */
    public function getFieldValues(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $request->validate([
                'field' => 'required|string'
            ]);

            $field = $request->field;

            // Define allowed fields and their display names
            $allowedFields = [
                'status' => 'Status',
                'customer_name' => 'Customer Name',
                'project_manager_name' => 'Project Manager Name',
                'country_id' => 'Country',
                'currency_id' => 'Currency',
                'manager_id' => 'Manager',
                'customer_id' => 'Customer'
            ];

            if (!array_key_exists($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            // Get unique values for the field
            $values = Project::forCompany($companyId)
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->distinct()
                ->pluck($field)
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'field' => $field,
                    'field_name' => $allowedFields[$field],
                    'values' => $values
                ],
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
     * Get customer data when customer is selected
     */
    public function getCustomerData($customerId): JsonResponse
    {
        try {
            $customer = Customer::with(['currency', 'country'])->findOrFail($customerId);

            return response()->json([
                'success' => true,
                'data' => [
                    'customer_name' => $customer->first_name . ' ' . $customer->second_name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'licensed_operator' => $customer->contact_name ?? '',
                    'currency_id' => $customer->currency_id,
                    'country_id' => $customer->country_id,
                ],
                'message' => 'Customer data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
    }

    /**
     * Get all customers for dropdown
     */
    public function getCustomers(Request $request): JsonResponse
    {
        try {
            $companyId = $request->user()->company_id;

            $customers = Customer::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'first_name', 'second_name', 'email', 'phone')
                ->orderBy('first_name')
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->first_name . ' ' . $customer->second_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers,
                'message' => 'Customers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all currencies for dropdown
     */
    public function getCurrencies(Request $request): JsonResponse
    {
        try {
            $companyId = $request->user()->company_id;

            $currencies = Currency::where('company_id', $companyId)
                ->select('id', 'code', 'name', 'symbol')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $currencies,
                'message' => 'Currencies retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving currencies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all employees/users for project manager dropdown
     */
    public function getEmployees(Request $request): JsonResponse
    {
        try {
            $employees = User::where('status', 'active')
                ->select('id', 'first_name', 'second_name', 'email')
                ->orderBy('first_name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->second_name,
                        'email' => $user->email,
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
     * Get all countries for dropdown
     */
    public function getCountries(): JsonResponse
    {
        try {
            $countries = Country::select('id', 'name', 'name_en', 'code')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $countries,
                'message' => 'Countries retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving countries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project statuses for dropdown
     */
    public function getProjectStatuses(): JsonResponse
    {
        $statuses = [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'open', 'label' => 'Open'],
            ['value' => 'on-hold', 'label' => 'On Hold'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
            ['value' => 'closed', 'label' => 'Closed'],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses,
            'message' => 'Project statuses retrieved successfully'
        ]);
    }

    /**
     * Get sortable fields for projects
     */
    public function getSortableFields(): JsonResponse
    {
        $sortableFields = [
            'id' => 'ID',
            'code' => 'Project Code',
            'project_number' => 'Project Number',
            'name' => 'Project Name',
            'customer_name' => 'Customer Name',
            'project_manager_name' => 'Project Manager Name',
            'status' => 'Status',
            'project_value' => 'Project Value',
            'currency_price' => 'Currency Price',
            'budget' => 'Budget',
            'actual_cost' => 'Actual Cost',
            'progress' => 'Progress',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'project_date' => 'Project Date',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date'
        ];

        return response()->json([
            'success' => true,
            'data' => $sortableFields,
            'message' => 'Sortable fields retrieved successfully'
        ]);
    }

    /**
     * Sort projects by specific field with first/last functionality
     */
    public function sortProjects(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $request->validate([
                'sort_field' => 'required|string',
                'sort_direction' => 'required|in:asc,desc,first,last',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $sortField = $request->sort_field;
            $sortDirection = $request->sort_direction;
            $perPage = $request->get('per_page', 15);

            // Convert first/last to asc/desc
            if ($sortDirection === 'first') {
                $sortDirection = 'asc';
            } elseif ($sortDirection === 'last') {
                $sortDirection = 'desc';
            }

            // Get projects with sorting
            $projects = Project::with([
                    'customer', 'currency', 'manager', 'country', 'company', 'branch'
                ])
                ->forCompany($companyId)
                ->sortBy($sortField, $sortDirection)
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projects,
                'sorting' => [
                    'field' => $sortField,
                    'direction' => $sortDirection,
                    'original_direction' => $request->sort_direction
                ],
                'message' => 'Projects sorted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sorting projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate VAT for given price and company
     */
    public function calculateVAT(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
                'company_id' => 'required|exists:companies,id',
                'include_vat' => 'boolean'
            ]);

            $price = $request->price;
            $includeVat = $request->boolean('include_vat');

            if (!$includeVat) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'original_price' => $price,
                        'vat_amount' => 0,
                        'total_price' => $price,
                        'vat_rate' => 0
                    ]
                ]);
            }

            $company = Company::find($request->company_id);
            $vatRate = $company->vat_rate ?? 0;
            $vatAmount = $price * ($vatRate / 100);
            $totalPrice = $price + $vatAmount;

            return response()->json([
                'success' => true,
                'data' => [
                    'original_price' => $price,
                    'vat_amount' => $vatAmount,
                    'total_price' => $totalPrice,
                    'vat_rate' => $vatRate
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating VAT: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next project code
     */
    public function generateProjectCode(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Generate project code using service
            $code = $this->projectService->generateProjectCode($user);

            return response()->json([
                'success' => true,
                'data' => ['code' => $code],
                'message' => 'Project code generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating project code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a project (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();

            // Delete project using service
            $this->projectService->deleteProject($id, $user);

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted project
     */
    public function restore($id): JsonResponse
    {
        try {
            $user = request()->user();
            $project = Project::withTrashed()->findOrFail($id);

            // Check if user has permission to restore this project
            if ($project->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to restore this project'
                ], 403);
            }

            // Restore the project
            $project->restore();

            // Clear deleted_by field
            $project->deleted_by = null;
            $project->save();

            return response()->json([
                'success' => true,
                'data' => $project,
                'message' => 'Project restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a project (force delete)
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $user = request()->user();
            $project = Project::withTrashed()->findOrFail($id);

            // Check if user has permission to permanently delete this project
            if ($project->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to permanently delete this project'
                ], 403);
            }

            // Additional authorization check - only admin or project creator can force delete
            if ($user->role !== 'admin' && $project->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators or project creators can permanently delete projects'
                ], 403);
            }

            // Force delete (permanent)
            $project->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Project permanently deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed (soft-deleted) projects
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $perPage = $request->get('per_page', 15);

            $trashedProjects = Project::onlyTrashed()
                ->with(['customer', 'currency', 'manager', 'country', 'company', 'deleter'])
                ->forCompany($companyId)
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $trashedProjects,
                'message' => 'Trashed projects retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed projects: ' . $e->getMessage()
            ], 500);
        }
    }
}
