<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Item;

class StockMovementService
{
    /**
     * Get stock movements for a user with filters and pagination.
     */
    public function getStockMovements($user, array $filters = [], int $perPage = 15)
    {
        $query = StockMovement::with(['item', 'warehouse', 'user', 'company'])
            ->forCompany($user->company_id);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $this->applySorting($query, $sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Create a new stock movement.
     */
    public function createStockMovement(array $data, $user): StockMovement
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['created_by'] = $user->id;

            // Validate item exists
            $item = Item::forCompany($user->company_id)->findOrFail($data['item_id']);

            // Create movement
            $movement = StockMovement::create($data);

            // Update item stock based on movement type
            $this->updateItemStock($item, $movement);

            // Load relationships for response
            $movement->load(['item', 'warehouse', 'user', 'company']);

            return $movement;
        });
    }

    /**
     * Get a stock movement by ID.
     */
    public function getStockMovementById(int $id, $user): StockMovement
    {
        return StockMovement::with(['item', 'warehouse', 'user', 'company'])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update a stock movement.
     */
    public function updateStockMovement(int $id, array $data, $user): StockMovement
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $movement = StockMovement::forCompany($user->company_id)->findOrFail($id);
            $originalQuantity = $movement->quantity;
            $originalType = $movement->type;

            // Set updated_by
            $data['updated_by'] = $user->id;

            // Reverse original movement effect on item stock
            $item = $movement->item;
            $this->reverseItemStock($item, $movement);

            // Update movement
            $movement->update($data);

            // Apply new movement effect on item stock
            $this->updateItemStock($item, $movement);

            return $movement->load(['item', 'warehouse', 'user', 'company']);
        });
    }

    /**
     * Delete a stock movement (soft delete).
     */
    public function deleteStockMovement(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $movement = StockMovement::forCompany($user->company_id)->findOrFail($id);

            // Reverse movement effect on item stock
            $this->reverseItemStock($movement->item, $movement);

            // Set deleted_by before soft delete
            $movement->update(['deleted_by' => $user->id]);

            return $movement->delete();
        });
    }

    /**
     * Restore a soft-deleted stock movement.
     */
    public function restoreStockMovement(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $movement = StockMovement::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $movement->restore();

            if ($result) {
                // Reapply movement effect on item stock
                $this->updateItemStock($movement->item, $movement);
                $movement->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete a stock movement.
     */
    public function forceDeleteStockMovement(int $id, $user): bool
    {
        $movement = StockMovement::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $movement->forceDelete();
    }

    /**
     * Get trashed stock movements.
     */
    public function getTrashedStockMovements($user, int $perPage = 15)
    {
        return StockMovement::onlyTrashed()
            ->with(['item', 'warehouse', 'user', 'company'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search stock movements with advanced filters.
     */
    public function searchStockMovements($user, array $searchParams, int $perPage = 15)
    {
        $query = StockMovement::with(['item', 'warehouse', 'user', 'company'])
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
     * Get movements by item.
     */
    public function getMovementsByItem(int $itemId, $user, int $perPage = 15)
    {
        return StockMovement::with(['item', 'warehouse', 'user', 'company'])
            ->forCompany($user->company_id)
            ->where('item_id', $itemId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get movements by warehouse.
     */
    public function getMovementsByWarehouse(int $warehouseId, $user, int $perPage = 15)
    {
        return StockMovement::with(['item', 'warehouse', 'user', 'company'])
            ->forCompany($user->company_id)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get movements by type.
     */
    public function getMovementsByType(string $type, $user, int $perPage = 15)
    {
        return StockMovement::with(['item', 'warehouse', 'user', 'company'])
            ->forCompany($user->company_id)
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get movements by date range.
     */
    public function getMovementsByDateRange($dateFrom, $dateTo, $user, int $perPage = 15)
    {
        return StockMovement::with(['item', 'warehouse', 'user', 'company'])
            ->forCompany($user->company_id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get stock movement summary by item.
     */
    public function getStockSummaryByItem(int $itemId, $user): array
    {
        $movements = StockMovement::forCompany($user->company_id)
            ->where('item_id', $itemId)
            ->selectRaw('
                type,
                SUM(quantity) as total_quantity,
                COUNT(*) as movement_count
            ')
            ->groupBy('type')
            ->get();

        return $movements->toArray();
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return StockMovement::forCompany($user->company_id)
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->distinct()
            ->pluck($field)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Update item stock based on movement.
     */
    private function updateItemStock(Item $item, StockMovement $movement): void
    {
        if ($movement->type === 'in' || $movement->type === 'purchase' || $movement->type === 'return') {
            $item->increment('balance', $movement->quantity);
        } elseif ($movement->type === 'out' || $movement->type === 'sale' || $movement->type === 'transfer') {
            $item->decrement('balance', $movement->quantity);
        } elseif ($movement->type === 'adjustment') {
            // For adjustments, the quantity can be positive or negative
            $item->increment('balance', $movement->quantity);
        }
    }

    /**
     * Reverse item stock changes from movement.
     */
    private function reverseItemStock(Item $item, StockMovement $movement): void
    {
        if ($movement->type === 'in' || $movement->type === 'purchase' || $movement->type === 'return') {
            $item->decrement('balance', $movement->quantity);
        } elseif ($movement->type === 'out' || $movement->type === 'sale' || $movement->type === 'transfer') {
            $item->increment('balance', $movement->quantity);
        } elseif ($movement->type === 'adjustment') {
            // For adjustments, reverse the quantity
            $item->decrement('balance', $movement->quantity);
        }
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('item_number', 'like', "%{$search}%");
                  });
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
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('item_number', 'like', "%{$search}%");
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
            'id', 'type', 'quantity', 'reference', 'notes',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
