<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class ReturnInvoiceResource extends JsonResource
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

            // Ledger system fields
            'ledger_code' => $this->ledger_code,
            'ledger_number' => $this->ledger_number,
            'ledger_invoice_count' => $this->ledger_invoice_count,

            // Auto-generated fields
            'invoice_number' => $this->invoice_number,
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'time' => $this->time ? $this->time->format('H:i:s') : null,
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,

            // Customer information
            'customer_id' => $this->customer_id,
            'customer_number' => $this->customer_number,
            'customer_name' => $this->customer_name,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'customer_number' => $this->customer->customer_number,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),
            'customer_email' => $this->customer_email,

            // Return invoice details
            'licensed_operator' => $this->licensed_operator,

            // Employee information
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->name,
                    'employee_number' => $this->employee->employee_number,
                ];
            }),

            // Currency information
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'name' => $this->currency->name,
                    'code' => $this->currency->code,
                    'symbol' => $this->currency->symbol,
                ];
            }),
            'exchange_rate' => $this->exchange_rate,

            // Tax settings
            'is_tax_inclusive' => $this->is_tax_inclusive,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,

            // Financial totals
            'total_without_tax' => $this->total_without_tax,
            'total_amount' => $this->total_amount,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,

            // System fields
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'notes' => $this->notes,

            // Branch information
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                    'code' => $this->branch->code,
                ];
            }),

            // Return invoice items
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
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                        'tax_rate_id' => $item->tax_rate_id,
                        'tax_amount' => $item->tax_amount,
                        'notes' => $item->notes,
                        'item' => $item->whenLoaded('item', function () use ($item) {
                            return [
                                'id' => $item->item->id,
                                'item_number' => $item->item->item_number,
                                'name' => $item->item->name,
                                'first_sale_price' => $item->item->first_sale_price,
                            ];
                        }),
                        'unit' => $item->whenLoaded('unit', function () use ($item) {
                            return [
                                'id' => $item->unit->id,
                                'name' => $item->unit->name,
                                'symbol' => $item->unit->symbol,
                            ];
                        }),
                        'tax_rate' => $item->whenLoaded('taxRate', function () use ($item) {
                            return [
                                'id' => $item->taxRate->id,
                                'name' => $item->taxRate->name,
                                'code' => $item->taxRate->code,
                                'rate' => $item->taxRate->rate,
                                'type' => $item->taxRate->type,
                            ];
                        }),
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

            // Display helpers
            'can_edit' => !in_array($this->status, ['completed', 'invoiced']),
            'can_delete' => !in_array($this->status, ['completed', 'invoiced']),
            'is_overdue' => $this->due_date && $this->due_date->isPast() && $this->status !== 'completed',
            
            // Formatted display values
            'formatted_total' => number_format($this->total_amount, 2),
            'formatted_date' => $this->date ? $this->date->format('d/m/Y') : null,
            'formatted_time' => $this->time ? $this->time->format('H:i') : null,
            'status_label' => ucfirst(str_replace('_', ' ', $this->status)),
            'ledger_display' => $this->ledger_code . ' (' . $this->ledger_invoice_count . '/50)',
        ];
    }
}
