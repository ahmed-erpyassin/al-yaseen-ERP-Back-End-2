<?php

namespace Modules\ProjectsManagment\Services;

use Illuminate\Support\Facades\DB;
use Modules\ProjectsManagment\Models\ProjectFinancial;
use Modules\ProjectsManagment\Models\Project;
use Modules\FinancialAccounts\Models\Currency;

class ProjectFinancialService
{
    /**
     * Create a new project financial record.
     */
    public function createProjectFinancial(array $data, $user): ProjectFinancial
    {
        return DB::transaction(function () use ($data, $user) {
            // Ensure user context is set
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['fiscal_year_id'] = $data['fiscal_year_id'] ?? $user->fiscal_year_id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            // Auto-populate exchange rate if not provided
            if (!isset($data['exchange_rate']) || empty($data['exchange_rate'])) {
                $data['exchange_rate'] = $this->getDefaultExchangeRate($data['currency_id']);
            }

            return ProjectFinancial::create($data);
        });
    }

    /**
     * Get project financials for a user.
     */
    public function getProjectFinancials($user, array $filters = [], int $perPage = 15)
    {
        $query = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
            ->forCompany($user->company_id);

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a project financial by ID.
     */
    public function getProjectFinancialById(int $id, $user): ProjectFinancial
    {
        return ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update a project financial record.
     */
    public function updateProjectFinancial(int $id, array $data, $user): ProjectFinancial
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $projectFinancial = ProjectFinancial::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            // Update exchange rate if currency changed
            if (isset($data['currency_id']) && $data['currency_id'] !== $projectFinancial->currency_id) {
                if (!isset($data['exchange_rate']) || empty($data['exchange_rate'])) {
                    $data['exchange_rate'] = $this->getDefaultExchangeRate($data['currency_id']);
                }
            }

            $projectFinancial->update($data);

            return $projectFinancial->load(['project', 'currency', 'creator', 'updater']);
        });
    }

    /**
     * Delete a project financial record (soft delete).
     */
    public function deleteProjectFinancial(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $projectFinancial = ProjectFinancial::forCompany($user->company_id)->findOrFail($id);

            // Set deleted_by before soft delete
            $projectFinancial->update(['deleted_by' => $user->id]);

            return $projectFinancial->delete();
        });
    }

    /**
     * Restore a soft-deleted project financial record.
     */
    public function restoreProjectFinancial(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $projectFinancial = ProjectFinancial::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $projectFinancial->restore();

            if ($result) {
                $projectFinancial->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a project financial record.
     */
    public function forceDeleteProjectFinancial(int $id, $user): bool
    {
        $projectFinancial = ProjectFinancial::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $projectFinancial->forceDelete();
    }

    /**
     * Get trashed project financials.
     */
    public function getTrashedProjectFinancials($user, int $perPage = 15)
    {
        return ProjectFinancial::onlyTrashed()
            ->with(['project', 'currency', 'creator', 'updater', 'deleter'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search project financials with advanced filters.
     */
    public function searchProjectFinancials($user, array $searchParams, int $perPage = 15)
    {
        $query = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
            ->forCompany($user->company_id);

        // Apply search filters
        $this->applySearchFilters($query, $searchParams);

        // Apply sorting
        $sortBy = $searchParams['sort_by'] ?? 'created_at';
        $sortOrder = $searchParams['sort_order'] ?? 'desc';
        $this->applySorting($query, $sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get project financials by specific field.
     */
    public function getProjectFinancialsByField($user, string $field, $value, int $perPage = 15)
    {
        $query = ProjectFinancial::with(['project', 'currency', 'creator', 'updater'])
            ->forCompany($user->company_id);

        if ($field === 'date') {
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
        return ProjectFinancial::forCompany($user->company_id)
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->distinct()
            ->pluck($field)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Calculate total amount for a project.
     */
    public function calculateProjectTotal(int $projectId, $user): array
    {
        $projectFinancials = ProjectFinancial::forCompany($user->company_id)
            ->where('project_id', $projectId)
            ->get();

        $totalAmount = $projectFinancials->sum('amount');
        $totalByType = $projectFinancials->groupBy('reference_type')
            ->map(function ($items) {
                return $items->sum('amount');
            });

        return [
            'total_amount' => $totalAmount,
            'total_by_type' => $totalByType,
            'count' => $projectFinancials->count()
        ];
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
     * Get currencies for dropdown.
     */
    public function getCurrenciesForDropdown(): array
    {
        return Currency::select('id', 'currency_code', 'currency_name_ar', 'currency_name_en')
            ->orderBy('currency_name_ar')
            ->get()
            ->map(function ($currency) {
                return [
                    'id' => $currency->id,
                    'currency_code' => $currency->currency_code,
                    'name' => $currency->currency_name_ar ?: $currency->currency_name_en,
                    'display_name' => $currency->currency_code . ' - ' . ($currency->currency_name_ar ?: $currency->currency_name_en)
                ];
            })
            ->toArray();
    }

    /**
     * Get default exchange rate for a currency.
     */
    private function getDefaultExchangeRate(?int $currencyId): float
    {
        if (!$currencyId) {
            return 1.0000;
        }

        // You can implement logic to get current exchange rate from external API
        // or from exchange_rates table. For now, return default value.
        return 1.0000;
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['currency_id'])) {
            $query->where('currency_id', $filters['currency_id']);
        }

        if (!empty($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }

        if (!empty($filters['reference_id'])) {
            $query->where('reference_id', $filters['reference_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
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
        if (!empty($searchParams['project_number'])) {
            $query->whereHas('project', function ($projectQuery) use ($searchParams) {
                $projectQuery->where('project_number', 'like', "%{$searchParams['project_number']}%");
            });
        }

        if (!empty($searchParams['project_name'])) {
            $query->whereHas('project', function ($projectQuery) use ($searchParams) {
                $projectQuery->where('name', 'like', "%{$searchParams['project_name']}%");
            });
        }

        if (!empty($searchParams['reference_type'])) {
            $query->where('reference_type', 'like', "%{$searchParams['reference_type']}%");
        }

        if (!empty($searchParams['reference_id'])) {
            $query->where('reference_id', 'like', "%{$searchParams['reference_id']}%");
        }

        // Date searches
        if (!empty($searchParams['date'])) {
            $query->whereDate('date', $searchParams['date']);
        }

        if (!empty($searchParams['date_from'])) {
            $query->whereDate('date', '>=', $searchParams['date_from']);
        }

        if (!empty($searchParams['date_to'])) {
            $query->whereDate('date', '<=', $searchParams['date_to']);
        }

        // Amount range searches
        if (!empty($searchParams['amount_min'])) {
            $query->where('amount', '>=', $searchParams['amount_min']);
        }

        if (!empty($searchParams['amount_max'])) {
            $query->where('amount', '<=', $searchParams['amount_max']);
        }

        // Exchange rate range searches
        if (!empty($searchParams['exchange_rate_min'])) {
            $query->where('exchange_rate', '>=', $searchParams['exchange_rate_min']);
        }

        if (!empty($searchParams['exchange_rate_max'])) {
            $query->where('exchange_rate', '<=', $searchParams['exchange_rate_max']);
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, string $sortBy, string $sortOrder): void
    {
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
}
