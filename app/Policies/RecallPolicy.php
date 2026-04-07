<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Recall;
use App\Models\User;

final class RecallPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_any_recall', 'view_own_branch_recall'], 'web');
    }

    public function view(User $user, Recall $recall): bool
    {
        if ($user->hasPermissionTo('view_recall', 'web')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_branch_recall', 'web')) {
            return $user->branch_id === $recall->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_recall', 'web');
    }

    public function update(User $user, Recall $recall): bool
    {
        if ($user->hasPermissionTo('update_recall', 'web')) {
            return true;
        }

        if ($user->hasPermissionTo('view_own_branch_recall', 'web')) {
            return $user->branch_id === $recall->branch_id;
        }

        return false;
    }

    public function delete(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('delete_recall', 'web');
    }

    public function restore(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('restore_recall', 'web');
    }

    public function forceDelete(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('force_delete_recall', 'web');
    }

    public function approve(User $user, Recall $recall): bool
    {
        return $user->hasPermissionTo('approve_recall', 'web');
    }
}
