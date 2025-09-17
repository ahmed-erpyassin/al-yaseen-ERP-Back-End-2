<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryItem;

class InventoryService
{
    /**
     * Get inventory items for a user with filters and pagination.
     */
    public function getInventoryItems($user, array $filters = [], int $perPage = 15)
    {
        $query = InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($user->company_id);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'item_name_ar';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $this->applySorting($query, $sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Create a new inventory item.
     */
    public function createInventoryItem(array $data, $user): InventoryItem
    {
        return DB::transaction(function () use ($data, $user) {
            // Set user context
            $data['company_id'] = $data['company_id'] ?? $user->company_id;
            $data['created_by'] = $user->id;

            $item = InventoryItem::create($data);

            // Load relationships for response
            $item->load(['company', 'stock.warehouse']);

            return $item;
        });
    }

    /**
     * Get an inventory item by ID.
     */
    public function getInventoryItemById(int $id, $user): InventoryItem
    {
        return InventoryItem::with(['company', 'stock.warehouse', 'category', 'supplier'])
            ->forCompany($user->company_id)
            ->findOrFail($id);
    }

    /**
     * Update an inventory item.
     */
    public function updateInventoryItem(int $id, array $data, $user): InventoryItem
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $item = InventoryItem::forCompany($user->company_id)->findOrFail($id);

            // Set updated_by
            $data['updated_by'] = $user->id;

            $item->update($data);

            return $item->load(['company', 'stock.warehouse']);
        });
    }

    /**
     * Delete an inventory item (soft delete).
     */
    public function deleteInventoryItem(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $item = InventoryItem::forCompany($user->company_id)->findOrFail($id);

            // Check if item has stock or movements
            if ($item->stock()->exists() || $item->stockMovements()->exists()) {
                throw new \Exception('Cannot delete item with existing stock or movements');
            }

            // Set deleted_by before soft delete
            $item->update(['deleted_by' => $user->id]);

            return $item->delete();
        });
    }

    /**
     * Restore a soft-deleted inventory item.
     */
    public function restoreInventoryItem(int $id, $user): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $item = InventoryItem::withTrashed()
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
     * Force delete an inventory item.
     */
    public function forceDeleteInventoryItem(int $id, $user): bool
    {
        $item = InventoryItem::withTrashed()
            ->forCompany($user->company_id)
            ->findOrFail($id);

        return $item->forceDelete();
    }

    /**
     * Get trashed inventory items.
     */
    public function getTrashedInventoryItems($user, int $perPage = 15)
    {
        return InventoryItem::onlyTrashed()
            ->with(['company', 'stock.warehouse'])
            ->forCompany($user->company_id)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search inventory items with advanced filters.
     */
    public function searchInventoryItems($user, array $searchParams, int $perPage = 15)
    {
        $query = InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($user->company_id);

        // Apply search filters
        $this->applySearchFilters($query, $searchParams);

        // Apply sorting
        $sortBy = $searchParams['sort_by'] ?? 'item_name_ar';
        $sortOrder = $searchParams['sort_order'] ?? 'asc';
        $this->applySorting($query, $sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get items that need reordering.
     */
    public function getReorderItems($user, int $perPage = 15)
    {
        return InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($user->company_id)
            ->whereColumn('quantity', '<=', 'reorder_limit')
            ->orderBy('item_name_ar')
            ->paginate($perPage);
    }

    /**
     * Get low stock items.
     */
    public function getLowStockItems($user, int $perPage = 15)
    {
        return InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($user->company_id)
            ->whereColumn('quantity', '<=', 'minimum_limit')
            ->orderBy('item_name_ar')
            ->paginate($perPage);
    }

    /**
     * Get inventory items by category.
     */
    public function getItemsByCategory(int $categoryId, $user, int $perPage = 15)
    {
        return InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($user->company_id)
            ->where('category_id', $categoryId)
            ->orderBy('item_name_ar')
            ->paginate($perPage);
    }

    /**
     * Get inventory items by supplier.
     */
    public function getItemsBySupplier(int $supplierId, $user, int $perPage = 15)
    {
        return InventoryItem::with(['company', 'stock.warehouse'])
            ->forCompany($user->company_id)
            ->where('supplier_id', $supplierId)
            ->orderBy('item_name_ar')
            ->paginate($perPage);
    }

    /**
     * Update item stock quantity.
     */
    public function updateItemStock(int $id, float $quantity, string $type, $user): InventoryItem
    {
        return DB::transaction(function () use ($id, $quantity, $type, $user) {
            $item = InventoryItem::forCompany($user->company_id)->findOrFail($id);

            if ($type === 'add') {
                $item->quantity += $quantity;
            } elseif ($type === 'subtract') {
                if ($item->quantity < $quantity) {
                    throw new \Exception('Insufficient stock quantity');
                }
                $item->quantity -= $quantity;
            } elseif ($type === 'set') {
                $item->quantity = $quantity;
            }

            $item->updated_by = $user->id;
            $item->save();

            return $item->load(['company', 'stock.warehouse']);
        });
    }

    /**
     * Get unique field values for dynamic selection.
     */
    public function getFieldValues($user, string $field): array
    {
        return InventoryItem::forCompany($user->company_id)
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
        if (!empty($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('item_name_ar', 'like', "%{$search}%")
                  ->orWhere('item_name_en', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
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
                $q->where('item_name_ar', 'like', "%{$search}%")
                  ->orWhere('item_name_en', 'like', "%{$search}%")
                  ->orWhere('item_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
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
            'id', 'item_number', 'item_name_ar', 'item_name_en', 'barcode', 'model',
            'quantity', 'minimum_limit', 'reorder_limit', 'unit_price',
            'first_purchase_price', 'first_sale_price', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('item_name_ar', 'asc');
        }
    }
}
