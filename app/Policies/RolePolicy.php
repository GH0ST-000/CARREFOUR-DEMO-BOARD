<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_role', 'web');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('view_role', 'web');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_role', 'web');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('update_role', 'web');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('delete_role', 'web');
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('restore_role', 'web');
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('force_delete_role', 'web');
    }
}
