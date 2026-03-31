<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_any_branch', 'view_own_branch_branch']);
    }

    public function view(User $user, Branch $branch): bool
    {
        if ($user->hasPermissionTo('view_branch')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_branch_branch')) {
            return $user->branch_id === $branch->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_branch');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('update_branch');
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('delete_branch');
    }

    public function restore(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('restore_branch');
    }

    public function forceDelete(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('force_delete_branch');
    }
}
