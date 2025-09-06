<?php

namespace Modules\Companies\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'landline' => $this->landline,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'logo' => $this->logo,
            'tax_number' => $this->tax_number,
            'timezone' => $this->timezone,
            'status' => $this->status,

            'user' => $this->whenLoaded('user'),
            'company' => $this->whenLoaded('company'),
            'currency' => $this->whenLoaded('currency'),
            'manager' => $this->whenLoaded('manager'),
            'financial_year' => $this->whenLoaded('financialYear'),
            'country' => $this->whenLoaded('country'),
            'region' => $this->whenLoaded('region'),
            'city' => $this->whenLoaded('city'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
