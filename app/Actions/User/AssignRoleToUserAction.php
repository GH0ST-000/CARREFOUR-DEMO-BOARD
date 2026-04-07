<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use App\Services\ActionLogService;

final readonly class AssignRoleToUserAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(User $user, string $roleName): User
    {
        $user->assignRole($roleName);
        $user->load('roles');

        $this->actionLogService->log(
            actionType: 'update',
            entityType: 'user',
            entityId: $user->id,
            module: 'users',
            description: "Role '{$roleName}' assigned to User #{$user->id}",
            metadata: ['role' => $roleName],
        );

        return $user;
    }
}
