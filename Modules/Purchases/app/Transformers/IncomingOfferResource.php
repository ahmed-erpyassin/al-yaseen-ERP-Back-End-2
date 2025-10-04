<?php

namespace Modules\Purchases\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class IncomingOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Basic Information
            'user' => new UserResource($this->whenLoaded('user')),
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company'),
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch'),
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency', function () {
                return $this->currency ? [
                    'id' => $this->currency->id,
                    'code' => $this->currency->code,
                    'name' => $this->currency->name,
                    'symbol' => $this->currency->symbol,
                ] : null;
            }),
            'employee_id' => $this->employee_id,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier', function () {
                return $this->supplier ? [
                    'id' => $this->supplier->id,
                    'supplier_number' => $this->supplier->supplier_number,
                    'supplier_name_ar' => $this->supplier->supplier_name_ar,
                    'supplier_name_en' => $this->supplier->supplier_name_en,
                    'email' => $this->supplier->email,
                    'mobile' => $this->supplier->mobile,
                ] : null;
            }),
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer'),

            // Quotation Information
            'quotation_number' => $this->quotation_number,
            'invoice_number' => $this->invoice_number,
            'date' => $this->date,
            'time' => $this->time,
            'due_date' => $this->due_date,

            // Customer Information
            'customer_number' => $this->customer_number,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_mobile' => $this->customer_mobile,

            // Supplier Information
            'supplier_name' => $this->supplier_name,
            'licensed_operator' => $this->licensed_operator,

            // Ledger System
            'journal_id' => $this->journal_id,
            'journal_number' => $this->journal_number,
            'ledger_code' => $this->ledger_code,
            'ledger_number' => $this->ledger_number,
            'ledger_invoice_count' => $this->ledger_invoice_count,

            // Type and Status
            'type' => $this->type,
            'status' => $this->status,

            // Financial Information
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'allowed_discount' => $this->allowed_discount,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'total_without_tax' => $this->total_without_tax,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'grand_total' => $this->grand_total,
            'remaining_balance' => $this->remaining_balance,

            // Currency Information
            'exchange_rate' => $this->exchange_rate,
            'currency_rate' => $this->currency_rate,
            'currency_rate_with_tax' => $this->currency_rate_with_tax,
            'tax_rate_id' => $this->tax_rate_id,
            'tax_rate' => $this->whenLoaded('taxRate'),
            'is_tax_applied_to_currency' => $this->is_tax_applied_to_currency,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,
            'total_amount' => $this->total_amount,

            // Additional Information
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
                        'item' => $item->relationLoaded('item') && $item->item ? [
                            'id' => $item->item->id,
                            'item_number' => $item->item->item_number,
                            'name' => $item->item->name,
                            'name_ar' => $item->item->name_ar,
                        ] : null,
                        'unit_id' => $item->unit_id,
                        'unit_name' => $item->unit_name,
                        'unit' => $item->relationLoaded('unit') && $item->unit ? [
                            'id' => $item->unit->id,
                            'name' => $item->unit->name,
                            'name_ar' => $item->unit->name_ar,
                        ] : null,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_rate' => $item->discount_rate,
                        'discount_percentage' => $item->discount_percentage,
                        'discount_amount' => $item->discount_amount,
                        'net_unit_price' => $item->net_unit_price,
                        'line_total_before_tax' => $item->line_total_before_tax,
                        'tax_rate' => $item->tax_rate,
                        'tax_amount' => $item->tax_amount,
                        'line_total_after_tax' => $item->line_total_after_tax,
                        'total_foreign' => $item->total_foreign,
                        'total_local' => $item->total_local,
                        'total' => $item->total,
                        'notes' => $item->notes,
                    ];
                });
            }),

            // Audit Fields
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator'),
            'updated_by' => $this->updated_by,
            'updater' => $this->whenLoaded('updater'),
            'deleted_by' => $this->deleted_by,
            'deleter' => $this->whenLoaded('deleter'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
