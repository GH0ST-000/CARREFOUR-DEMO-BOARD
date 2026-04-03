<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\AssignRoleToUserAction;
use App\Actions\User\RemoveRoleFromUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssignRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    public function __construct(
        private readonly AssignRoleToUserAction $assignRoleAction,
        private readonly RemoveRoleFromUserAction $removeRoleAction,
    ) {}

    public function store(User $user, AssignRoleRequest $request): UserResource
    {
        $this->authorize('update', $user);

        /** @var array{role: string} $validated */
        $validated = $request->validated();
        $updatedUser = $this->assignRoleAction->execute($user, $validated['role']);

        return new UserResource($updatedUser);
    }

    public function destroy(User $user, string $role): JsonResponse
    {
        $this->authorize('update', $user);

        $this->removeRoleAction->execute($user, $role);

        return response()->json([
            'message' => 'Role removed successfully',
        ]);
    }
}
