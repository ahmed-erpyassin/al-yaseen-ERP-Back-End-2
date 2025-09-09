<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Models\Project;
use Modules\ProjectsManagment\Http\Requests\StoreProjectRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateProjectRequest;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Company;

class ProjectsManagmentController extends Controller
{
    /**
     * Display a listing of the resource with advanced search and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            // Get search filters from request
            $filters = $request->only([
                'project_number', 'project_name', 'customer_name', 'status',
                'project_manager_name', 'exact_date', 'date_from', 'date_to',
                'start_date_from', 'start_date_to', 'end_date_from', 'end_date_to',
                'general_search'
            ]);

            // Get sorting parameters
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $perPage = $request->get('per_page', 15);

            // Build query with search and sorting
            $projects = Project::with([
                    'customer', 'currency', 'manager', 'country', 'company', 'branch'
                ])
                ->forCompany($companyId)
                ->search($filters)
                ->sortBy($sortField, $sortDirection)
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projects,
                'filters_applied' => $filters,
                'sorting' => [
                    'field' => $sortField,
                    'direction' => $sortDirection
                ],
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

            // Auto-populate customer information if customer_id is provided
            if (isset($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    $data['customer_name'] = $customer->first_name . ' ' . $customer->second_name;
                    $data['customer_email'] = $customer->email;
                    $data['customer_phone'] = $customer->phone;
                    $data['licensed_operator'] = $customer->contact_name ?? '';
                }
            }

            // Calculate VAT if needed
            if ($data['include_vat'] && isset($data['currency_price'])) {
                $company = Company::find($data['company_id']);
                if ($company && $company->vat_rate > 0) {
                    $vatAmount = $data['currency_price'] * ($company->vat_rate / 100);
                    $data['currency_price'] = $data['currency_price'] + $vatAmount;
                }
            }

            // Set additional system fields
            $data['user_id'] = $request->user()->id;
            $data['created_by'] = $request->user()->id;

            $project = Project::create($data);

            // Load relationships for response
            $project->load(['customer', 'currency', 'manager', 'country', 'company']);

            return response()->json([
                'success' => true,
                'data' => $project,
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
            $companyId = $user->company_id;

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
                'sort_field' => 'nullable|string',
                'sort_direction' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            // Get search filters
            $filters = $request->only([
                'project_number', 'project_name', 'customer_name', 'status',
                'project_manager_name', 'exact_date', 'date_from', 'date_to',
                'start_date_from', 'start_date_to', 'end_date_from', 'end_date_to',
                'general_search'
            ]);

            // Get sorting parameters
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $perPage = $request->get('per_page', 15);

            // Build query
            $projects = Project::with([
                    'customer', 'currency', 'manager', 'country', 'company', 'branch'
                ])
                ->forCompany($companyId)
                ->search($filters)
                ->sortBy($sortField, $sortDirection)
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projects,
                'search_criteria' => $filters,
                'sorting' => [
                    'field' => $sortField,
                    'direction' => $sortDirection
                ],
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
            $project = Project::with([
                'customer', 'currency', 'manager', 'country', 'company', 'branch',
                'fiscalYear', 'costCenter', 'creator', 'updater', 'deleter',
                'milestones', 'tasks', 'resources', 'documents', 'financials', 'risks'
            ])->findOrFail($id);

            // Check if user has permission to view this project
            $user = request()->user();
            if ($project->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this project'
                ], 403);
            }

            // Calculate additional project metrics
            $projectData = $project->toArray();

            // Add calculated fields
            $projectData['calculated_fields'] = [
                'vat_amount' => $project->calculateVATAmount(),
                'total_price_with_vat' => $project->getTotalPriceWithVAT(),
                'days_remaining' => $project->end_date ? now()->diffInDays($project->end_date, false) : null,
                'project_duration_days' => $project->start_date && $project->end_date ?
                    $project->start_date->diffInDays($project->end_date) : null,
                'is_overdue' => $project->end_date ? now()->isAfter($project->end_date) : false,
                'completion_percentage' => $project->progress ?? 0,
            ];

            // Add project statistics
            $projectData['statistics'] = [
                'total_milestones' => $project->milestones->count(),
                'completed_milestones' => $project->milestones->where('status', 'completed')->count(),
                'total_tasks' => $project->tasks->count(),
                'completed_tasks' => $project->tasks->where('status', 'done')->count(),
                'total_documents' => $project->documents->count(),
                'total_risks' => $project->risks->count(),
                'open_risks' => $project->risks->where('status', 'open')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $projectData,
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
            $project = Project::findOrFail($id);

            // Check if user has permission to update this project
            if ($project->company_id !== $request->user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this project'
                ], 403);
            }

            $data = $request->validated();

            // Auto-populate customer information if customer_id is changed
            if (isset($data['customer_id']) && $data['customer_id'] !== $project->customer_id) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    $data['customer_name'] = $customer->first_name . ' ' . $customer->second_name;
                    $data['customer_email'] = $customer->email;
                    $data['customer_phone'] = $customer->phone;
                    $data['licensed_operator'] = $customer->contact_name ?? '';
                }
            }

            // Recalculate VAT if needed
            if (isset($data['include_vat']) && isset($data['currency_price'])) {
                if ($data['include_vat'] && $data['currency_price']) {
                    $company = Company::find($project->company_id);
                    if ($company && $company->vat_rate > 0) {
                        $vatAmount = $data['currency_price'] * ($company->vat_rate / 100);
                        $data['currency_price'] = $data['currency_price'] + $vatAmount;
                    }
                }
            }

            // Set system fields
            $data['updated_by'] = $request->user()->id;

            // Update the project
            $project->update($data);

            // Load relationships for response
            $project->load([
                'customer', 'currency', 'manager', 'country', 'company', 'branch',
                'creator', 'updater'
            ]);

            return response()->json([
                'success' => true,
                'data' => $project,
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
            $companyId = $user->company_id;

            $request->validate([
                'field' => 'required|string',
                'value' => 'required',
                'sort_field' => 'nullable|string',
                'sort_direction' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $field = $request->field;
            $value = $request->value;
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $perPage = $request->get('per_page', 15);

            // Define allowed fields for security
            $allowedFields = [
                'status', 'customer_name', 'project_manager_name', 'country_id',
                'currency_id', 'manager_id', 'customer_id', 'project_number',
                'name', 'start_date', 'end_date', 'project_date'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            // Build query based on field type
            $query = Project::with([
                    'customer', 'currency', 'manager', 'country', 'company', 'branch'
                ])
                ->forCompany($companyId);

            // Apply field-specific filtering
            if (in_array($field, ['status', 'customer_name', 'project_manager_name', 'project_number', 'name'])) {
                $query->where($field, 'like', '%' . $value . '%');
            } elseif (in_array($field, ['country_id', 'currency_id', 'manager_id', 'customer_id'])) {
                $query->where($field, $value);
            } elseif (in_array($field, ['start_date', 'end_date', 'project_date'])) {
                $query->whereDate($field, $value);
            }

            $projects = $query->sortBy($sortField, $sortDirection)->paginate($perPage);

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
            $companyId = $request->user()->company_id;
            $year = date('Y');

            $lastProject = Project::where('company_id', $companyId)
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastProject ? (intval(substr($lastProject->code, -4)) + 1) : 1;
            $code = 'PRJ-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

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
            $project = Project::findOrFail($id);

            // Check if user has permission to delete this project
            if ($project->company_id !== $user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this project'
                ], 403);
            }

            // Check if project can be deleted (business logic)
            if ($project->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a closed project'
                ], 422);
            }

            // Set deleted_by before soft delete
            $project->deleted_by = $user->id;
            $project->save();

            // Perform soft delete
            $project->delete();

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
