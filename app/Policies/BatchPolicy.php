<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_batch');
    }

    public function view(User $user, Batch $batch): bool
    {
        return $user->hasPermissionTo('view_batch');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_batch');
    }

    public function update(User $user, Batch $batch): bool
    {
        return $user->hasPermissionTo('update_batch');
    }

    public function delete(User $user, Batch $batch): bool
    {
        return $user->hasPermissionTo('delete_batch');
    }

    public function restore(User $user, Batch $batch): bool
    {
        return $user->hasPermissionTo('restore_batch');
    }

    public function forceDelete(User $user, Batch $batch): bool
    {
        return $user->hasPermissionTo('force_delete_batch');
    }
}
