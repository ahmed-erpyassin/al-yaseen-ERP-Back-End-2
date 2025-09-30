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
            'id' => $this->id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'currency_id' => $this->currency_id,
            'employee_id' => $this->employee_id,
            'country_id' => $this->country_id,
            'region_id' => $this->region_id,
            'city_id' => $this->city_id,
            'first_name' => $this->first_name,
            'second_name' => $this->second_name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address_one' => $this->address_one,
            'address_two' => $this->address_two,
            'postal_code' => $this->postal_code,
            'tax_number' => $this->tax_number,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'updated_by' => new UserResource($this->whenLoaded('updater')),
            'deleted_by' => new UserResource($this->whenLoaded('deleter')),
            'is_deleted' => !is_null($this->deleted_at),
            'full_name' => $this->full_name,
            'company' => $this->whenLoaded('company'),
            'branch' => $this->whenLoaded('branch'),
            'currency' => $this->whenLoaded('currency'),
            'employee' => $this->whenLoaded('employee'),
            'country' => $this->whenLoaded('country'),
            'region' => $this->whenLoaded('region'),
            'city' => $this->whenLoaded('city'),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
