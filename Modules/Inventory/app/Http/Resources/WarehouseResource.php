<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'manager_id' => $this->manager_id,
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
                    'address' => $this->branch->address,
                ];
            }),

            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->name,
                    'email' => $this->manager->email,
                    'phone' => $this->manager->phone,
                ];
            }),

            'stock_items' => $this->whenLoaded('stockItems', function () {
                return $this->stockItems->map(function ($stockItem) {
                    return [
                        'id' => $stockItem->id,
                        'item_id' => $stockItem->item_id,
                        'quantity' => $stockItem->quantity,
                        'item' => $stockItem->whenLoaded('item', function () use ($stockItem) {
                            return [
                                'id' => $stockItem->item->id,
                                'item_number' => $stockItem->item->item_number,
                                'name' => $stockItem->item->name,
                            ];
                        }),
                    ];
                });
            }),

            // Computed properties
            'status_display' => $this->is_active ? 'Active' : 'Inactive',
            'status_color' => $this->is_active ? 'green' : 'red',
            'display_name' => $this->name . ($this->code ? " ({$this->code})" : ''),
            'full_address' => $this->getFullAddress(),

            // Contact information
            'contact_info' => $this->getContactInfo(),
            'has_contact_info' => !empty($this->phone) || !empty($this->email),

            // Stock statistics (if stock items are loaded)
            'stock_summary' => $this->when(
                $this->relationLoaded('stockItems'),
                fn() => $this->getStockSummary()
            ),

            // Display properties
            'formatted_created_date' => $this->created_at?->format('M d, Y'),
            'relative_created_date' => $this->created_at?->diffForHumans(),

            // Permissions (if user is available in request)
            'can_edit' => $this->when(
                $request->user(),
                fn() => $this->canEdit($request->user())
            ),
            'can_delete' => $this->when(
                $request->user(),
                fn() => $this->canDelete($request->user())
            ),
            'can_manage_stock' => $this->when(
                $request->user(),
                fn() => $this->canManageStock($request->user())
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
     * Get full formatted address.
     */
    private function getFullAddress(): ?string
    {
        return $this->address ?: null;
    }

    /**
     * Get contact information array.
     */
    private function getContactInfo(): array
    {
        $contact = [];

        if ($this->phone) {
            $contact['phone'] = $this->phone;
        }

        if ($this->email) {
            $contact['email'] = $this->email;
        }

        return $contact;
    }

    /**
     * Get stock summary for the warehouse.
     */
    private function getStockSummary(): array
    {
        if (!$this->relationLoaded('stockItems')) {
            return [];
        }

        $stockItems = $this->stockItems;

        return [
            'total_items' => $stockItems->count(),
            'total_quantity' => $stockItems->sum('quantity'),
            'low_stock_items' => $stockItems->filter(function ($item) {
                return $item->quantity <= ($item->minimum_limit ?? 0);
            })->count(),
            'out_of_stock_items' => $stockItems->where('quantity', '<=', 0)->count(),
            'formatted_total_quantity' => number_format($stockItems->sum('quantity'), 2),
        ];
    }

    /**
     * Check if user can edit this warehouse.
     */
    private function canEdit($user): bool
    {
        return $this->company_id === $user->company_id;
    }

    /**
     * Check if user can delete this warehouse.
     */
    private function canDelete($user): bool
    {
        return $this->company_id === $user->company_id && 
               !$this->stockItems()->exists();
    }

    /**
     * Check if user can manage stock in this warehouse.
     */
    private function canManageStock($user): bool
    {
        return $this->company_id === $user->company_id && 
               $this->is_active &&
               ($this->manager_id === $user->id || $user->hasRole('admin'));
    }
}
