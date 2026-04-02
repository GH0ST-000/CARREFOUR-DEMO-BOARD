<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read User $resource
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'branch_id' => $this->resource->branch_id,
            'roles' => $this->resource->roles->pluck('name'),
            'permissions' => $this->resource->getAllPermissions()->pluck('name'),
        ];
    }
}
