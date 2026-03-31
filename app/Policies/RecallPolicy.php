<?php

namespace App\Policies;

use App\Models\Recall;
use App\Models\User;

class RecallPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_any_recall', 'view_own_branch_recall']);
    }

    public function view(User $user, Recall $recall): bool
    {
        if ($user->hasPermissionTo('view_recall')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_branch_recall')) {
            return $user->branch_id === $recall->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_recall');
    }

    public function update(User $user, Recall $recall): bool
    {
        if ($user->hasPermissionTo('update_recall')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_branch_recall')) {
            return $user->branch_id === $recall->branch_id;
        }

        return false;
    }

    public function delete(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('delete_recall');
    }

    public function restore(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('restore_recall');
    }

    public function forceDelete(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('force_delete_recall');
    }

    public function approve(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('approve_recall');
    }
}
