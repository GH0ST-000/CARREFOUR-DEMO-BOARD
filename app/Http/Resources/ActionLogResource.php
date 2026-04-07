<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ActionLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ActionLog
 */
final class ActionLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'action_type' => $this->action_type,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'module' => $this->module,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
