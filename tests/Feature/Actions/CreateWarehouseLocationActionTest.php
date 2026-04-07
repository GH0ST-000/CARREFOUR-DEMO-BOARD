<?php

declare(strict_types=1);

use App\Actions\WarehouseLocation\CreateWarehouseLocationAction;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates warehouse location successfully', function (): void {
    $data = [
        'name' => 'Central Warehouse',
        'code' => 'CW-001',
        'type' => 'central_warehouse',
        'is_active' => true,
    ];

    $action = app(CreateWarehouseLocationAction::class);
    $location = $action->execute($data);

    expect($location)->toBeInstanceOf(WarehouseLocation::class);
    expect($location->name)->toBe('Central Warehouse');
    expect($location->code)->toBe('CW-001');
});

test('persists location to database', function (): void {
    $data = [
        'name' => 'Cold Storage',
        'code' => 'CS-001',
        'type' => 'storage_unit',
        'temp_min' => 0,
        'temp_max' => 4,
    ];

    $action = app(CreateWarehouseLocationAction::class);
    $action->execute($data);

    $this->assertDatabaseHas('warehouse_locations', [
        'name' => 'Cold Storage',
        'code' => 'CS-001',
    ]);
});
