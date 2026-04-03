<?php

namespace App\Actions\User;

use App\Models\User;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class RemoveRoleFromUserAction
{
    public function execute(User $user, string $roleName): User
    {
        try {
            $role = Role::findByName($roleName, 'web');
            $user->removeRole($role);
        } catch (RoleDoesNotExist) {
        }

        $user->load('roles');

        return $user;
    }
}
