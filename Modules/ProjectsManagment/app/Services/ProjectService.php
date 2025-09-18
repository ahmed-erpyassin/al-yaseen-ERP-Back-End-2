<?php

namespace Modules\ProjectsManagment\Services;

use Illuminate\Support\Facades\DB;

use Modules\ProjectsManagment\Models\Project;
use Modules\Customers\Models\Customer;
use Modules\Companies\Models\Company;
use Modules\FinancialAccounts\Models\Currency;

class ProjectService
{
    /**
     * Get projects for a user with filters and pagination.
     */
    public function getProjects($user, array $filters = [], int $perPage = 15)
    {
        $query = Project::with([
                'customer', 'currency', 'manager', 'country', 'company', 'branch'
            ]);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $this->applySorting($query, $sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Create a new project.
     */
    public function createProject(array $data, $user): Project
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['fiscal_year_id'] = $data['fiscal_year_id'] ?? $user->fiscal_year_id;

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

            $project = Project::create($data);

            // Load relationships for response
            $project->load(['customer', 'currency', 'manager', 'country', 'company']);

            return $project;
        });
    }

    /**
     * Get a project by ID.
     */
    public function getProjectById(int $id, $user): Project
    {
        return Project::with([
                'customer', 'currency', 'manager', 'country', 'company', 'branch',
                'fiscalYear', 'costCenter', 'creator', 'updater', 'deleter',
                'milestones', 'tasks', 'resources', 'documents', 'financials', 'risks'
            ])
            ->findOrFail($id);
    }

    /**
     * Update a project.
     */
    public function updateProject(int $id, array $data, $user): Project
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $project = Project::findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

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
            if (isset($data['include_vat']) && $data['include_vat'] && isset($data['currency_price'])) {
                $company = Company::find($project->company_id);
                if ($company && $company->vat_rate > 0) {
                    $vatAmount = $data['currency_price'] * ($company->vat_rate / 100);
                    $data['currency_price'] = $data['currency_price'] + $vatAmount;
                }
            }

            $project->update($data);

            return $project->load(['customer', 'currency', 'manager', 'country', 'company']);
        });
    }

    /**
     * Delete a project (soft delete).
     */
    public function deleteProject(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $project = Project::findOrFail($id);

            // Set deleted_by before soft delete
            $project->update(['deleted_by' => $user->id]);

            return $project->delete();
        });
    }

    /**
     * Restore a soft-deleted project.
     */
    public function restoreProject(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $project = Project::withTrashed()
                ->findOrFail($id);

            $result = $project->restore();

            if ($result) {
                $project->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a project.
     */
    public function forceDeleteProject(int $id, $user): bool
    {
        $project = Project::withTrashed()
            ->findOrFail($id);

        return $project->forceDelete();
    }

    /**
     * Get trashed projects.
     */
    public function getTrashedProjects($user, int $perPage = 15)
    {
        return Project::onlyTrashed()
            ->with(['customer', 'currency', 'manager', 'country', 'company', 'branch', 'deleter'])
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search projects with advanced filters.
     */
    public function searchProjects($user, array $searchParams, int $perPage = 15)
    {
        $query = Project::with([
                'customer', 'currency', 'manager', 'country', 'company', 'branch'
            ]);

        // Apply search filters
        $this->applySearchFilters($query, $searchParams);

        // Apply sorting
        $sortBy = $searchParams['sort_by'] ?? 'created_at';
        $sortOrder = $searchParams['sort_order'] ?? 'desc';
        $this->applySorting($query, $sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Generate next project code.
     */
    public function generateProjectCode($user): string
    {
        $companyId = $user->company_id;
        $year = date('Y');

        $lastProject = Project::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastProject ? (intval(substr($lastProject->code, -4)) + 1) : 1;
        return 'PRJ-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get projects by specific field.
     */
    public function getProjectsByField($user, string $field, $value, int $perPage = 15)
    {
        $query = Project::with([
                'customer', 'currency', 'manager', 'country', 'company', 'branch'
            ]);

        if ($field === 'start_date' || $field === 'end_date' || $field === 'created_at') {
            $query->whereDate($field, $value);
        } else {
            $query->where($field, 'like', "%{$value}%");
        }

        return $query->paginate($perPage);
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return Project::whereNotNull($field)
            ->where($field, '!=', '')
            ->distinct()
            ->pluck($field)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get projects for dropdown.
     */
    public function getProjectsForDropdown($user): array
    {
        return Project::where('company_id', $user->company_id)
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
            })
            ->toArray();
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['project_number'])) {
            $query->where('project_number', 'like', "%{$filters['project_number']}%");
        }

        if (!empty($filters['project_name'])) {
            $query->where('name', 'like', "%{$filters['project_name']}%");
        }

        if (!empty($filters['customer_name'])) {
            $query->where('customer_name', 'like', "%{$filters['customer_name']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['project_manager_name'])) {
            $query->where('project_manager_name', 'like', "%{$filters['project_manager_name']}%");
        }

        if (!empty($filters['exact_date'])) {
            $query->whereDate('created_at', $filters['exact_date']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['start_date_from'])) {
            $query->whereDate('start_date', '>=', $filters['start_date_from']);
        }

        if (!empty($filters['start_date_to'])) {
            $query->whereDate('start_date', '<=', $filters['start_date_to']);
        }

        if (!empty($filters['end_date_from'])) {
            $query->whereDate('end_date', '>=', $filters['end_date_from']);
        }

        if (!empty($filters['end_date_to'])) {
            $query->whereDate('end_date', '<=', $filters['end_date_to']);
        }

        if (!empty($filters['general_search'])) {
            $search = $filters['general_search'];
            $query->where(function ($q) use ($search) {
                $q->where('project_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('project_manager_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Apply search filters to the query.
     */
    private function applySearchFilters($query, array $searchParams): void
    {
        // General search
        if (!empty($searchParams['search'])) {
            $search = $searchParams['search'];
            $query->where(function ($q) use ($search) {
                $q->where('project_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('project_manager_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('second_name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply the same filters as applyFilters method
        $this->applyFilters($query, $searchParams);
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, string $sortBy, string $sortOrder): void
    {
        $allowedSortFields = [
            'id', 'project_number', 'name', 'customer_name', 'status', 'project_manager_name',
            'start_date', 'end_date', 'project_value', 'currency_price', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
