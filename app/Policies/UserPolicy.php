<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_any_user', 'view_own_branch_user']);
    }

    public function view(User $user, User $model): bool
    {
        if ($user->hasPermissionTo('view_user')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_branch_user')) {
            return $user->branch_id === $model->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_user');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasPermissionTo('update_user')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('delete_user');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('restore_user');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('force_delete_user');
    }
}
