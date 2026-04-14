<?php

declare(strict_types=1);

use App\Actions\Batch\CreateBatchAction;
use App\Actions\Batch\UpdateBatchAction;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('CreateBatchAction calculates expiry when production_date is Carbon', function (): void {
    $product = Product::factory()->create(['shelf_life_days' => 10]);

    /** @var CreateBatchAction $action */
    $action = app(CreateBatchAction::class);

    $batch = $action->execute([
        'batch_number' => 'BATCH-CARBON-001',
        'production_date' => Carbon::parse('2026-01-01'),
        'expiry_date' => null,
        'quantity' => 10,
        'unit' => 'kg',
        'status' => 'received',
        'product_id' => $product->id,
    ]);

    expect($batch->expiry_date->toDateString())->toBe('2026-01-11');
});

test('UpdateBatchAction ignores null expiry_date and recalculates using Carbon production_date', function (): void {
    $product = Product::factory()->create(['shelf_life_days' => 5]);

    $batch = Batch::factory()->create([
        'product_id' => $product->id,
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-01-20',
    ]);

    /** @var UpdateBatchAction $action */
    $action = app(UpdateBatchAction::class);

    $updated = $action->execute($batch, [
        'production_date' => Carbon::parse('2026-01-02'),
        'expiry_date' => null,
    ]);

    $updated->refresh();

    expect($updated->expiry_date->toDateString())->toBe('2026-01-07');
});

test('UpdateBatchAction recalculates expiry when product_id changes without explicit expiry_date', function (): void {
    $product1 = Product::factory()->create(['shelf_life_days' => 10]);
    $product2 = Product::factory()->create(['shelf_life_days' => 2]);

    $batch = Batch::factory()->create([
        'product_id' => $product1->id,
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-01-11',
    ]);

    /** @var UpdateBatchAction $action */
    $action = app(UpdateBatchAction::class);

    $updated = $action->execute($batch, [
        'product_id' => $product2->id,
    ]);

    $updated->refresh();

    expect($updated->expiry_date->toDateString())->toBe('2026-01-03');
});

test('UpdateBatchAction parses production_date string when provided', function (): void {
    $product = Product::factory()->create(['shelf_life_days' => 3]);

    $batch = Batch::factory()->create([
        'product_id' => $product->id,
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-01-10',
    ]);

    /** @var UpdateBatchAction $action */
    $action = app(UpdateBatchAction::class);

    $updated = $action->execute($batch, [
        'production_date' => '2026-01-05',
    ]);

    $updated->refresh();

    expect($updated->expiry_date->toDateString())->toBe('2026-01-08');
});
