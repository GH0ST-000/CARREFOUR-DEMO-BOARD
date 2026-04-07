<?php

declare(strict_types=1);

use App\Actions\WarehouseLocation\UpdateWarehouseLocationAction;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('updates warehouse location successfully', function (): void {
    $location = WarehouseLocation::factory()->create([
        'name' => 'Old Name',
        'is_active' => true,
    ]);

    $data = [
        'name' => 'New Name',
        'is_active' => false,
    ];

    $action = app(UpdateWarehouseLocationAction::class);
    $updated = $action->execute($location, $data);

    expect($updated->name)->toBe('New Name');
    expect($updated->is_active)->toBeFalse();
});

test('persists changes to database', function (): void {
    $location = WarehouseLocation::factory()->create([
        'name' => 'Warehouse A',
    ]);

    $data = [
        'name' => 'Warehouse B',
    ];

    $action = app(UpdateWarehouseLocationAction::class);
    $action->execute($location, $data);

    $this->assertDatabaseHas('warehouse_locations', [
        'id' => $location->id,
        'name' => 'Warehouse B',
    ]);
});
