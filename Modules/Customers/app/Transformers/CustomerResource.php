<?php

namespace Modules\Customers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user'         => new UserResource($this->whenLoaded('user')),
            'company'      => $this->whenLoaded('company'),
            'currency'     => $this->whenLoaded('currency'),
            'country'      => $this->whenLoaded('country'),
            'region'       => $this->whenLoaded('region'),
            'city'         => $this->whenLoaded('city'),
            'employee'     => new UserResource($this->whenLoaded('employee')),
            'branch'       => $this->whenLoaded('branch'),
            'creator'      => new UserResource($this->whenLoaded('creator')),
            'updater'      => new UserResource($this->whenLoaded('updater')),
            'deleter'      => new UserResource($this->whenLoaded('deleter')),

            // IDs for reference
            'user_id'      => $this->user_id,
            'company_id'   => $this->company_id,
            'branch_id'    => $this->branch_id,
            'currency_id'  => $this->currency_id,
            'employee_id'  => $this->employee_id,
            'country_id'   => $this->country_id,
            'region_id'    => $this->region_id,
            'city_id'      => $this->city_id,

            // Customer Information
            'customer_number' => $this->customer_number,
            'customer_type'   => $this->customer_type,
            'customer_type_display' => $this->customer_type_display,
            'balance'         => $this->balance,
            'formatted_balance' => $this->formatted_balance,
            'company_name'    => $this->company_name,
            'first_name'      => $this->first_name,
            'second_name'     => $this->second_name,
            'full_name'       => trim($this->first_name . ' ' . $this->second_name),
            'contact_name'    => $this->contact_name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'mobile'          => $this->mobile,
            'address_one'     => $this->address_one,
            'address_two'     => $this->address_two,
            'postal_code'     => $this->postal_code,
            'licensed_operator' => $this->licensed_operator,
            'tax_number'      => $this->tax_number,
            'notes'           => $this->notes,
            'code'            => $this->code,
            'barcode'         => $this->barcode,
            'barcode_type'    => $this->barcode_type,
            'invoice_type'    => $this->invoice_type,
            'category'        => $this->category,

            // Transaction Information
            'last_transaction_date' => $this->last_transaction_date,
            'sales_count'     => $this->whenLoaded('sales', function () {
                return $this->sales->count();
            }),
            'invoices_count'  => $this->whenLoaded('invoices', function () {
                return $this->invoices->count();
            }),

            // Audit Information
            'created_by'   => $this->created_by,
            'updated_by'   => $this->updated_by,
            'deleted_by'   => $this->deleted_by,
            'status'       => $this->status,

            // Timestamps
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'deleted_at'   => $this->deleted_at,
        ];
    }
}
