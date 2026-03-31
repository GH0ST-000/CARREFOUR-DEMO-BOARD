<?php

use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(BranchSeeder::class);
});

test('recall admin has full access to all permissions', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Recall Admin');

    expect($admin->hasRole('Recall Admin'))->toBeTrue()
        ->and($admin->hasPermissionTo('view_any_user'))->toBeTrue()
        ->and($admin->hasPermissionTo('create_user'))->toBeTrue()
        ->and($admin->hasPermissionTo('approve_recall'))->toBeTrue();
});

test('quality manager can approve actions', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole('Quality Manager');

    expect($manager->hasPermissionTo('approve_recall'))->toBeTrue()
        ->and($manager->hasPermissionTo('view_any_recall'))->toBeTrue()
        ->and($manager->hasPermissionTo('create_recall'))->toBeTrue();
});

test('branch manager cannot approve actions', function (): void {
    $branchManager = User::factory()->create();
    $branchManager->assignRole('Branch Manager');

    expect($branchManager->hasPermissionTo('approve_recall'))->toBeFalse();
});

test('warehouse operator has limited editing permissions', function (): void {
    $operator = User::factory()->create();
    $operator->assignRole('Warehouse Operator');

    expect($operator->hasPermissionTo('create_inventory'))->toBeTrue()
        ->and($operator->hasPermissionTo('update_inventory'))->toBeTrue()
        ->and($operator->hasPermissionTo('approve_recall'))->toBeFalse();
});

test('auditor can only view and evaluate', function (): void {
    $auditor = User::factory()->create();
    $auditor->assignRole('Auditor');

    expect($auditor->hasPermissionTo('view_any_audit'))->toBeTrue()
        ->and($auditor->hasPermissionTo('view_any_recall'))->toBeTrue()
        ->and($auditor->hasPermissionTo('approve_recall'))->toBeFalse()
        ->and($auditor->hasPermissionTo('delete_recall'))->toBeFalse();
});

test('roles can be assigned to users', function (): void {
    $user = User::factory()->create();
    $role = Role::findByName('Quality Manager');

    $user->assignRole($role);

    expect($user->hasRole('Quality Manager'))->toBeTrue()
        ->and($user->roles)->toHaveCount(1);
});
