<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force_delete',
            'approve',
            'view_own_branch',
        ];

        $resources = [
            'user',
            'role',
            'branch',
            'product',
            'batch',
            'warehouse_location',
            'receiving',
            'inventory',
            'recall',
            'audit',
            'manufacturer',
            'ticket',
            'action_log',
        ];

        foreach ($resources as $resource) {
            foreach ($permissions as $permission) {
                Permission::create(['name' => "{$permission}_{$resource}"]);
            }
        }

        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $recallAdmin = Role::create(['name' => 'Recall Admin']);
        $recallAdmin->givePermissionTo(Permission::all());

        $qualityManager = Role::create(['name' => 'Quality Manager']);
        $qualityManager->givePermissionTo([
            'view_any_user', 'view_user',
            'view_any_branch', 'view_branch',
            'view_any_product', 'view_product', 'create_product', 'update_product', 'delete_product',
            'view_any_inventory', 'view_inventory', 'create_inventory', 'update_inventory', 'delete_inventory',
            'view_any_recall', 'view_recall', 'create_recall', 'update_recall', 'delete_recall', 'approve_recall',
            'view_any_audit', 'view_audit', 'create_audit', 'update_audit', 'approve_audit',
        ]);

        $branchManager = Role::create(['name' => 'Branch Manager']);
        $branchManager->givePermissionTo([
            'view_own_branch_user',
            'view_own_branch_branch',
            'view_own_branch_product',
            'view_own_branch_inventory', 'create_inventory', 'update_inventory',
            'view_own_branch_recall', 'create_recall',
            'view_own_branch_audit',
        ]);

        $warehouseOperator = Role::create(['name' => 'Warehouse Operator']);
        $warehouseOperator->givePermissionTo([
            'view_inventory', 'create_inventory', 'update_inventory',
            'view_product',
            'create_recall',
            'view_recall',
        ]);

        $auditor = Role::create(['name' => 'Auditor']);
        $auditor->givePermissionTo([
            'view_any_audit', 'view_audit', 'create_audit', 'update_audit',
            'view_any_recall', 'view_recall',
            'view_any_inventory', 'view_inventory',
            'view_any_product', 'view_product',
        ]);
    }
}
