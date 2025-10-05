<?php

namespace Modules\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Purchases\Models\Purchase;

class ExpenseResource extends JsonResource
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
            'expense_number' => $this->expense_number,
            'type' => $this->type,
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
                ] : null;
            }),

            // Company and Branch
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
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
            'journal_invoice_count' => $this->journal_invoice_count,
            'invoice_number' => $this->invoice_number,

            // Financial Information
            'discount_percentage' => (float) $this->discount_percentage,
            'discount_amount' => (float) $this->discount_amount,
            'total_without_tax' => (float) $this->total_without_tax,
            'tax_percentage' => (float) $this->tax_percentage,
            'tax_amount' => (float) $this->tax_amount,
            'is_tax_applied_to_currency' => (bool) $this->is_tax_applied_to_currency,
            'tax_rate_id' => $this->tax_rate_id,
            'total_foreign' => (float) $this->total_foreign,
            'total_local' => (float) $this->total_local,
            'total_amount' => (float) $this->total_amount,
            'grand_total' => (float) $this->grand_total,

            // Additional Information
            'notes' => $this->notes,

            // Items
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'serial_number' => $item->serial_number,
                        'account_id' => $item->account_id,
                        'account_number' => $item->account_number,
                        'account_name' => $item->account_name,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total' => (float) $item->total,
                        'notes' => $item->notes,
                        'account' => $item->relationLoaded('account') && $item->account ? [
                            'id' => $item->account->id,
                            'code' => $item->account->code,
                            'name' => $item->account->name,
                            'type' => $item->account->type,
                        ] : null,
                    ];
                });
            }),

            // All Additional Fields from purchases table
            'user_id' => $this->user_id,
            'employee_id' => $this->employee_id,
            'customer_id' => $this->customer_id,
            'journal_id' => $this->journal_id,
            'journal_number' => $this->journal_number,
            'cash_paid' => (float) $this->cash_paid,
            'checks_paid' => (float) $this->checks_paid,
            'allowed_discount' => (float) $this->allowed_discount,
            'remaining_balance' => (float) $this->remaining_balance,
            'reference_number' => $this->reference_number,
            'outgoing_order_number' => $this->outgoing_order_number,
            'quotation_number' => $this->quotation_number,

            // Audit Information
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'creator' => $this->whenLoaded('creator', function () {
                return $this->creator ? [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ] : null;
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),
            'deleter' => $this->whenLoaded('deleter', function () {
                return [
                    'id' => $this->deleter->id,
                    'name' => $this->deleter->name,
                    'email' => $this->deleter->email,
                ];
            }),

            // Tax Rate Information
            'tax_rate' => $this->whenLoaded('taxRate', function () {
                return [
                    'id' => $this->taxRate->id,
                    'name' => $this->taxRate->name,
                    'rate' => $this->taxRate->rate,
                ];
            }),

            // Journal Information
            'journal' => $this->whenLoaded('journal', function () {
                return [
                    'id' => $this->journal->id,
                    'name' => $this->journal->name,
                    'code' => $this->journal->code,
                ];
            }),

            // Branch Information
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),

            // Timestamps
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),

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
                'deleted_at' => $this->deleted_at?->format('d/m/Y H:i'),
            ],
        ];
    }

    /**
     * Get status badge class for frontend
     */
    private function getStatusBadge(): array
    {
        $badges = [
            'draft' => ['class' => 'badge-warning', 'text' => 'Draft'],
            'approved' => ['class' => 'badge-info', 'text' => 'Approved'],
            'sent' => ['class' => 'badge-primary', 'text' => 'Sent'],
            'invoiced' => ['class' => 'badge-success', 'text' => 'Invoiced'],
            'cancelled' => ['class' => 'badge-danger', 'text' => 'Cancelled'],
        ];

        return $badges[$this->status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($this->status)];
    }
}
