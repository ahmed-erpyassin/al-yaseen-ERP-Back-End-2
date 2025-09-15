<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relationships
            'item' => $this->whenLoaded('item', function () {
                return [
                    'id' => $this->item->id,
                    'item_number' => $this->item->item_number,
                    'name' => $this->item->name,
                    'unit' => $this->item->whenLoaded('unit', function () {
                        return [
                            'id' => $this->item->unit->id,
                            'name' => $this->item->unit->name,
                            'symbol' => $this->item->unit->symbol,
                        ];
                    }),
                ];
            }),

            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                    'code' => $this->warehouse->code,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),

            // Computed properties
            'formatted_quantity' => number_format($this->quantity, 2),
            'type_display' => $this->getTypeDisplay(),
            'type_color' => $this->getTypeColor(),
            'movement_direction' => $this->getMovementDirection(),
            'is_inbound' => $this->isInbound(),
            'is_outbound' => $this->isOutbound(),

            // Display properties
            'display_reference' => $this->reference ?: 'N/A',
            'display_notes' => $this->notes ?: 'No notes',
            'formatted_date' => $this->created_at?->format('M d, Y H:i'),
            'relative_date' => $this->created_at?->diffForHumans(),

            // Movement impact
            'quantity_with_sign' => $this->getQuantityWithSign(),
            'formatted_quantity_with_sign' => $this->getFormattedQuantityWithSign(),

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
     * Get display name for movement type.
     */
    private function getTypeDisplay(): string
    {
        return match ($this->type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'purchase' => 'Purchase',
            'sale' => 'Sale',
            'transfer' => 'Transfer',
            'return' => 'Return',
            'adjustment' => 'Adjustment',
            'production' => 'Production',
            'consumption' => 'Consumption',
            default => ucfirst($this->type)
        };
    }

    /**
     * Get color for movement type.
     */
    private function getTypeColor(): string
    {
        return match ($this->type) {
            'in', 'purchase', 'return' => 'green',
            'out', 'sale', 'consumption' => 'red',
            'transfer' => 'blue',
            'adjustment' => 'orange',
            'production' => 'purple',
            default => 'gray'
        };
    }

    /**
     * Get movement direction.
     */
    private function getMovementDirection(): string
    {
        return $this->isInbound() ? 'inbound' : 'outbound';
    }

    /**
     * Check if movement is inbound (increases stock).
     */
    private function isInbound(): bool
    {
        return in_array($this->type, ['in', 'purchase', 'return']) || 
               ($this->type === 'adjustment' && $this->quantity > 0);
    }

    /**
     * Check if movement is outbound (decreases stock).
     */
    private function isOutbound(): bool
    {
        return in_array($this->type, ['out', 'sale', 'transfer', 'consumption']) ||
               ($this->type === 'adjustment' && $this->quantity < 0);
    }

    /**
     * Get quantity with appropriate sign.
     */
    private function getQuantityWithSign(): float
    {
        if ($this->type === 'adjustment') {
            return $this->quantity; // Adjustments can be positive or negative
        }

        return $this->isInbound() ? $this->quantity : -$this->quantity;
    }

    /**
     * Get formatted quantity with sign.
     */
    private function getFormattedQuantityWithSign(): string
    {
        $quantity = $this->getQuantityWithSign();
        $sign = $quantity >= 0 ? '+' : '';
        
        return $sign . number_format($quantity, 2);
    }

    /**
     * Check if user can edit this movement.
     */
    private function canEdit($user): bool
    {
        return $this->company_id === $user->company_id &&
               $this->created_at->isAfter(now()->subHours(24)); // Can only edit within 24 hours
    }

    /**
     * Check if user can delete this movement.
     */
    private function canDelete($user): bool
    {
        return $this->company_id === $user->company_id &&
               $this->created_at->isAfter(now()->subHours(24)); // Can only delete within 24 hours
    }
}
