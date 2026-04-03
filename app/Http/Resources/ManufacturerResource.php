<?php

namespace App\Http\Resources;

use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Manufacturer $resource
 */
class ManufacturerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'full_name' => $this->resource->full_name,
            'short_name' => $this->resource->short_name,
            'legal_form' => $this->resource->legal_form,
            'identification_number' => $this->resource->identification_number,
            'legal_address' => $this->resource->legal_address,
            'phone' => $this->resource->phone,
            'email' => $this->resource->email,
            'country' => $this->resource->country,
            'region' => $this->resource->region,
            'city' => $this->resource->city,
            'is_active' => $this->resource->is_active,
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }
}
