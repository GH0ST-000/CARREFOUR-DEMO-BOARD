<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WarehouseLocation;

class WarehouseLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_warehouse_location');
    }

    public function view(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->hasPermissionTo('view_warehouse_location');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_warehouse_location');
    }

    public function update(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->hasPermissionTo('update_warehouse_location');
    }

    public function delete(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->hasPermissionTo('delete_warehouse_location');
    }

    public function restore(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->hasPermissionTo('restore_warehouse_location');
    }

    public function forceDelete(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->hasPermissionTo('force_delete_warehouse_location');
    }
}
