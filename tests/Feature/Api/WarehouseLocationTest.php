<?php

use App\Models\User;
use App\Models\WarehouseLocation;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Recall Admin');
});

test('can list warehouse locations', function (): void {
    WarehouseLocation::factory()->count(3)->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/warehouse-locations');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'code',
                    'type',
                    'is_active',
                ],
            ],
        ])
        ->assertJsonCount(3, 'data');
});

test('can create warehouse location', function (): void {
    $data = [
        'name' => 'Central Warehouse',
        'code' => 'CW-001',
        'type' => 'central_warehouse',
        'is_active' => true,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', $data);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Central Warehouse')
        ->assertJsonPath('data.code', 'CW-001')
        ->assertJsonPath('data.type', 'central_warehouse');

    $this->assertDatabaseHas('warehouse_locations', [
        'name' => 'Central Warehouse',
        'code' => 'CW-001',
    ]);
});

test('can view single warehouse location', function (): void {
    $location = WarehouseLocation::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/warehouse-locations/{$location->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $location->id)
        ->assertJsonPath('data.name', $location->name);
});

test('can update warehouse location', function (): void {
    $location = WarehouseLocation::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/warehouse-locations/{$location->id}", [
            'name' => 'Updated Warehouse',
            'is_active' => false,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Warehouse')
        ->assertJsonPath('data.is_active', false);
});

test('can delete warehouse location', function (): void {
    $location = WarehouseLocation::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/warehouse-locations/{$location->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('warehouse_locations', [
        'id' => $location->id,
    ]);
});

test('validates required fields when creating location', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'code',
            'type',
        ]);
});

test('validates unique code', function (): void {
    WarehouseLocation::factory()->create(['code' => 'WH-001']);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'New Warehouse',
            'code' => 'WH-001',
            'type' => 'central_warehouse',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);
});

test('validates type is valid', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Test Warehouse',
            'code' => 'WH-001',
            'type' => 'invalid_type',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

test('can create temperature controlled location', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Cold Storage',
            'code' => 'CS-001',
            'type' => 'storage_unit',
            'temp_min' => 0,
            'temp_max' => 4,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.temp_min', 0)
        ->assertJsonPath('data.temp_max', 4);
});

test('validates temp_max is greater than or equal to temp_min', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Cold Storage',
            'code' => 'CS-001',
            'type' => 'storage_unit',
            'temp_min' => 10,
            'temp_max' => 5,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['temp_max']);
});

test('can create hierarchical location with parent', function (): void {
    $parent = WarehouseLocation::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Sub Zone',
            'code' => 'SZ-001',
            'type' => 'zone',
            'parent_id' => $parent->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.parent_id', $parent->id);

    $this->assertDatabaseHas('warehouse_locations', [
        'name' => 'Sub Zone',
        'parent_id' => $parent->id,
    ]);
});

test('validates parent location exists', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Sub Zone',
            'code' => 'SZ-001',
            'type' => 'zone',
            'parent_id' => 99999,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);
});

test('loads parent relationship', function (): void {
    $parent = WarehouseLocation::factory()->create();
    $child = WarehouseLocation::factory()->create(['parent_id' => $parent->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/warehouse-locations/{$child->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'parent' => ['id', 'name', 'code'],
            ],
        ])
        ->assertJsonPath('data.parent.id', $parent->id);
});

test('can assign responsible user', function (): void {
    $responsibleUser = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Warehouse A',
            'code' => 'WH-A',
            'type' => 'regional_warehouse',
            'responsible_user_id' => $responsibleUser->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.responsible_user_id', $responsibleUser->id);
});

test('loads responsible user relationship', function (): void {
    $responsibleUser = User::factory()->create();
    $location = WarehouseLocation::factory()->create([
        'responsible_user_id' => $responsibleUser->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/warehouse-locations/{$location->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'responsible_user' => ['id', 'name', 'email'],
            ],
        ])
        ->assertJsonPath('data.responsible_user.id', $responsibleUser->id);
});

test('trims string fields', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => '  Test Warehouse  ',
            'code' => '  WH-001  ',
            'type' => 'central_warehouse',
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('warehouse_locations', [
        'name' => 'Test Warehouse',
        'code' => 'WH-001',
    ]);
});

test('paginates locations with cursor', function (): void {
    WarehouseLocation::factory()->count(30)->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/warehouse-locations?per_page=10');

    $response->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data',
            'meta' => ['next_cursor'],
        ]);
});

test('unauthorized user cannot access warehouse locations', function (): void {
    $unauthorized = User::factory()->create();

    $response = $this->actingAs($unauthorized, 'api')
        ->getJson('/api/warehouse-locations');

    $response->assertForbidden();
});

test('can set inspection frequency', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Cold Storage',
            'code' => 'CS-001',
            'type' => 'storage_unit',
            'inspection_frequency_hours' => 8,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.inspection_frequency_hours', 8);
});

test('validates inspection frequency is within valid range', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Warehouse',
            'code' => 'WH-001',
            'type' => 'central_warehouse',
            'inspection_frequency_hours' => 200,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['inspection_frequency_hours']);
});

test('can mark location with sensor', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Smart Warehouse',
            'code' => 'SW-001',
            'type' => 'central_warehouse',
            'has_sensor' => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.has_sensor', true);
});

test('trims nullable fields in create location request', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => '  Warehouse  ',
            'code' => '  WH-001  ',
            'type' => 'central_warehouse',
            'description' => '  Test description  ',
            'address' => '  123 Main St  ',
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('warehouse_locations', [
        'name' => 'Warehouse',
        'code' => 'WH-001',
        'description' => 'Test description',
        'address' => '123 Main St',
    ]);
});

test('trims nullable fields to null in create location request', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/warehouse-locations', [
            'name' => 'Warehouse',
            'code' => 'WH-001',
            'type' => 'central_warehouse',
            'description' => '   ',
            'address' => '',
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('warehouse_locations', [
        'name' => 'Warehouse',
        'code' => 'WH-001',
        'description' => null,
        'address' => null,
    ]);
});

test('trims nullable fields in update location request', function (): void {
    $location = WarehouseLocation::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/warehouse-locations/{$location->id}", [
            'description' => '  Updated description  ',
            'address' => '  456 New St  ',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('warehouse_locations', [
        'id' => $location->id,
        'description' => 'Updated description',
        'address' => '456 New St',
    ]);
});

test('can update location with nullable fields set to null', function (): void {
    $location = WarehouseLocation::factory()->create([
        'description' => 'Test',
        'address' => '123 Main St',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/warehouse-locations/{$location->id}", [
            'description' => null,
            'address' => null,
        ]);

    $response->assertOk();

    $location->refresh();
    expect($location->description)->toBeNull();
    expect($location->address)->toBeNull();
});

test('can update warehouse location name and code', function (): void {
    $location = WarehouseLocation::factory()->create([
        'name' => 'Old Name',
        'code' => 'OLD-001',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/warehouse-locations/{$location->id}", [
            'name' => '  New Name  ',
            'code' => '  NEW-001  ',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('warehouse_locations', [
        'id' => $location->id,
        'name' => 'New Name',
        'code' => 'NEW-001',
    ]);
});
