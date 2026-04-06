<?php

namespace App\Http\Resources;

use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WarehouseLocation
 */
class WarehouseLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['parent', 'responsibleUser']);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? [
                'id' => $this->parent->id,
                'name' => $this->parent->name,
                'code' => $this->parent->code,
                'type' => $this->parent->type,
            ] : null),
            'temp_min' => $this->temp_min,
            'temp_max' => $this->temp_max,
            'responsible_user_id' => $this->responsible_user_id,
            'responsible_user' => $this->whenLoaded('responsibleUser', fn () => $this->responsibleUser ? [
                'id' => $this->responsibleUser->id,
                'name' => $this->responsibleUser->name,
                'email' => $this->responsibleUser->email,
            ] : null),
            'inspection_frequency_hours' => $this->inspection_frequency_hours,
            'description' => $this->description,
            'address' => $this->address,
            'has_sensor' => $this->has_sensor,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
