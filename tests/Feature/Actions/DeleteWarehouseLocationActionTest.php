<?php

declare(strict_types=1);

use App\Actions\WarehouseLocation\DeleteWarehouseLocationAction;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deletes warehouse location successfully', function (): void {
    $location = WarehouseLocation::factory()->create();

    $action = app(DeleteWarehouseLocationAction::class);
    $result = $action->execute($location);

    expect($result)->toBeTrue();
});

test('removes location from database', function (): void {
    $location = WarehouseLocation::factory()->create();
    $locationId = $location->id;

    $action = app(DeleteWarehouseLocationAction::class);
    $action->execute($location);

    $this->assertDatabaseMissing('warehouse_locations', [
        'id' => $locationId,
    ]);
});
