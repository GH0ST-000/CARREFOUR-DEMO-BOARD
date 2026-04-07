<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Receiving;
use App\Models\User;
use App\Models\WarehouseLocation;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
        'view_any_receiving',
        'view_receiving',
        'create_receiving',
        'update_receiving',
        'delete_receiving',
    ]);
});

test('can list receivings with pagination', function (): void {
    Receiving::factory()->count(30)->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/receivings');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'receipt_number',
                    'receipt_datetime',
                    'batch_id',
                    'warehouse_location_id',
                    'received_quantity',
                    'unit',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta',
            'links',
        ]);
});

test('can create receiving', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $data = [
        'batch_id' => $batch->id,
        'warehouse_location_id' => $location->id,
        'received_by_user_id' => $receivedBy->id,
        'receipt_datetime' => now()->toDateTimeString(),
        'received_quantity' => 100.50,
        'unit' => 'kg',
        'receipt_number' => 'RCP-001',
        'recorded_temperature' => 2.5,
        'temperature_compliant' => true,
        'packaging_condition' => 'good',
        'documents_verified' => true,
        'status' => 'accepted',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', $data);

    $response->assertCreated()
        ->assertJsonFragment([
            'receipt_number' => 'RCP-001',
            'received_quantity' => 100.5,
            'unit' => 'kg',
            'status' => 'accepted',
        ]);

    $this->assertDatabaseHas('receivings', [
        'receipt_number' => 'RCP-001',
        'batch_id' => $batch->id,
    ]);
});

test('can view receiving', function (): void {
    $receiving = Receiving::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/receivings/{$receiving->id}");

    $response->assertOk()
        ->assertJsonFragment([
            'id' => $receiving->id,
            'receipt_number' => $receiving->receipt_number,
        ]);
});

test('can update receiving', function (): void {
    $receiving = Receiving::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/receivings/{$receiving->id}", [
            'status' => 'accepted',
            'documents_verified' => true,
        ]);

    $response->assertOk()
        ->assertJsonFragment([
            'status' => 'accepted',
            'documents_verified' => true,
        ]);

    $this->assertDatabaseHas('receivings', [
        'id' => $receiving->id,
        'status' => 'accepted',
    ]);
});

test('can delete receiving', function (): void {
    $receiving = Receiving::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/receivings/{$receiving->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Receiving deleted successfully']);

    $this->assertDatabaseMissing('receivings', [
        'id' => $receiving->id,
    ]);
});

test('validates required fields on create', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'batch_id',
            'warehouse_location_id',
            'received_by_user_id',
            'receipt_datetime',
            'received_quantity',
            'unit',
        ]);
});

test('validates batch_id exists', function (): void {
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => 99999,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['batch_id']);
});

test('validates warehouse_location_id exists', function (): void {
    $batch = Batch::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => 99999,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['warehouse_location_id']);
});

test('validates received_quantity minimum value', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 0,
            'unit' => 'kg',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['received_quantity']);
});

test('validates status enum values', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
            'status' => 'invalid_status',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('validates packaging_condition enum values', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
            'packaging_condition' => 'invalid_condition',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['packaging_condition']);
});

test('validates temperature range', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
            'recorded_temperature' => 100,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['recorded_temperature']);
});

test('requires view_any_receiving permission to list', function (): void {
    $this->user->revokePermissionTo('view_any_receiving');

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/receivings');

    $response->assertForbidden();
});

test('requires create_receiving permission to create', function (): void {
    $this->user->revokePermissionTo('create_receiving');
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
        ]);

    $response->assertForbidden();
});

test('requires update_receiving permission to update', function (): void {
    $this->user->revokePermissionTo('update_receiving');
    $receiving = Receiving::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/receivings/{$receiving->id}", [
            'status' => 'accepted',
        ]);

    $response->assertForbidden();
});

test('requires delete_receiving permission to delete', function (): void {
    $this->user->revokePermissionTo('delete_receiving');
    $receiving = Receiving::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/receivings/{$receiving->id}");

    $response->assertForbidden();
});

test('trims nullable text fields in create', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
            'receipt_number' => '  RCP-001  ',
            'notes' => '  Some notes  ',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('receivings', [
        'receipt_number' => 'RCP-001',
        'notes' => 'Some notes',
    ]);
});

test('converts empty strings to null in nullable fields', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/receivings', [
            'batch_id' => $batch->id,
            'warehouse_location_id' => $location->id,
            'received_by_user_id' => $receivedBy->id,
            'receipt_datetime' => now()->toDateTimeString(),
            'received_quantity' => 100,
            'unit' => 'kg',
            'receipt_number' => '',
            'notes' => '   ',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('receivings', [
        'receipt_number' => null,
        'notes' => null,
    ]);
});

test('can update status to rejected with reason', function (): void {
    $receiving = Receiving::factory()->pending()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/receivings/{$receiving->id}", [
            'status' => 'rejected',
            'rejection_reason' => 'Temperature not compliant',
        ]);

    $response->assertOk()
        ->assertJsonFragment([
            'status' => 'rejected',
            'rejection_reason' => 'Temperature not compliant',
        ]);
});

test('can update status to quarantined', function (): void {
    $receiving = Receiving::factory()->pending()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/receivings/{$receiving->id}", [
            'status' => 'quarantined',
        ]);

    $response->assertOk()
        ->assertJsonFragment([
            'status' => 'quarantined',
        ]);
});

test('can add photos to receiving', function (): void {
    $receiving = Receiving::factory()->create(['photos' => null]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/receivings/{$receiving->id}", [
            'photos' => ['photo1.jpg', 'photo2.jpg', 'photo3.jpg'],
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('receivings', [
        'id' => $receiving->id,
    ]);

    $updated = $receiving->fresh();
    expect($updated->photos)->toBeArray()->toHaveCount(3);
});
