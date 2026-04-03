<?php

namespace App\Actions\User;

use App\Models\User;

class AssignRoleToUserAction
{
    public function execute(User $user, string $roleName): User
    {
        $user->assignRole($roleName);
        $user->load('roles');

        return $user;
    }
}
