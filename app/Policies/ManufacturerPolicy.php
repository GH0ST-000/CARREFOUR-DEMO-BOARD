<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Manufacturer;
use App\Models\User;

final class ManufacturerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_manufacturer', 'web');
    }

    public function view(User $user, Manufacturer $manufacturer): bool
    {
        return $user->hasPermissionTo('view_manufacturer', 'web');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_manufacturer', 'web');
    }

    public function update(User $user, Manufacturer $manufacturer): bool
    {
        return $user->hasPermissionTo('update_manufacturer', 'web');
    }

    public function delete(User $user, Manufacturer $manufacturer): bool
    {
        return $user->hasPermissionTo('delete_manufacturer', 'web');
    }

    public function restore(User $user, Manufacturer $manufacturer): bool
    {
        return $user->hasPermissionTo('restore_manufacturer', 'web');
    }

    public function forceDelete(User $user, Manufacturer $manufacturer): bool
    {
        return $user->hasPermissionTo('force_delete_manufacturer', 'web');
    }
}
