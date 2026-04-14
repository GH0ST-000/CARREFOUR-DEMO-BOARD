<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
final class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'qr_code' => $this->qr_code,
            'brand' => $this->brand,
            'category' => $this->category,
            'unit' => $this->unit,
            'origin_type' => $this->origin_type,
            'country_of_origin' => $this->country_of_origin,
            'storage_temp_min' => $this->storage_temp_min,
            'storage_temp_max' => $this->storage_temp_max,
            'production_date' => $this->production_date?->toDateString(),
            'shelf_life_days' => $this->shelf_life_days,
            'expiration_date' => $this->expiration_date?->toDateString(),
            'inventory_policy' => $this->inventory_policy,
            'allergens' => $this->allergens,
            'risk_indicators' => $this->risk_indicators,
            'required_documents' => $this->required_documents,
            'manufacturer_id' => $this->manufacturer_id,
            'manufacturer' => new ManufacturerResource($this->whenLoaded('manufacturer')),
            'documents' => DocumentResource::collection(
                $this->relationLoaded('documents') ? $this->documents : collect(),
            ),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
