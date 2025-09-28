<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // Basic Information
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(),

            // Auto-generated fields
            'book_code' => $this->book_code,
            'invoice_number' => $this->invoice_number,
            'journal_number' => $this->journal_number,
            'date' => $this->date,
            'time' => $this->time ? $this->time->format('H:i:s') : null,
            'due_date' => $this->due_date,

            // Customer Information
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'customer_number' => $this->customer->customer_number,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'licensed_operator' => $this->customer->licensed_operator,
                ];
            }),
            'customer_name' => $this->customer?->name,
            'customer_number' => $this->customer?->customer_number,
            'customer_email' => $this->customer_email,
            'licensed_operator' => $this->licensed_operator,

            // Currency Information
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'code' => $this->currency->code,
                    'name' => $this->currency->name,
                    'symbol' => $this->currency->symbol,
                ];
            }),
            'currency_code' => $this->currency?->code,
            'currency_name' => $this->currency?->name,
            'currency_symbol' => $this->currency?->symbol,
            'exchange_rate' => $this->exchange_rate,

            // Employee Information
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'employee_number' => $this->employee->employee_number,
                    'first_name' => $this->employee->first_name,
                    'second_name' => $this->employee->second_name,
                    'email' => $this->employee->email,
                ];
            }),
            'employee_name' => $this->employee ? ($this->employee->first_name . ' ' . $this->employee->second_name) : null,

            // Financial Information
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'allowed_discount' => $this->allowed_discount,
            'discount_percentage' => $this->discount_percentage,
            'total_without_tax' => $this->total_without_tax,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'is_tax_inclusive' => $this->is_tax_inclusive,
            'remaining_balance' => $this->remaining_balance,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,
            'total_amount' => $this->total_amount,

            // Formatted amounts
            'formatted_total_amount' => $this->getFormattedTotal(),
            'formatted_remaining_balance' => $this->getFormattedRemainingBalance(),

            // Company and Branch
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'journal_id' => $this->journal_id,

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
                        'unit_name' => $item->unit?->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_rate' => $item->discount_rate,
                        'discount_amount' => $item->discount_amount,
                        'tax_rate' => $item->tax_rate,
                        'total' => $item->total,
                        'description' => $item->description,
                    ];
                });
            }),
            'items_count' => $this->items_count ?? $this->items?->count() ?? 0,

            // Additional Information
            'notes' => $this->notes,

            // Audit Information
            'user' => new UserResource($this->whenLoaded('user')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),

            // Display helpers
            'can_edit' => $this->status !== 'invoiced',
            'can_delete' => $this->status !== 'invoiced',
        ];
    }

    /**
     * Get status display name
     */
    private function getStatusDisplay(): string
    {
        $statusMap = [
            'draft' => 'Draft',
            'approved' => 'Approved',
            'sent' => 'Sent',
            'invoiced' => 'Invoiced',
            'cancelled' => 'Cancelled'
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get formatted total with currency
     */
    private function getFormattedTotal(): string
    {
        $symbol = $this->currency?->symbol ?? '';
        return $symbol . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted remaining balance with currency
     */
    private function getFormattedRemainingBalance(): string
    {
        $symbol = $this->currency?->symbol ?? '';
        return $symbol . ' ' . number_format($this->remaining_balance, 2);
    }
}
