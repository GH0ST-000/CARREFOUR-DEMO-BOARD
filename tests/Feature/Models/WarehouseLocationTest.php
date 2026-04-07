<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('has fillable attributes', function (): void {
    $location = new WarehouseLocation;

    expect($location->getFillable())->toContain(
        'name',
        'code',
        'type',
        'parent_id',
        'temp_min',
        'temp_max',
        'responsible_user_id',
        'is_active'
    );
});

test('has many children', function (): void {
    $parent = WarehouseLocation::factory()->create();
    $child1 = WarehouseLocation::factory()->create(['parent_id' => $parent->id]);
    $child2 = WarehouseLocation::factory()->create(['parent_id' => $parent->id]);

    expect($parent->children)->toHaveCount(2);
    expect($parent->children->first()->id)->toBe($child1->id);
});

test('belongs to parent', function (): void {
    $parent = WarehouseLocation::factory()->create();
    $child = WarehouseLocation::factory()->create(['parent_id' => $parent->id]);

    expect($child->parent)->toBeInstanceOf(WarehouseLocation::class);
    expect($child->parent->id)->toBe($parent->id);
});

test('belongs to responsible user', function (): void {
    $user = User::factory()->create();
    $location = WarehouseLocation::factory()->create(['responsible_user_id' => $user->id]);

    expect($location->responsibleUser)->toBeInstanceOf(User::class);
    expect($location->responsibleUser->id)->toBe($user->id);
});

test('scope active returns only active locations', function (): void {
    WarehouseLocation::factory()->active()->create();
    WarehouseLocation::factory()->create(['is_active' => false]);
    WarehouseLocation::factory()->active()->create();

    $active = WarehouseLocation::active()->get();

    expect($active)->toHaveCount(2);
    expect($active->every(fn ($loc) => $loc->is_active))->toBeTrue();
});

test('scope ordered returns locations by name', function (): void {
    $locationC = WarehouseLocation::factory()->create(['name' => 'Warehouse C']);
    $locationA = WarehouseLocation::factory()->create(['name' => 'Warehouse A']);
    $locationB = WarehouseLocation::factory()->create(['name' => 'Warehouse B']);

    $ordered = WarehouseLocation::ordered()->get();

    expect($ordered->first()->id)->toBe($locationA->id);
    expect($ordered->last()->id)->toBe($locationC->id);
});

test('scope roots returns only top-level locations', function (): void {
    $parent1 = WarehouseLocation::factory()->create(['parent_id' => null]);
    $parent2 = WarehouseLocation::factory()->create(['parent_id' => null]);
    WarehouseLocation::factory()->create(['parent_id' => $parent1->id]);
    WarehouseLocation::factory()->create(['parent_id' => $parent2->id]);

    $roots = WarehouseLocation::roots()->get();

    expect($roots)->toHaveCount(2);
    expect($roots->pluck('id')->toArray())->toContain($parent1->id, $parent2->id);
});

test('has temperature control returns true when temperatures are set', function (): void {
    $location = WarehouseLocation::factory()->create([
        'temp_min' => 0,
        'temp_max' => 4,
    ]);

    expect($location->hasTemperatureControl())->toBeTrue();
});

test('has temperature control returns false when temperatures are null', function (): void {
    $location = WarehouseLocation::factory()->create([
        'temp_min' => null,
        'temp_max' => null,
    ]);

    expect($location->hasTemperatureControl())->toBeFalse();
});

test('is temperature in range returns true for valid temperature', function (): void {
    $location = WarehouseLocation::factory()->create([
        'temp_min' => 0,
        'temp_max' => 4,
    ]);

    expect($location->isTemperatureInRange(2))->toBeTrue();
    expect($location->isTemperatureInRange(0))->toBeTrue();
    expect($location->isTemperatureInRange(4))->toBeTrue();
});

test('is temperature in range returns false for invalid temperature', function (): void {
    $location = WarehouseLocation::factory()->create([
        'temp_min' => 0,
        'temp_max' => 4,
    ]);

    expect($location->isTemperatureInRange(-1))->toBeFalse();
    expect($location->isTemperatureInRange(5))->toBeFalse();
});

test('is temperature in range returns true when no temperature control', function (): void {
    $location = WarehouseLocation::factory()->create([
        'temp_min' => null,
        'temp_max' => null,
    ]);

    expect($location->isTemperatureInRange(25))->toBeTrue();
});

test('casts boolean fields correctly', function (): void {
    $location = WarehouseLocation::factory()->create([
        'has_sensor' => true,
        'is_active' => false,
    ]);

    expect($location->has_sensor)->toBeBool();
    expect($location->is_active)->toBeBool();
    expect($location->has_sensor)->toBeTrue();
    expect($location->is_active)->toBeFalse();
});

test('casts numeric fields correctly', function (): void {
    $location = WarehouseLocation::factory()->create([
        'temp_min' => 0,
        'temp_max' => 4,
        'inspection_frequency_hours' => 8,
    ]);

    expect($location->temp_min)->toBeFloat();
    expect($location->temp_max)->toBeFloat();
    expect($location->inspection_frequency_hours)->toBeInt();
});

test('can create hierarchical structure with multiple levels', function (): void {
    $central = WarehouseLocation::factory()->centralWarehouse()->create();
    $regional = WarehouseLocation::factory()->regionalWarehouse()->create([
        'parent_id' => $central->id,
    ]);
    $zone = WarehouseLocation::factory()->zone()->create([
        'parent_id' => $regional->id,
    ]);

    expect($central->children)->toHaveCount(1);
    expect($regional->parent->id)->toBe($central->id);
    expect($regional->children)->toHaveCount(1);
    expect($zone->parent->id)->toBe($regional->id);
});
