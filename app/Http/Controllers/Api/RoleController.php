<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->get();

        return RoleResource::collection($roles);
    }

    public function show(Role $role): RoleResource
    {
        $this->authorize('view', $role);

        $role->load('permissions');

        return new RoleResource($role);
    }
}
