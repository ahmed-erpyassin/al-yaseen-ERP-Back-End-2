<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class OutgoingShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,

            // Auto-generated fields
            'book_code' => $this->book_code,
            'invoice_number' => $this->invoice_number,
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'time' => $this->time ? $this->time->format('H:i:s') : null,
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,

            // Customer information
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),
            'customer_email' => $this->customer_email,

            // Employee information
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->name,
                    'employee_number' => $this->employee->employee_number,
                ];
            }),

            // System fields
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'notes' => $this->notes,

            // Items
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'serial_number' => $item->serial_number,
                        'item_id' => $item->item_id,
                        'item_number' => $item->item_number,
                        'item_name' => $item->item_name,
                        'unit_id' => $item->unit_id,
                        'unit_name' => $item->unit_name,
                        'quantity' => $item->quantity,
                        'warehouse_id' => $item->warehouse_id,
                        'notes' => $item->notes,
                        'item' => $item->item ? [
                            'id' => $item->item->id,
                            'name' => $item->item->name,
                            'item_number' => $item->item->item_number,
                        ] : null,
                        'unit' => $item->unit ? [
                            'id' => $item->unit->id,
                            'name' => $item->unit->name,
                        ] : null,
                    ];
                });
            }),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at,

            // Computed fields
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),
            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),
        ];
    }
}
