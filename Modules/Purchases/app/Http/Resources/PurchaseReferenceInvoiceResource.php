<?php

namespace Modules\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Purchases\Models\Purchase;

class PurchaseReferenceInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if resource data is null
        if (!$this->resource) {
            return [
                'error' => 'Resource data is null',
                'message' => 'The purchase reference invoice data could not be loaded.'
            ];
        }

        return [
            // Basic Information
            'id' => $this->id,
            'type' => $this->type,
            'purchase_reference_invoice_number' => $this->purchase_reference_invoice_number,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'status_label' => Purchase::STATUS_OPTIONS[$this->status] ?? $this->status,

            // Dates
            'date' => $this->date?->format('Y-m-d'),
            'time' => $this->time?->format('H:i:s'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Supplier Information
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name,
            'supplier_email' => $this->supplier_email,
            'licensed_operator' => $this->licensed_operator,
            'supplier' => $this->whenLoaded('supplier', function () {
                return $this->supplier ? [
                    'id' => $this->supplier->id,
                    'supplier_number' => $this->supplier->supplier_number,
                    'supplier_name_ar' => $this->supplier->supplier_name_ar,
                    'supplier_name_en' => $this->supplier->supplier_name_en,
                    'email' => $this->supplier->email,
                    'mobile' => $this->supplier->mobile,
                    'phone' => $this->supplier->phone,
                    'address_one' => $this->supplier->address_one,
                    'tax_number' => $this->supplier->tax_number,
                ] : null;
            }),

            // Company and Branch
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'company' => $this->whenLoaded('company', function () {
                return $this->company ? [
                    'id' => $this->company->id,
                    'title' => $this->company->title,
                ] : null;
            }),
            'branch' => $this->whenLoaded('branch', function () {
                return $this->branch ? [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ] : null;
            }),

            // Currency Information
            'currency_id' => $this->currency_id,
            'exchange_rate' => (float) $this->exchange_rate,
            'currency_rate' => (float) $this->currency_rate,
            'currency_rate_with_tax' => (float) $this->currency_rate_with_tax,
            'currency' => $this->whenLoaded('currency', function () {
                return $this->currency ? [
                    'id' => $this->currency->id,
                    'code' => $this->currency->code,
                    'name' => $this->currency->name,
                    'symbol' => $this->currency->symbol,
                ] : null;
            }),

            // Ledger System
            'ledger_code' => $this->ledger_code,
            'ledger_number' => $this->ledger_number,
            'ledger_invoice_count' => $this->ledger_invoice_count,
            'journal_code' => $this->journal_code,
            'journal_number' => $this->journal_number,
            'journal_invoice_count' => $this->journal_invoice_count,

            // Tax Information
            'tax_rate_id' => $this->tax_rate_id,
            'tax_percentage' => (float) $this->tax_percentage,
            'tax_amount' => (float) $this->tax_amount,
            'is_tax_inclusive' => (bool) $this->is_tax_inclusive,
            'is_tax_applied_to_currency' => (bool) $this->is_tax_applied_to_currency,
            'is_tax_applied_to_currency_rate' => (bool) $this->is_tax_applied_to_currency_rate,
            'tax_rate' => $this->whenLoaded('taxRate', function () {
                return $this->taxRate ? [
                    'id' => $this->taxRate->id,
                    'name' => $this->taxRate->name,
                    'rate' => $this->taxRate->rate,
                ] : null;
            }),

            // Financial Information
            'cash_paid' => (float) $this->cash_paid,
            'checks_paid' => (float) $this->checks_paid,
            'allowed_discount' => (float) $this->allowed_discount,
            'discount_percentage' => (float) $this->discount_percentage,
            'discount_amount' => (float) $this->discount_amount,
            'total_without_tax' => (float) $this->total_without_tax,
            'total_foreign' => (float) $this->total_foreign,
            'total_local' => (float) $this->total_local,
            'total_amount' => (float) $this->total_amount,
            'grand_total' => (float) $this->grand_total,
            'remaining_balance' => (float) $this->remaining_balance,

            // Inventory Impact
            'affects_inventory' => (bool) $this->affects_inventory,

            // Additional Information
            'notes' => $this->notes,
            'user_id' => $this->user_id,
            'employee_id' => $this->employee_id,
            'customer_id' => $this->customer_id,
            'journal_id' => $this->journal_id,

            // Items with detailed information
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'serial_number' => $item->serial_number,
                        'item_id' => $item->item_id,
                        'item_number' => $item->item_number,
                        'item_name' => $item->item_name,
                        'item' => ($item->relationLoaded('item') && $item->item) ? [
                            'id' => $item->item->id,
                            'item_number' => $item->item->item_number,
                            'item_name_ar' => $item->item->item_name_ar,
                            'item_name_en' => $item->item->item_name_en,
                            'first_selling_price' => $item->item->first_selling_price,
                        ] : null,
                        'unit_id' => $item->unit_id,
                        'unit_name' => $item->unit_name,
                        'unit' => ($item->relationLoaded('unit') && $item->unit) ? [
                            'id' => $item->unit->id,
                            'name' => $item->unit->name,
                        ] : null,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'first_selling_price' => (float) $item->first_selling_price,
                        'total' => (float) $item->total,
                        'notes' => $item->notes,
                        'affects_inventory' => (bool) $item->affects_inventory,
                    ];
                });
            }),

            // Audit Information
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'creator' => $this->whenLoaded('creator', function () {
                return $this->creator ? [
                    'id' => $this->creator->id,
                    'first_name' => $this->creator->first_name,
                    'second_name' => $this->creator->second_name,
                    'email' => $this->creator->email,
                ] : null;
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return $this->updater ? [
                    'id' => $this->updater->id,
                    'first_name' => $this->updater->first_name,
                    'second_name' => $this->updater->second_name,
                    'email' => $this->updater->email,
                ] : null;
            }),

            // Journal Information
            'journal' => $this->whenLoaded('journal', function () {
                return $this->journal ? [
                    'id' => $this->journal->id,
                    'name' => $this->journal->name,
                    'code' => $this->journal->code,
                ] : null;
            }),

            // Statistics
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),
            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),

            // Formatted values for frontend
            'formatted' => [
                'date' => $this->date?->format('d/m/Y'),
                'due_date' => $this->due_date?->format('d/m/Y'),
                'total_amount' => number_format($this->total_amount, 2),
                'grand_total' => number_format($this->grand_total, 2),
                'cash_paid' => number_format($this->cash_paid, 2),
                'checks_paid' => number_format($this->checks_paid, 2),
                'remaining_balance' => number_format($this->remaining_balance, 2),
                'total_without_tax' => number_format($this->total_without_tax, 2),
                'tax_amount' => number_format($this->tax_amount, 2),
                'discount_amount' => number_format($this->discount_amount, 2),
                'status_badge' => $this->getStatusBadge(),
                'created_at' => $this->created_at?->format('d/m/Y H:i'),
                'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            ],
        ];
    }

    /**
     * Get status badge for frontend display
     */
    private function getStatusBadge(): array
    {
        $statusColors = [
            'draft' => ['color' => 'gray', 'text' => 'Draft'],
            'pending' => ['color' => 'yellow', 'text' => 'Pending'],
            'approved' => ['color' => 'green', 'text' => 'Approved'],
            'rejected' => ['color' => 'red', 'text' => 'Rejected'],
            'invoiced' => ['color' => 'blue', 'text' => 'Invoiced'],
            'paid' => ['color' => 'green', 'text' => 'Paid'],
            'cancelled' => ['color' => 'red', 'text' => 'Cancelled'],
        ];

        return $statusColors[$this->status] ?? ['color' => 'gray', 'text' => ucfirst($this->status)];
    }
}
