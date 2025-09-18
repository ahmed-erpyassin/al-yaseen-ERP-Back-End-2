<?php

namespace Modules\Companies\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'name_en' => $this->name_en,
            'phone_code' => $this->phone_code,
            'currency_code' => $this->currency_code,
            'timezone' => $this->timezone,
            'regions' => RegionResource::collection($this->whenLoaded('regions')),
        ];
    }
}
