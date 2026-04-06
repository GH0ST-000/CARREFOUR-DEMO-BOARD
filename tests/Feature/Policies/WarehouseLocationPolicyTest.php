<?php

use App\Models\User;
use App\Models\WarehouseLocation;
use App\Policies\WarehouseLocationPolicy;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->policy = new WarehouseLocationPolicy;
    $this->location = WarehouseLocation::factory()->create();
});

test('recall admin can view any warehouse location', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('recall admin can view warehouse location', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->view($user, $this->location))->toBeTrue();
});

test('recall admin can create warehouse location', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->create($user))->toBeTrue();
});

test('recall admin can update warehouse location', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->update($user, $this->location))->toBeTrue();
});

test('recall admin can delete warehouse location', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->delete($user, $this->location))->toBeTrue();
});

test('recall admin can restore warehouse location', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->restore($user, $this->location))->toBeTrue();
});

test('recall admin can force delete warehouse location', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->forceDelete($user, $this->location))->toBeTrue();
});

test('user without permission cannot view any warehouse location', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('user without permission cannot create warehouse location', function (): void {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});
