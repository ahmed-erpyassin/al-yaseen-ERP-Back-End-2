<?php

namespace Modules\Companies\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'country_id' => $this->country_id,
            'cities' => CityResource::collection($this->whenLoaded('cities')),
            'country' => new CountryResource($this->whenLoaded('country')),
        ];
    }
}
