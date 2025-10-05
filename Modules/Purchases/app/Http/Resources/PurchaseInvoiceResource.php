<?php

namespace Modules\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseInvoiceResource extends JsonResource
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
            
            // Basic Information
            'type' => $this->type,
            'status' => $this->status,
            
            // Invoice Numbers and Codes
            'invoice_number' => $this->invoice_number,
            'purchase_invoice_number' => $this->purchase_invoice_number,
            'entry_number' => $this->entry_number,
            'ledger_code' => $this->ledger_code,
            'ledger_number' => $this->ledger_number,
            'ledger_invoice_count' => $this->ledger_invoice_count,
            
            // Dates and Times
            'date' => $this->date?->format('Y-m-d'),
            'time' => $this->time?->format('H:i:s'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            
            // Supplier Information
            'supplier_id' => $this->supplier_id,
            'supplier_number' => $this->supplier_number,
            'supplier_name' => $this->supplier_name,
            'supplier_email' => $this->supplier_email,
            'supplier_mobile' => $this->supplier_mobile,
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'supplier_number' => $this->supplier->supplier_number,
                    'first_name' => $this->supplier->first_name,
                    'second_name' => $this->supplier->second_name,
                    'full_name' => trim($this->supplier->first_name . ' ' . $this->supplier->second_name),
                    'email' => $this->supplier->email,
                    'mobile' => $this->supplier->mobile,
                    'phone' => $this->supplier->phone,
                    'address' => $this->supplier->address,
                ];
            }),
            
            // Operational Information
            'licensed_operator' => $this->licensed_operator,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'employee_number' => $this->employee->employee_number,
                    'first_name' => $this->employee->first_name,
                    'last_name' => $this->employee->last_name,
                    'full_name' => trim($this->employee->first_name . ' ' . $this->employee->last_name),
                    'email' => $this->employee->email,
                ];
            }),
            
            // Currency Information
            'currency_id' => $this->currency_id,
            'exchange_rate' => $this->exchange_rate,
            'currency_rate' => $this->currency_rate,
            'currency_rate_with_tax' => $this->currency_rate_with_tax,
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'name' => $this->currency->name,
                    'code' => $this->currency->code,
                    'symbol' => $this->currency->symbol,
                ];
            }),
            
            // Tax Information
            'tax_rate_id' => $this->tax_rate_id,
            'is_tax_applied_to_currency' => $this->is_tax_applied_to_currency,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'tax_rate' => $this->whenLoaded('taxRate', function () {
                return [
                    'id' => $this->taxRate->id,
                    'name' => $this->taxRate->name,
                    'code' => $this->taxRate->code,
                    'rate' => $this->taxRate->rate,
                    'type' => $this->taxRate->type,
                ];
            }),
            
            // Financial Information
            'total_without_tax' => $this->total_without_tax,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'allowed_discount' => $this->allowed_discount,
            'total_amount' => $this->total_amount,
            'grand_total' => $this->grand_total,
            'total_foreign' => $this->total_foreign,
            'total_local' => $this->total_local,
            
            // Payment Information
            'cash_paid' => $this->cash_paid,
            'checks_paid' => $this->checks_paid,
            'remaining_balance' => $this->remaining_balance,
            
            // Items
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'serial_number' => $item->serial_number,
                        'item_id' => $item->item_id,
                        'item_number' => $item->item_number,
                        'item_name' => $item->item_name,
                        'item' => $item->whenLoaded('item', function () use ($item) {
                            return [
                                'id' => $item->item->id,
                                'name' => $item->item->name,
                                'item_number' => $item->item->item_number,
                                'description' => $item->item->description,
                                'barcode' => $item->item->barcode,
                            ];
                        }),
                        'unit_id' => $item->unit_id,
                        'unit_name' => $item->unit_name,
                        'unit' => $item->whenLoaded('unit', function () use ($item) {
                            return [
                                'id' => $item->unit->id,
                                'name' => $item->unit->name,
                                'symbol' => $item->unit->symbol,
                                'code' => $item->unit->code,
                            ];
                        }),
                        'warehouse_id' => $item->warehouse_id,
                        'warehouse' => $item->whenLoaded('warehouse', function () use ($item) {
                            return [
                                'id' => $item->warehouse->id,
                                'name' => $item->warehouse->name,
                                'warehouse_number' => $item->warehouse->warehouse_number,
                                'address' => $item->warehouse->address,
                            ];
                        }),
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percentage' => $item->discount_percentage,
                        'discount_amount' => $item->discount_amount,
                        'net_unit_price' => $item->net_unit_price,
                        'line_total_before_tax' => $item->line_total_before_tax,
                        'tax_rate' => $item->tax_rate,
                        'tax_amount' => $item->tax_amount,
                        'line_total_after_tax' => $item->line_total_after_tax,
                        'total' => $item->total,
                        'notes' => $item->notes,
                    ];
                });
            }),
            
            // Additional Information
            'notes' => $this->notes,
            
            // User Information
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'first_name' => $this->user->first_name,
                    'second_name' => $this->user->second_name,
                    'full_name' => trim($this->user->first_name . ' ' . $this->user->second_name),
                    'email' => $this->user->email,
                ];
            }),
            
            // Branch Information
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                    'address' => $this->branch->address,
                    'phone' => $this->branch->phone,
                    'email' => $this->branch->email,
                ];
            }),
            
            // Audit Information
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // Statistics (when available)
            'statistics' => $this->when(isset($this->statistics), $this->statistics ?? []),
        ];
    }
}
