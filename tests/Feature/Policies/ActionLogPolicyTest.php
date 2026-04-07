<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\ActionLogPolicy;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->policy = new ActionLogPolicy;
    $this->user = User::factory()->create();
});

test('viewAny returns true when user has view_any_action_log permission', function (): void {
    $this->user->givePermissionTo('view_any_action_log');

    expect($this->policy->viewAny($this->user))->toBeTrue();
});

test('viewAny returns false when user lacks permission', function (): void {
    expect($this->policy->viewAny($this->user))->toBeFalse();
});

test('view returns true when user has view_action_log permission', function (): void {
    $this->user->givePermissionTo('view_action_log');

    expect($this->policy->view($this->user))->toBeTrue();
});

test('view returns false when user lacks permission', function (): void {
    expect($this->policy->view($this->user))->toBeFalse();
});
