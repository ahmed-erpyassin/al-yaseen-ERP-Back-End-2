<?php

namespace Modules\Suppliers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Transformers\UserResource;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id'           => $this->id,

            // Relations IDs
            'user_id'      => $this->user_id,
            'company_id'   => $this->company_id,
            'branch_id'    => $this->branch_id,
            'currency_id'  => $this->currency_id,
            'employee_id'  => $this->employee_id,
            'department_id' => $this->department_id,
            'project_id'   => $this->project_id,
            'donor_id'     => $this->donor_id,
            'sales_representative_id' => $this->sales_representative_id,

            // Location Information
            'country_id'   => $this->country_id,
            'region_id'    => $this->region_id,
            'city_id'      => $this->city_id,

            // Basic Supplier Information
            'supplier_name_ar' => $this->supplier_name_ar,
            'supplier_name_en' => $this->supplier_name_en,
            'supplier_code'    => $this->supplier_code,
            'supplier_number'  => $this->supplier_number,
            'supplier_type'    => $this->supplier_type,
            'supplier_type_display' => $this->supplier_type_display,
            'contact_person'   => $this->contact_person,

            // Personal Names
            'first_name'   => $this->first_name,
            'second_name'  => $this->second_name,
            'contact_name' => $this->contact_name,

            // Contact Information
            'email'        => $this->email,
            'phone'        => $this->phone,
            'mobile'       => $this->mobile,
            'website'      => $this->website,

            // Address Information
            'address_one'  => $this->address_one,
            'address_two'  => $this->address_two,
            'address'      => $this->address,
            'postal_code'  => $this->postal_code,

            // Financial Information
            'tax_number'   => $this->tax_number,
            'commercial_register' => $this->commercial_register,
            'credit_limit' => $this->credit_limit,
            'payment_terms' => $this->payment_terms,
            'balance'      => $this->balance,
            'last_transaction_date' => $this->last_transaction_date,

            // Account Data
            'code_number'  => $this->code_number,
            'barcode_type_id' => $this->barcode_type_id,

            // Classification
            'classification' => $this->classification,
            'classification_display' => $this->classification_display,
            'custom_classification' => $this->custom_classification,

            // Additional Information
            'notes'        => $this->notes,

            // Status
            'status'       => $this->status,
            'active'       => $this->active,

            // Audit Fields
            'created_by'   => $this->created_by,
            'updated_by'   => $this->updated_by,
            'deleted_by'   => $this->deleted_by,

            // Relationships
            'user'         => new UserResource($this->whenLoaded('user')),
            'company'      => $this->whenLoaded('company'),
            'branch'       => $this->whenLoaded('branch'),
            'currency'     => $this->whenLoaded('currency'),
            'department'   => $this->whenLoaded('department'),
            'project'      => $this->whenLoaded('project'),
            'donor'        => $this->whenLoaded('donor'),
            'sales_representative' => $this->whenLoaded('salesRepresentative'),
            'country'      => $this->whenLoaded('country'),
            'region'       => $this->whenLoaded('region'),
            'city'         => $this->whenLoaded('city'),
            'barcode_type' => $this->whenLoaded('barcodeType'),
            'creator'      => new UserResource($this->whenLoaded('creator')),
            'updater'      => new UserResource($this->whenLoaded('updater')),
            'deleter'      => new UserResource($this->whenLoaded('deleter')),

            // Timestamps
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'deleted_at'   => $this->deleted_at,
        ];
    }
}
