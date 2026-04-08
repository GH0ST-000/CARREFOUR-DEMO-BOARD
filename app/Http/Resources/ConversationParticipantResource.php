<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ConversationParticipant
 */
final class ConversationParticipantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user_id,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
        ];
    }
}
