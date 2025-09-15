<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Warehouse;

class WarehouseService
{
    /**
     * Get warehouses for a user with filters and pagination.
     */
    public function getWarehouses($user, array $filters = [], int $perPage = 15)
    {
        $query = Warehouse::with(['company', 'branch', 'manager'])
            ->forCompany($user->company_id);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $this->applySorting($query, $sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Create a new warehouse.
     */
    public function createWarehouse(array $data, $user): Warehouse
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['created_by'] = $user->id;

            $warehouse = Warehouse::create($data);

            // Load relationships for response
            $warehouse->load(['company', 'branch', 'manager']);

            return $warehouse;
        });
    }

    /**
     * Get a warehouse by ID.
     */
    public function getWarehouseById(int $id, $user): Warehouse
    {
        return Warehouse::with(['company', 'branch', 'manager', 'stockItems'])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update a warehouse.
     */
    public function updateWarehouse(int $id, array $data, $user): Warehouse
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $warehouse = Warehouse::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            $warehouse->update($data);

            return $warehouse->load(['company', 'branch', 'manager']);
        });
    }

    /**
     * Delete a warehouse (soft delete).
     */
    public function deleteWarehouse(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $warehouse = Warehouse::forCompany($user->company_id)->findOrFail($id);

            // Check if warehouse has stock items
            if ($warehouse->stockItems()->exists()) {
                throw new \Exception('Cannot delete warehouse that contains stock items');
            }

            // Set deleted_by before soft delete
            $warehouse->update(['deleted_by' => $user->id]);

            return $warehouse->delete();
        });
    }

    /**
     * Restore a soft-deleted warehouse.
     */
    public function restoreWarehouse(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $warehouse = Warehouse::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $warehouse->restore();

            if ($result) {
                $warehouse->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a warehouse.
     */
    public function forceDeleteWarehouse(int $id, $user): bool
    {
        $warehouse = Warehouse::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $warehouse->forceDelete();
    }

    /**
     * Get trashed warehouses.
     */
    public function getTrashedWarehouses($user, int $perPage = 15)
    {
        return Warehouse::onlyTrashed()
            ->with(['company', 'branch', 'manager'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search warehouses with advanced filters.
     */
    public function searchWarehouses($user, array $searchParams, int $perPage = 15)
    {
        $query = Warehouse::with(['company', 'branch', 'manager'])
            ->forCompany($user->company_id);

        // Apply search filters
        $this->applySearchFilters($query, $searchParams);

        // Apply sorting
        $sortBy = $searchParams['sort_by'] ?? 'name';
        $sortOrder = $searchParams['sort_order'] ?? 'asc';
        $this->applySorting($query, $sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get warehouses by branch.
     */
    public function getWarehousesByBranch(int $branchId, $user, int $perPage = 15)
    {
        return Warehouse::with(['company', 'branch', 'manager'])
            ->forCompany($user->company_id)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get warehouses by manager.
     */
    public function getWarehousesByManager(int $managerId, $user, int $perPage = 15)
    {
        return Warehouse::with(['company', 'branch', 'manager'])
            ->forCompany($user->company_id)
            ->where('manager_id', $managerId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get active warehouses.
     */
    public function getActiveWarehouses($user, int $perPage = 15)
    {
        return Warehouse::with(['company', 'branch', 'manager'])
            ->forCompany($user->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get warehouse stock summary.
     */
    public function getWarehouseStockSummary(int $id, $user): array
    {
        $warehouse = Warehouse::forCompany($user->company_id)->findOrFail($id);

        $stockSummary = $warehouse->stockItems()
            ->selectRaw('
                COUNT(*) as total_items,
                SUM(quantity) as total_quantity,
                SUM(CASE WHEN quantity <= minimum_limit THEN 1 ELSE 0 END) as low_stock_items,
                SUM(CASE WHEN quantity <= reorder_limit THEN 1 ELSE 0 END) as reorder_items
            ')
            ->first();

        return [
            'warehouse' => $warehouse,
            'summary' => $stockSummary
        ];
    }

    /**
     * Transfer stock between warehouses.
     */
    public function transferStock(int $fromWarehouseId, int $toWarehouseId, int $itemId, float $quantity, $user): bool
    {
        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $itemId, $quantity, $user) {
            $fromWarehouse = Warehouse::forCompany($user->company_id)->findOrFail($fromWarehouseId);
            $toWarehouse = Warehouse::forCompany($user->company_id)->findOrFail($toWarehouseId);

            // Check if source warehouse has enough stock
            $sourceStock = $fromWarehouse->stockItems()->where('item_id', $itemId)->first();
            if (!$sourceStock || $sourceStock->quantity < $quantity) {
                throw new \Exception('Insufficient stock in source warehouse');
            }

            // Reduce stock from source warehouse
            $sourceStock->decrement('quantity', $quantity);

            // Add stock to destination warehouse
            $destinationStock = $toWarehouse->stockItems()->where('item_id', $itemId)->first();
            if ($destinationStock) {
                $destinationStock->increment('quantity', $quantity);
            } else {
                // Create new stock record in destination warehouse
                $toWarehouse->stockItems()->create([
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'company_id' => $user->company_id,
                    'created_by' => $user->id
                ]);
            }

            return true;
        });
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return Warehouse::forCompany($user->company_id)
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->distinct()
            ->pluck($field)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
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
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
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
            'id', 'name', 'code', 'address', 'phone', 'email',
            'is_active', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }
    }
}
