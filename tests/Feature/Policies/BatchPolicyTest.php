<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\User;
use App\Policies\BatchPolicy;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->policy = new BatchPolicy;
    $this->batch = Batch::factory()->create();
});

test('recall admin can view any batch', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('recall admin can view batch', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->view($user, $this->batch))->toBeTrue();
});

test('recall admin can create batch', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->create($user))->toBeTrue();
});

test('recall admin can update batch', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->update($user, $this->batch))->toBeTrue();
});

test('recall admin can delete batch', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->delete($user, $this->batch))->toBeTrue();
});

test('recall admin can restore batch', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->restore($user, $this->batch))->toBeTrue();
});

test('recall admin can force delete batch', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Recall Admin');

    expect($this->policy->forceDelete($user, $this->batch))->toBeTrue();
});

test('user without permission cannot view any batch', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('user without permission cannot create batch', function (): void {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});
