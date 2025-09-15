<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'item_number' => $this->item_number,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'type' => $this->type,
            'balance' => $this->balance,
            'minimum_limit' => $this->minimum_limit,
            'maximum_limit' => $this->maximum_limit,
            'reorder_limit' => $this->reorder_limit,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'user_id' => $this->user_id,
            'unit_id' => $this->unit_id,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relationships
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),

            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                    'symbol' => $this->unit->symbol,
                ];
            }),

            'parent' => $this->whenLoaded('parent', function () {
                return [
                    'id' => $this->parent->id,
                    'item_number' => $this->parent->item_number,
                    'name' => $this->parent->name,
                ];
            }),

            'children' => $this->whenLoaded('children', function () {
                return $this->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'item_number' => $child->item_number,
                        'name' => $child->name,
                        'balance' => $child->balance,
                    ];
                });
            }),

            'item_units' => $this->whenLoaded('itemUnits', function () {
                return $this->itemUnits->map(function ($itemUnit) {
                    return [
                        'id' => $itemUnit->id,
                        'conversion_factor' => $itemUnit->conversion_factor,
                        'unit' => $itemUnit->whenLoaded('unit', function () use ($itemUnit) {
                            return [
                                'id' => $itemUnit->unit->id,
                                'name' => $itemUnit->unit->name,
                                'symbol' => $itemUnit->unit->symbol,
                            ];
                        }),
                    ];
                });
            }),

            // Computed properties
            'formatted_balance' => number_format($this->balance, 2),
            'is_low_stock' => $this->balance <= $this->minimum_limit,
            'is_high_stock' => $this->balance >= $this->maximum_limit,
            'needs_reorder' => $this->balance <= $this->reorder_limit,
            'stock_status' => $this->getStockStatus(),
            'has_children' => $this->children()->exists(),
            'has_parent' => !is_null($this->parent_id),

            // Display properties
            'display_name' => $this->name,
            'full_item_number' => $this->item_number,
            'type_display' => ucfirst(str_replace('_', ' ', $this->type)),

            // Status indicators
            'status_color' => $this->getStatusColor(),
            'status_text' => $this->getStatusText(),
            'stock_level_percentage' => $this->getStockLevelPercentage(),

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
     * Get stock status based on balance levels.
     */
    private function getStockStatus(): string
    {
        if ($this->balance <= 0) {
            return 'out_of_stock';
        } elseif ($this->balance <= $this->minimum_limit) {
            return 'low_stock';
        } elseif ($this->balance <= $this->reorder_limit) {
            return 'reorder_needed';
        } elseif ($this->balance >= $this->maximum_limit) {
            return 'overstock';
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
            'overstock' => 'blue',
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
            'overstock' => 'Overstock',
            'in_stock' => 'In Stock',
            default => 'Unknown'
        };
    }

    /**
     * Get stock level as percentage.
     */
    private function getStockLevelPercentage(): float
    {
        if ($this->maximum_limit <= 0) {
            return 0;
        }

        return min(100, ($this->balance / $this->maximum_limit) * 100);
    }

    /**
     * Check if user can edit this item.
     */
    private function canEdit($user): bool
    {
        return $this->company_id === $user->company_id;
    }

    /**
     * Check if user can delete this item.
     */
    private function canDelete($user): bool
    {
        return $this->company_id === $user->company_id && 
               !$this->children()->exists();
    }
}
