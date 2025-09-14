<?php

namespace Modules\Companies\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'commercial_registeration_number' => $this->commercial_registeration_number,
            'address' => $this->address,
            'logo' => $this->logo,
            'email' => $this->email,
            'landline' => $this->landline,
            'mobile' => $this->mobile,
            'income_tax_rate' => $this->income_tax_rate,
            'vat_rate' => $this->vat_rate,
            'status' => $this->status,

            // العلاقات
            'currency' => $this->whenLoaded('currency'),
            'industry' => $this->whenLoaded('industry'),
            'business_type' => $this->whenLoaded('businessType'),
            'country' => $this->whenLoaded('country'),
            'region' => $this->whenLoaded('region'),
            'city' => $this->whenLoaded('city'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
