<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_any_user', 'view_own_branch_user'], 'web');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->hasPermissionTo('view_user', 'web')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_branch_user', 'web')) {
            return $user->branch_id === $model->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_user', 'web');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasPermissionTo('update_user', 'web')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('delete_user', 'web');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('restore_user', 'web');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('force_delete_user', 'web');
    }
}
