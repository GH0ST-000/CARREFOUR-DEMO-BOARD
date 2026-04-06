<?php

use App\Models\Receiving;
use App\Models\User;
use App\Policies\ReceivingPolicy;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->policy = new ReceivingPolicy;
});

test('user with view_any_receiving permission can view any', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('view_any_receiving');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('user without view_any_receiving permission cannot view any', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('user with view_receiving permission can view', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('view_receiving');
    $receiving = Receiving::factory()->create();

    expect($this->policy->view($user, $receiving))->toBeTrue();
});

test('user without view_receiving permission cannot view', function (): void {
    $user = User::factory()->create();
    $receiving = Receiving::factory()->create();

    expect($this->policy->view($user, $receiving))->toBeFalse();
});

test('user with create_receiving permission can create', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('create_receiving');

    expect($this->policy->create($user))->toBeTrue();
});

test('user without create_receiving permission cannot create', function (): void {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});

test('user with update_receiving permission can update', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('update_receiving');
    $receiving = Receiving::factory()->create();

    expect($this->policy->update($user, $receiving))->toBeTrue();
});

test('user without update_receiving permission cannot update', function (): void {
    $user = User::factory()->create();
    $receiving = Receiving::factory()->create();

    expect($this->policy->update($user, $receiving))->toBeFalse();
});

test('user with delete_receiving permission can delete', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_receiving');
    $receiving = Receiving::factory()->create();

    expect($this->policy->delete($user, $receiving))->toBeTrue();
});

test('user without delete_receiving permission cannot delete', function (): void {
    $user = User::factory()->create();
    $receiving = Receiving::factory()->create();

    expect($this->policy->delete($user, $receiving))->toBeFalse();
});

test('user with restore_receiving permission can restore', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('restore_receiving');
    $receiving = Receiving::factory()->create();

    expect($this->policy->restore($user, $receiving))->toBeTrue();
});

test('user without restore_receiving permission cannot restore', function (): void {
    $user = User::factory()->create();
    $receiving = Receiving::factory()->create();

    expect($this->policy->restore($user, $receiving))->toBeFalse();
});

test('user with force_delete_receiving permission can force delete', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('force_delete_receiving');
    $receiving = Receiving::factory()->create();

    expect($this->policy->forceDelete($user, $receiving))->toBeTrue();
});

test('user without force_delete_receiving permission cannot force delete', function (): void {
    $user = User::factory()->create();
    $receiving = Receiving::factory()->create();

    expect($this->policy->forceDelete($user, $receiving))->toBeFalse();
});
