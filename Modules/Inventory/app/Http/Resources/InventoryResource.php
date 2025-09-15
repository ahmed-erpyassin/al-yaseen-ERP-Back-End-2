<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'item_number' => $this->item_number,
            'item_name_ar' => $this->item_name_ar,
            'item_name_en' => $this->item_name_en,
            'barcode' => $this->barcode,
            'model' => $this->model,
            'quantity' => $this->quantity,
            'minimum_limit' => $this->minimum_limit,
            'reorder_limit' => $this->reorder_limit,
            'unit_price' => $this->unit_price,
            'first_purchase_price' => $this->first_purchase_price,
            'first_sale_price' => $this->first_sale_price,
            'active' => $this->active,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relationships
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),

            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'name' => $this->supplier->name,
                ];
            }),

            'stock' => $this->whenLoaded('stock', function () {
                return $this->stock->map(function ($stockItem) {
                    return [
                        'id' => $stockItem->id,
                        'warehouse_id' => $stockItem->warehouse_id,
                        'quantity' => $stockItem->quantity,
                        'warehouse' => $stockItem->whenLoaded('warehouse', function () use ($stockItem) {
                            return [
                                'id' => $stockItem->warehouse->id,
                                'name' => $stockItem->warehouse->name,
                            ];
                        }),
                    ];
                });
            }),

            // Computed properties
            'formatted_unit_price' => $this->unit_price ? number_format($this->unit_price, 2) : null,
            'formatted_purchase_price' => $this->first_purchase_price ? number_format($this->first_purchase_price, 2) : null,
            'formatted_sale_price' => $this->first_sale_price ? number_format($this->first_sale_price, 2) : null,
            'is_low_stock' => $this->quantity <= $this->minimum_limit,
            'needs_reorder' => $this->quantity <= $this->reorder_limit,
            'stock_status' => $this->getStockStatus(),
            'total_stock_value' => $this->quantity * $this->unit_price,
            'formatted_total_value' => number_format($this->quantity * $this->unit_price, 2),

            // Display names
            'display_name' => $this->item_name_ar ?: $this->item_name_en,
            'full_name' => $this->item_name_ar . ($this->item_name_en ? ' / ' . $this->item_name_en : ''),

            // Status indicators
            'status_color' => $this->getStatusColor(),
            'status_text' => $this->getStatusText(),

            // Permissions (if user is available in request)
            'can_edit' => $this->when(
                $request->user(),
                fn() => $this->canEdit($request->user())
            ),
            'can_delete' => $this->when(
                $request->user(),
                fn() => $this->canDelete($request->user())
            ),

            // Audit fields
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ];
            }),
            'updated_by' => $this->whenLoaded('updatedBy', function () {
                return [
                    'id' => $this->updatedBy->id,
                    'name' => $this->updatedBy->name,
                ];
            }),
        ];
    }

    /**
     * Get stock status based on quantity levels.
     */
    private function getStockStatus(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->quantity <= $this->minimum_limit) {
            return 'low_stock';
        } elseif ($this->quantity <= $this->reorder_limit) {
            return 'reorder_needed';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get status color for UI display.
     */
    private function getStatusColor(): string
    {
        return match ($this->getStockStatus()) {
            'out_of_stock' => 'red',
            'low_stock' => 'orange',
            'reorder_needed' => 'yellow',
            'in_stock' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get status text for UI display.
     */
    private function getStatusText(): string
    {
        return match ($this->getStockStatus()) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'reorder_needed' => 'Reorder Needed',
            'in_stock' => 'In Stock',
            default => 'Unknown'
        };
    }

    /**
     * Check if user can edit this inventory item.
     */
    private function canEdit($user): bool
    {
        return $this->company_id === $user->company_id;
    }

    /**
     * Check if user can delete this inventory item.
     */
    private function canDelete($user): bool
    {
        return $this->company_id === $user->company_id && 
               !$this->stock()->exists() && 
               !$this->stockMovements()->exists();
    }
}
