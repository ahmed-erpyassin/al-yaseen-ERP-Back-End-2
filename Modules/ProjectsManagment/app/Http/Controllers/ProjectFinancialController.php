<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Models\ProjectFinancial;
use Modules\ProjectsManagment\Models\Project;
use Modules\FinancialAccounts\Models\Currency;
use Modules\ProjectsManagment\Http\Requests\StoreProjectFinancialRequest;
use Modules\ProjectsManagment\Http\Requests\UpdateProjectFinancialRequest;
use Modules\ProjectsManagment\Http\Resources\ProjectFinancialResource;

class ProjectFinancialController extends Controller
{
    /**
     * Display a listing of project financials.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            $query = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply filters
            if ($request->has('project_id') && !empty($request->project_id)) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('currency_id') && !empty($request->currency_id)) {
                $query->where('currency_id', $request->currency_id);
            }

            if ($request->has('reference_type') && !empty($request->reference_type)) {
                $query->where('reference_type', $request->reference_type);
            }

            if ($request->has('reference_id') && !empty($request->reference_id)) {
                $query->where('reference_id', $request->reference_id);
            }

            // Apply advanced search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSortFields = [
                'id', 'project_id', 'currency_id', 'exchange_rate', 'reference_type',
                'reference_id', 'amount', 'date', 'description', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $projectFinancials = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projectFinancials,
                'message' => 'Project financials retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project financials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created project financial.
     */
    public function store(StoreProjectFinancialRequest $request): JsonResponse
    {
        try {
            $projectFinancial = ProjectFinancial::create($request->validated());

            $projectFinancial->load(['project', 'currency', 'creator']);

            return response()->json([
                'success' => true,
                'data' => new ProjectFinancialResource($projectFinancial),
                'message' => 'Project financial created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating project financial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified project financial.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectFinancial = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new ProjectFinancialResource($projectFinancial),
                'message' => 'Project financial retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project financial: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified project financial.
     */
    public function update(UpdateProjectFinancialRequest $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $projectFinancial = ProjectFinancial::forCompany($companyId)->findOrFail($id);

            // Store original data for audit trail
            $originalData = $projectFinancial->toArray();

            // Update the project financial
            $validatedData = $request->validated();
            $validatedData['updated_by'] = $user->id;

            $projectFinancial->update($validatedData);

            // Load relationships for response
            $projectFinancial->load(['project', 'currency', 'creator', 'updater']);

            return response()->json([
                'success' => true,
                'data' => new ProjectFinancialResource($projectFinancial),
                'message' => 'Project financial updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating project financial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified project financial (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectFinancial = ProjectFinancial::forCompany($companyId)->findOrFail($id);

            // Set deleted_by before soft delete
            $projectFinancial->update(['deleted_by' => $user->id]);
            $projectFinancial->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project financial deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting project financial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for project financials.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            // Build query
            $query = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply advanced search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            $projectFinancials = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projectFinancials,
                'message' => 'Project financials search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching project financials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project financials by specific field.
     */
    public function getProjectFinancialsByField(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
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
                'project_id', 'currency_id', 'reference_type', 'reference_id',
                'amount', 'date', 'description'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            $query = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId);

            if ($field === 'date') {
                $query->whereDate($field, $value);
            } else {
                $query->where($field, 'like', "%{$value}%");
            }

            $projectFinancials = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projectFinancials,
                'message' => 'Project financials filtered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering project financials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get field values for dynamic selection.
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
                'reference_type', 'reference_id', 'amount', 'description'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field specified'
                ], 400);
            }

            $values = ProjectFinancial::forCompany($companyId)
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->distinct()
                ->pluck($field)
                ->filter()
                ->values();

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
     * Get sortable fields.
     */
    public function getSortableFields(): JsonResponse
    {
        try {
            $sortableFields = [
                'id' => 'ID',
                'project_id' => 'Project',
                'currency_id' => 'Currency',
                'exchange_rate' => 'Exchange Rate',
                'reference_type' => 'Reference Type',
                'reference_id' => 'Reference ID',
                'amount' => 'Amount',
                'date' => 'Date',
                'description' => 'Description',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At'
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
     * Sort project financials.
     */
    public function sortProjectFinancials(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $query = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId);

            // Apply sorting
            $this->applySorting($query, $request);

            $projectFinancials = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projectFinancials,
                'message' => 'Project financials sorted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sorting project financials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted project financial.
     */
    public function restore($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectFinancial = ProjectFinancial::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            $projectFinancial->restore();
            $projectFinancial->update(['deleted_by' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Project financial restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring project financial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete a project financial.
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectFinancial = ProjectFinancial::withTrashed()
                ->forCompany($companyId)
                ->findOrFail($id);

            $projectFinancial->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Project financial permanently deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting project financial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed project financials.
     */
    public function getTrashed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $perPage = $request->get('per_page', 15);

            $trashedProjectFinancials = ProjectFinancial::onlyTrashed()
                ->with(['project', 'currency', 'creator', 'updater', 'deleter'])
                ->forCompany($companyId)
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $trashedProjectFinancials,
                'message' => 'Trashed project financials retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed project financials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get projects for dropdown.
     */
    public function getProjects(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $projects = Project::where('company_id', $companyId)
                ->select('id', 'project_number', 'name', 'project_value', 'currency_id')
                ->orderBy('name')
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'project_number' => $project->project_number,
                        'name' => $project->name,
                        'display_name' => $project->project_number . ' - ' . $project->name,
                        'project_value' => $project->project_value,
                        'currency_id' => $project->currency_id
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
     * Get currencies for dropdown.
     */
    public function getCurrencies(Request $request): JsonResponse
    {
        try {
            $currencies = Currency::select('id', 'currency_code', 'currency_name_ar', 'currency_name_en')
                ->orderBy('currency_name_ar')
                ->get()
                ->map(function ($currency) {
                    return [
                        'id' => $currency->id,
                        'currency_code' => $currency->currency_code,
                        'name' => $currency->currency_name_ar ?: $currency->currency_name_en,
                        'display_name' => $currency->currency_code . ' - ' . ($currency->currency_name_ar ?: $currency->currency_name_en)
                    ];
                });

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
     * Apply search filters to the query.
     */
    private function applySearchFilters($query, Request $request): void
    {
        // General search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_type', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($projectQuery) use ($search) {
                      $projectQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('project_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('currency', function ($currencyQuery) use ($search) {
                      $currencyQuery->where('currency_code', 'like', "%{$search}%")
                                   ->orWhere('currency_name_ar', 'like', "%{$search}%")
                                   ->orWhere('currency_name_en', 'like', "%{$search}%");
                  });
            });
        }

        // Specific field searches
        if ($request->has('project_number') && !empty($request->project_number)) {
            $query->whereHas('project', function ($projectQuery) use ($request) {
                $projectQuery->where('project_number', 'like', "%{$request->project_number}%");
            });
        }

        if ($request->has('project_name') && !empty($request->project_name)) {
            $query->whereHas('project', function ($projectQuery) use ($request) {
                $projectQuery->where('name', 'like', "%{$request->project_name}%");
            });
        }

        if ($request->has('reference_type') && !empty($request->reference_type)) {
            $query->where('reference_type', 'like', "%{$request->reference_type}%");
        }

        if ($request->has('reference_id') && !empty($request->reference_id)) {
            $query->where('reference_id', 'like', "%{$request->reference_id}%");
        }

        // Date searches
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Amount range searches
        if ($request->has('amount_min') && !empty($request->amount_min)) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->has('amount_max') && !empty($request->amount_max)) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Exchange rate range searches
        if ($request->has('exchange_rate_min') && !empty($request->exchange_rate_min)) {
            $query->where('exchange_rate', '>=', $request->exchange_rate_min);
        }

        if ($request->has('exchange_rate_max') && !empty($request->exchange_rate_max)) {
            $query->where('exchange_rate', '<=', $request->exchange_rate_max);
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
            'id', 'project_id', 'currency_id', 'exchange_rate', 'reference_type',
            'reference_id', 'amount', 'date', 'description', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get project financials for a specific project.
     */
    public function getProjectFinancials($projectId): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectFinancials = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId)
                ->where('project_id', $projectId)
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $projectFinancials,
                'message' => 'Project financials retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project financials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project financials by reference type.
     */
    public function getByReferenceType($referenceType): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectFinancials = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId)
                ->where('reference_type', $referenceType)
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $projectFinancials,
                'message' => 'Project financials retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project financials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project financials by date range.
     */
    public function getByDateRange($dateFrom, $dateTo): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $projectFinancials = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
                ->forCompany($companyId)
                ->whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $projectFinancials,
                'message' => 'Project financials retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project financials: ' . $e->getMessage()
            ], 500);
        }
    }
}
