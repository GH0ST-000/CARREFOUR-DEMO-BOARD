<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProductTraceabilityEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductTraceabilityEvent
 */
final class ProductTraceabilityEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['fromBranch', 'toBranch', 'responsibleUser', 'createdBy', 'batch']);

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'batch_id' => $this->batch_id,
            'from_branch_id' => $this->from_branch_id,
            'from_branch' => $this->whenLoaded('fromBranch', fn (): ?array => $this->fromBranch ? [
                'id' => $this->fromBranch->id,
                'name' => $this->fromBranch->name,
                'code' => $this->fromBranch->code,
            ] : null),
            'to_branch_id' => $this->to_branch_id,
            'to_branch' => $this->whenLoaded('toBranch', fn (): array => [
                'id' => $this->toBranch->id,
                'name' => $this->toBranch->name,
                'code' => $this->toBranch->code,
            ]),
            'quantity' => $this->quantity,
            'transferred_at' => $this->transferred_at->toIso8601String(),
            'responsible_user_id' => $this->responsible_user_id,
            'responsible_user' => $this->whenLoaded('responsibleUser', fn (): ?array => $this->responsibleUser ? [
                'id' => $this->responsibleUser->id,
                'name' => $this->responsibleUser->name,
                'email' => $this->responsibleUser->email,
            ] : null),
            'created_by_user_id' => $this->created_by_user_id,
            'created_by' => $this->whenLoaded('createdBy', fn (): ?array => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email,
            ] : null),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
