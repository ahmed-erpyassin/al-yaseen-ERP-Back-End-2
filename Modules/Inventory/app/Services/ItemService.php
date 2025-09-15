<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemType;

class ItemService
{
    /**
     * Get items for a user with filters and pagination.
     */
    public function getItems($user, array $filters = [], int $perPage = 15)
    {
        $query = Item::with(['company', 'branch', 'user', 'unit', 'parent', 'itemUnits.unit'])
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
     * Create a new item.
     */
    public function createItem(array $data, $user): Item
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['branch_id'] = $data['branch_id'] ?? $user->branch_id;
            $data['created_by'] = $user->id;

            // Handle custom item type creation if needed
            if (!empty($data['custom_item_type'])) {
                $customType = ItemType::createCustomType(
                    $user->company_id,
                    $data['custom_item_type'],
                    $data['custom_item_type_ar'] ?? $data['custom_item_type']
                );
                $data['item_type'] = $customType->code;
                unset($data['custom_item_type'], $data['custom_item_type_ar']);
            }

            $item = Item::create($data);

            // Load relationships for response
            $item->load([
                'company:id,name',
                'branch:id,name',
                'user:id,name,email',
                'unit:id,name,symbol',
                'parent:id,item_number,name',
                'createdBy:id,name,email',
                'children:id,item_number,name',
                'itemUnits.unit:id,name,symbol'
            ]);

            return $item;
        });
    }

    /**
     * Get an item by ID.
     */
    public function getItemById(int $id, $user): Item
    {
        return Item::with([
                'company', 'branch', 'user', 'unit', 'parent', 'children',
                'createdBy', 'updatedBy', 'itemUnits.unit'
            ])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update an item.
     */
    public function updateItem(int $id, array $data, $user): Item
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $item = Item::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            // Store original values for comparison
            $originalData = $item->only([
                'item_number', 'name', 'description', 'model', 'unit_id',
                'balance', 'minimum_limit', 'maximum_limit', 'reorder_limit'
            ]);

            $item->update($data);

            return $item->load([
                'company', 'branch', 'user', 'unit', 'parent', 'children',
                'createdBy', 'updatedBy', 'itemUnits.unit'
            ]);
        });
    }

    /**
     * Delete an item (soft delete).
     */
    public function deleteItem(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $item = Item::forCompany($user->company_id)->findOrFail($id);

            // Check if item has children or is being used
            if ($item->children()->exists()) {
                throw new \Exception('Cannot delete item that has child items');
            }

            // Set deleted_by before soft delete
            $item->update(['deleted_by' => $user->id]);

            return $item->delete();
        });
    }

    /**
     * Restore a soft-deleted item.
     */
    public function restoreItem(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $item = Item::withTrashed()
                ->forCompany($user->company_id)
                ->findOrFail($id);

            $result = $item->restore();

            if ($result) {
                $item->update(['deleted_by' => null]);
            }

            return $result;
        });
    }

    /**
     * Force delete an item.
     */
    public function forceDeleteItem(int $id, $user): bool
    {
        $item = Item::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $item->forceDelete();
    }

    /**
     * Get trashed items.
     */
    public function getTrashedItems($user, int $perPage = 15)
    {
        return Item::onlyTrashed()
            ->with(['company', 'branch', 'user', 'unit', 'parent'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search items with advanced filters.
     */
    public function searchItems($user, array $searchParams, int $perPage = 15)
    {
        $query = Item::with(['company', 'branch', 'user', 'unit', 'parent', 'itemUnits.unit'])
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
     * Get items by type.
     */
    public function getItemsByType(string $type, $user, int $perPage = 15)
    {
        return Item::with(['company', 'branch', 'user', 'unit', 'parent'])
            ->forCompany($user->company_id)
            ->where('type', $type)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get items by parent.
     */
    public function getItemsByParent(int $parentId, $user, int $perPage = 15)
    {
        return Item::with(['company', 'branch', 'user', 'unit', 'parent'])
            ->forCompany($user->company_id)
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get low stock items.
     */
    public function getLowStockItems($user, int $perPage = 15)
    {
        return Item::with(['company', 'branch', 'user', 'unit'])
            ->forCompany($user->company_id)
            ->lowStock()
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get items that need reordering.
     */
    public function getReorderItems($user, int $perPage = 15)
    {
        return Item::with(['company', 'branch', 'user', 'unit'])
            ->forCompany($user->company_id)
            ->needReorder()
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Update item stock.
     */
    public function updateItemStock(int $id, float $quantity, string $type, $user): Item
    {
        return DB::transaction(function () use ($id, $quantity, $type, $user) {
            $item = Item::forCompany($user->company_id)->findOrFail($id);

            if ($type === 'add') {
                $item->balance += $quantity;
            } elseif ($type === 'subtract') {
                if ($item->balance < $quantity) {
                    throw new \Exception('Insufficient stock balance');
                }
                $item->balance -= $quantity;
            } elseif ($type === 'set') {
                $item->balance = $quantity;
            }

            $item->updated_by = $user->id;
            $item->save();

            return $item->load(['company', 'branch', 'user', 'unit']);
        });
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return Item::forCompany($user->company_id)
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
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock();
        }

        if (isset($filters['need_reorder']) && $filters['need_reorder']) {
            $query->needReorder();
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
                  ->orWhere('item_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
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
            'id', 'item_number', 'name', 'description', 'model', 'type',
            'balance', 'minimum_limit', 'maximum_limit', 'reorder_limit',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }
    }
}
