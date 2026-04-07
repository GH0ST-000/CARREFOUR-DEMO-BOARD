<?php

declare(strict_types=1);

use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->policy = new ProductPolicy;
    $this->user = User::factory()->create();
    $this->manufacturer = Manufacturer::factory()->create();
    $this->product = Product::factory()->create(['manufacturer_id' => $this->manufacturer->id]);
});

test('viewAny returns true when user has permission', function (): void {
    $this->user->givePermissionTo('view_any_product');

    expect($this->policy->viewAny($this->user))->toBeTrue();
});

test('viewAny returns false when user lacks permission', function (): void {
    expect($this->policy->viewAny($this->user))->toBeFalse();
});

test('view returns true when user has permission', function (): void {
    $this->user->givePermissionTo('view_product');

    expect($this->policy->view($this->user, $this->product))->toBeTrue();
});

test('view returns false when user lacks permission', function (): void {
    expect($this->policy->view($this->user, $this->product))->toBeFalse();
});

test('create returns true when user has permission', function (): void {
    $this->user->givePermissionTo('create_product');

    expect($this->policy->create($this->user))->toBeTrue();
});

test('create returns false when user lacks permission', function (): void {
    expect($this->policy->create($this->user))->toBeFalse();
});

test('update returns true when user has permission', function (): void {
    $this->user->givePermissionTo('update_product');

    expect($this->policy->update($this->user, $this->product))->toBeTrue();
});

test('update returns false when user lacks permission', function (): void {
    expect($this->policy->update($this->user, $this->product))->toBeFalse();
});

test('delete returns true when user has permission', function (): void {
    $this->user->givePermissionTo('delete_product');

    expect($this->policy->delete($this->user, $this->product))->toBeTrue();
});

test('delete returns false when user lacks permission', function (): void {
    expect($this->policy->delete($this->user, $this->product))->toBeFalse();
});

test('restore returns true when user has permission', function (): void {
    $this->user->givePermissionTo('restore_product');

    expect($this->policy->restore($this->user, $this->product))->toBeTrue();
});

test('restore returns false when user lacks permission', function (): void {
    expect($this->policy->restore($this->user, $this->product))->toBeFalse();
});

test('forceDelete returns true when user has permission', function (): void {
    $this->user->givePermissionTo('force_delete_product');

    expect($this->policy->forceDelete($this->user, $this->product))->toBeTrue();
});

test('forceDelete returns false when user lacks permission', function (): void {
    expect($this->policy->forceDelete($this->user, $this->product))->toBeFalse();
});
