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
            'user_id'      => new UserResource($this->whenLoaded('user')),
            'company_id'   => $this->company_id,
            'branch_id'    => $this->branch_id,
            'currency_id'  => $this->currency_id,
            'employee_id'  => $this->employee_id,
            'country_id'   => $this->country_id,
            'region_id'    => $this->region_id,
            'city_id'      => $this->city_id,

            'first_name'   => $this->first_name,
            'second_name'  => $this->second_name,
            'contact_name' => $this->contact_name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'mobile'       => $this->mobile,
            'address_one'  => $this->address_one,
            'address_two'  => $this->address_two,
            'postal_code'  => $this->postal_code,
            'tax_number'   => $this->tax_number,
            'notes'        => $this->notes,

            'created_by'   => $this->created_by,
            'updated_by'   => $this->updated_by,
            'deleted_by'   => $this->deleted_by,
            'status'       => $this->status,

            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'deleted_at'   => $this->deleted_at,
        ];
    }
}
