<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
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

test('can list batches', function (): void {
    Batch::factory()->count(3)->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/batches');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'batch_number',
                    'production_date',
                    'expiry_date',
                    'quantity',
                    'remaining_quantity',
                    'status',
                    'product',
                ],
            ],
        ])
        ->assertJsonCount(3, 'data');
});

test('can create batch', function (): void {
    $product = Product::factory()->create();

    $data = [
        'batch_number' => 'BATCH-001',
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'quantity' => 100.50,
        'unit' => 'kg',
        'product_id' => $product->id,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', $data);

    $response->assertCreated()
        ->assertJsonPath('data.batch_number', 'BATCH-001')
        ->assertJsonPath('data.quantity', 100.5)
        ->assertJsonPath('data.remaining_quantity', 100.5);

    $this->assertDatabaseHas('batches', [
        'batch_number' => 'BATCH-001',
        'quantity' => 100.5,
        'remaining_quantity' => 100.5,
    ]);
});

test('can view single batch', function (): void {
    $batch = Batch::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/batches/{$batch->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $batch->id)
        ->assertJsonPath('data.batch_number', $batch->batch_number);
});

test('can update batch', function (): void {
    $batch = Batch::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/batches/{$batch->id}", [
            'status' => 'blocked',
            'notes' => 'Quality issue detected',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'blocked')
        ->assertJsonPath('data.notes', 'Quality issue detected');
});

test('can delete batch', function (): void {
    $batch = Batch::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/batches/{$batch->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('batches', [
        'id' => $batch->id,
    ]);
});

test('validates required fields when creating batch', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'batch_number',
            'production_date',
            'expiry_date',
            'quantity',
            'unit',
            'product_id',
        ]);
});

test('validates unique batch number', function (): void {
    $existing = Batch::factory()->create(['batch_number' => 'BATCH-001']);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', [
            'batch_number' => 'BATCH-001',
            'production_date' => '2026-01-01',
            'expiry_date' => '2026-12-31',
            'quantity' => 100,
            'unit' => 'kg',
            'product_id' => Product::factory()->create()->id,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['batch_number']);
});

test('validates expiry date is after production date', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', [
            'batch_number' => 'BATCH-001',
            'production_date' => '2026-12-31',
            'expiry_date' => '2026-01-01',
            'quantity' => 100,
            'unit' => 'kg',
            'product_id' => Product::factory()->create()->id,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['expiry_date']);
});

test('paginates batches with cursor', function (): void {
    Batch::factory()->count(30)->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/batches?per_page=10');

    $response->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data',
            'meta' => ['next_cursor'],
        ]);
});

test('unauthorized user cannot access batches', function (): void {
    $unauthorized = User::factory()->create();

    $response = $this->actingAs($unauthorized, 'api')
        ->getJson('/api/batches');

    $response->assertForbidden();
});

test('trims string fields when creating batch', function (): void {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', [
            'batch_number' => '  BATCH-001  ',
            'unit' => '  kg  ',
            'production_date' => '2026-01-01',
            'expiry_date' => '2026-12-31',
            'quantity' => 100,
            'product_id' => $product->id,
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('batches', [
        'batch_number' => 'BATCH-001',
        'unit' => 'kg',
    ]);
});

test('can create local batch', function (): void {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', [
            'batch_number' => 'BATCH-LOCAL-001',
            'local_production_number' => 'LOC-123456',
            'production_date' => '2026-01-01',
            'expiry_date' => '2026-12-31',
            'quantity' => 100,
            'unit' => 'kg',
            'product_id' => $product->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.local_production_number', 'LOC-123456');
});

test('can create imported batch', function (): void {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', [
            'batch_number' => 'BATCH-IMP-001',
            'import_declaration_number' => 'IMP-987654',
            'production_date' => '2026-01-01',
            'expiry_date' => '2026-12-31',
            'quantity' => 100,
            'unit' => 'kg',
            'product_id' => $product->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.import_declaration_number', 'IMP-987654');
});

test('can update remaining quantity', function (): void {
    $batch = Batch::factory()->create([
        'quantity' => 100,
        'remaining_quantity' => 100,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/batches/{$batch->id}", [
            'remaining_quantity' => 75.5,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.remaining_quantity', 75.5);
});

test('validates remaining quantity cannot exceed total quantity', function (): void {
    $batch = Batch::factory()->create([
        'quantity' => 100,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/batches/{$batch->id}", [
            'remaining_quantity' => 150,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['remaining_quantity']);
});

test('can assign batch to warehouse location', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/batches/{$batch->id}", [
            'warehouse_location_id' => $location->id,
            'status' => 'in_storage',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.warehouse_location_id', $location->id)
        ->assertJsonPath('data.status', 'in_storage');
});

test('trims nullable fields in create batch request', function (): void {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', [
            'batch_number' => '  BATCH-001  ',
            'import_declaration_number' => '  IMP-123  ',
            'local_production_number' => '  LOC-456  ',
            'packaging_condition' => '  Good condition  ',
            'notes' => '  Test notes  ',
            'production_date' => '2026-01-01',
            'expiry_date' => '2026-12-31',
            'quantity' => 100,
            'unit' => 'kg',
            'product_id' => $product->id,
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('batches', [
        'batch_number' => 'BATCH-001',
        'import_declaration_number' => 'IMP-123',
        'local_production_number' => 'LOC-456',
        'packaging_condition' => 'Good condition',
        'notes' => 'Test notes',
    ]);
});

test('trims nullable fields to null in create batch request', function (): void {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/batches', [
            'batch_number' => 'BATCH-001',
            'import_declaration_number' => '   ',
            'packaging_condition' => '',
            'production_date' => '2026-01-01',
            'expiry_date' => '2026-12-31',
            'quantity' => 100,
            'unit' => 'kg',
            'product_id' => $product->id,
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('batches', [
        'batch_number' => 'BATCH-001',
        'import_declaration_number' => null,
        'packaging_condition' => null,
    ]);
});

test('trims nullable fields in update batch request', function (): void {
    $batch = Batch::factory()->create();

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/batches/{$batch->id}", [
            'import_declaration_number' => '  IMP-UPDATED  ',
            'local_production_number' => '  LOC-UPDATED  ',
            'packaging_condition' => '  Updated condition  ',
            'notes' => '  Updated notes  ',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('batches', [
        'id' => $batch->id,
        'import_declaration_number' => 'IMP-UPDATED',
        'local_production_number' => 'LOC-UPDATED',
        'packaging_condition' => 'Updated condition',
        'notes' => 'Updated notes',
    ]);
});

test('can update batch with nullable fields set to null', function (): void {
    $batch = Batch::factory()->create([
        'import_declaration_number' => 'IMP-123',
        'packaging_condition' => 'Good',
        'notes' => 'Test',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/batches/{$batch->id}", [
            'import_declaration_number' => null,
            'packaging_condition' => null,
            'notes' => null,
        ]);

    $response->assertOk();

    $batch->refresh();
    expect($batch->import_declaration_number)->toBeNull();
    expect($batch->packaging_condition)->toBeNull();
    expect($batch->notes)->toBeNull();
});

test('can update batch number and unit', function (): void {
    $batch = Batch::factory()->create([
        'batch_number' => 'OLD-001',
        'unit' => 'kg',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/batches/{$batch->id}", [
            'batch_number' => '  NEW-001  ',
            'unit' => '  l  ',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('batches', [
        'id' => $batch->id,
        'batch_number' => 'NEW-001',
        'unit' => 'l',
    ]);
});

test('loads product and warehouse location relationships', function (): void {
    $product = Product::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $batch = Batch::factory()->create([
        'product_id' => $product->id,
        'warehouse_location_id' => $location->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/batches/{$batch->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'product' => ['id', 'name', 'sku'],
                'warehouse_location' => ['id', 'name', 'code'],
            ],
        ]);
});
