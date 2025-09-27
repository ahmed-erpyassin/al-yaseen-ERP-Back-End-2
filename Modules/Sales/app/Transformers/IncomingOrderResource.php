<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class IncomingOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'company' => $this->whenLoaded('company'),
            'branch' => $this->whenLoaded('branch'),
            'currency' => $this->whenLoaded('currency'),
            'employee' => $this->whenLoaded('employee'),
            'customer' => $this->whenLoaded('customer'),

            // IDs for reference
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'currency_id' => $this->currency_id,
            'employee_id' => $this->employee_id,
            'customer_id' => $this->customer_id,
            'journal_id' => $this->journal_id,
            'journal_number' => $this->journal_number,

            // Order Information
            'book_code' => $this->book_code,
            'book_display' => $this->book_display,
            'invoice_number' => $this->invoice_number,
            'date' => $this->date,
            'time' => $this->time,
            'due_date' => $this->due_date,
            'type' => $this->type,
            'status' => $this->status,

            // Customer Information
            'customer_email' => $this->customer_email,
            'licensed_operator' => $this->licensed_operator,

            // Financial Information
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'allowed_discount' => $this->allowed_discount,
            'discount_percentage' => $this->discount_percentage,
            'total_without_tax' => $this->total_without_tax,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'remaining_balance' => $this->remaining_balance,
            'exchange_rate' => $this->exchange_rate,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,
            'total_amount' => $this->total_amount,
            'formatted_total' => $this->formatted_total,
            'is_tax_inclusive' => $this->is_tax_inclusive,
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
                        'item_display_name' => $item->item_display_name,
                        'unit_name' => $item->unit_name,
                        'unit_display_name' => $item->unit_display_name,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'formatted_unit_price' => $item->formatted_unit_price,
                        'discount_rate' => $item->discount_rate,
                        'discount_percentage' => $item->discount_percentage,
                        'discount_amount' => $item->discount_amount,
                        'tax_rate' => $item->tax_rate,
                        'total' => $item->total,
                        'formatted_total' => $item->formatted_total,
                        'total_foreign' => $item->total_foreign,
                        'total_local' => $item->total_local,
                    ];
                });
            }),

            // Audit Information
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
