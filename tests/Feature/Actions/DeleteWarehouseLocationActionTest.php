<?php

use App\Actions\WarehouseLocation\DeleteWarehouseLocationAction;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deletes warehouse location successfully', function (): void {
    $location = WarehouseLocation::factory()->create();

    $action = new DeleteWarehouseLocationAction;
    $result = $action->execute($location);

    expect($result)->toBeTrue();
});

test('removes location from database', function (): void {
    $location = WarehouseLocation::factory()->create();
    $locationId = $location->id;

    $action = new DeleteWarehouseLocationAction;
    $action->execute($location);

    $this->assertDatabaseMissing('warehouse_locations', [
        'id' => $locationId,
    ]);
});
