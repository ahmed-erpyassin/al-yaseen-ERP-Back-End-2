<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class OutgoingOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'company_name' => $this->customer->company_name,
                    'first_name' => $this->customer->first_name,
                    'second_name' => $this->customer->second_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),
            // 'employee' => $this->whenLoaded('employee', function () {
            //     return [
            //         'id' => $this->employee->id,
            //         'first_name' => $this->employee->first_name,
            //         'second_name' => $this->employee->second_name,
            //         'email' => $this->employee->email,
            //         'phone1' => $this->employee->phone1,
            //     ];
            // }),
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'name' => $this->currency->name,
                    'code' => $this->currency->code,
                    'symbol' => $this->currency->symbol,
                ];
            }),
            // 'items' => $this->whenLoaded('items', function () {
            //     return $this->items->map(function ($item) {
            //         return [
            //             'id' => $item->id,
            //             'item_id' => $item->item_id,
            //             'item' => $item->whenLoaded('item', function () use ($item) {
            //                 return [
            //                     'id' => $item->item->id,
            //                     'name' => $item->item->name,
            //                     'code' => $item->item->code,
            //                     'item_number' => $item->item->item_number,
            //                 ];
            //             }),
            //             'description' => $item->description,
            //             'quantity' => $item->quantity,
            //             'unit_price' => $item->unit_price,
            //             'discount_rate' => $item->discount_rate,
            //             'tax_rate' => $item->tax_rate,
            //             'total_foreign' => $item->total_foreign,
            //             'total_local' => $item->total_local,
            //             'total' => $item->total,
            //         ];
            //     });
            // }),
            'items' => $this->items,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'currency_id' => $this->currency_id,
            'employee_id' => $this->employee_id,
            'customer_id' => $this->customer_id,
            'journal_id' => $this->journal_id,
            'journal_number' => $this->journal_number,
            'invoice_number' => $this->invoice_number,
            'time' => $this->time,
            'due_date' => $this->due_date,
            'type' => $this->type,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'allowed_discount' => $this->allowed_discount,
            'total_without_tax' => $this->total_without_tax,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'remaining_balance' => $this->remaining_balance,
            'exchange_rate' => $this->exchange_rate,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'مسودة',
            'approved' => 'معتمد',
            'sent' => 'مرسل',
            'invoiced' => 'مفوتر',
            'cancelled' => 'ملغي',
            default => $this->status,
        };
    }
}
