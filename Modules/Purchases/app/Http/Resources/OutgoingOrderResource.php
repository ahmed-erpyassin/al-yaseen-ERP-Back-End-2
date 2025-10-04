<?php

namespace Modules\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Purchases\Models\Purchase;

class OutgoingOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'outgoing_order_number' => $this->outgoing_order_number,
            'journal_code' => $this->journal_code,
            'journal_number' => $this->journal_number,
            'invoice_number' => $this->journal_number, // Same as journal_number
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'time' => $this->time ? $this->time->format('H:i:s') : null,
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,

            // Customer information
            'customer_id' => $this->customer_id,
            'customer_number' => $this->customer_number,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_mobile' => $this->customer_mobile,
            'licensed_operator' => $this->licensed_operator,

            // Customer relationship data
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'customer_number' => $this->customer->customer_number,
                    'first_name' => $this->customer->first_name,
                    'second_name' => $this->customer->second_name,
                    'company_name' => $this->customer->company_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'mobile' => $this->customer->mobile,
                ];
            }),

            // Currency information
            'currency_id' => $this->currency_id,
            'exchange_rate' => $this->exchange_rate,
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'code' => $this->currency->code,
                    'name' => $this->currency->name,
                    'symbol' => $this->currency->symbol,
                ];
            }),

            // Financial information
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'allowed_discount' => $this->allowed_discount,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'total_without_tax' => $this->total_without_tax,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'is_tax_inclusive' => $this->is_tax_inclusive,
            'total_amount' => $this->total_amount,
            'remaining_balance' => $this->remaining_balance,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,

            // Status and type
            'type' => $this->type,
            'status' => $this->status,

            // Items
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'serial_number' => $item->serial_number,
                        'item_id' => $item->item_id,
                        'item_number' => $item->item_number,
                        'item_name' => $item->item_name,
                        'unit' => $item->unit,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percentage' => $item->discount_percentage,
                        'discount_amount' => $item->discount_amount,
                        'total_without_tax' => $item->total_without_tax,
                        'tax_rate' => $item->tax_rate,
                        'total' => $item->total,
                        'description' => $item->description,
                        'item' => $item->relationLoaded('item') && $item->item ? [
                            'id' => $item->item->id,
                            'item_number' => $item->item->item_number,
                            'name' => $item->item->name,
                            'name_ar' => $item->item->name_ar,
                            'first_selling_price' => $item->item->first_selling_price,
                        ] : null,
                    ];
                });
            }),

            // Additional information
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),

            // Audit information
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),

            // Company and branch information
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'employee_id' => $this->employee_id,
            'journal_id' => $this->journal_id,
            'journal_invoice_count' => $this->journal_invoice_count,

            // Additional financial fields
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'remaining_balance' => $this->remaining_balance,

            // Journal information
            'journal' => $this->whenLoaded('journal', function () {
                return [
                    'id' => $this->journal->id,
                    'name' => $this->journal->name,
                    'code' => $this->journal->code,
                    'type' => $this->journal->type,
                ];
            }),

            // Formatted display values
            'formatted_date' => $this->date ? $this->date->format('d/m/Y') : null,
            'formatted_time' => $this->time ? $this->time->format('H:i') : null,
            'formatted_due_date' => $this->due_date ? $this->due_date->format('d/m/Y') : null,
            'formatted_total_amount' => number_format($this->total_amount, 2),
            'formatted_total_without_tax' => number_format($this->total_without_tax, 2),
            'formatted_tax_amount' => number_format($this->tax_amount, 2),
            'formatted_exchange_rate' => number_format($this->exchange_rate, 4),

            // Status display
            'status_label' => Purchase::STATUS_OPTIONS[$this->status] ?? $this->status,
            'type_label' => Purchase::TYPE_OPTIONS[$this->type] ?? $this->type,

            // Items count
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),

            // Total items quantity
            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),
        ];
    }
}
