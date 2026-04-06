<?php

use App\Actions\Batch\CreateBatchAction;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates batch successfully', function (): void {
    $product = Product::factory()->create();

    $data = [
        'batch_number' => 'BATCH-001',
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'quantity' => 100,
        'unit' => 'kg',
        'product_id' => $product->id,
    ];

    $action = new CreateBatchAction;
    $batch = $action->execute($data);

    expect($batch)->toBeInstanceOf(Batch::class);
    expect($batch->batch_number)->toBe('BATCH-001');
    expect($batch->quantity)->toBe(100.0);
    expect($batch->remaining_quantity)->toBe(100.0);
});

test('sets remaining quantity equal to quantity', function (): void {
    $product = Product::factory()->create();

    $data = [
        'batch_number' => 'BATCH-001',
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'quantity' => 250,
        'unit' => 'kg',
        'product_id' => $product->id,
    ];

    $action = new CreateBatchAction;
    $batch = $action->execute($data);

    expect($batch->remaining_quantity)->toBe(250.0);
});

test('persists batch to database', function (): void {
    $product = Product::factory()->create();

    $data = [
        'batch_number' => 'BATCH-001',
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'quantity' => 100,
        'unit' => 'kg',
        'product_id' => $product->id,
    ];

    $action = new CreateBatchAction;
    $action->execute($data);

    $this->assertDatabaseHas('batches', [
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
    ]);
});
