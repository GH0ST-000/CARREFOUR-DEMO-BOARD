<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use App\Models\WarehouseLocation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('has fillable attributes', function (): void {
    $batch = new Batch;

    expect($batch->getFillable())->toContain(
        'batch_number',
        'import_declaration_number',
        'local_production_number',
        'production_date',
        'expiry_date',
        'quantity',
        'status',
        'product_id'
    );
});

test('casts dates correctly', function (): void {
    $batch = Batch::factory()->create([
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
    ]);

    expect($batch->production_date)->toBeInstanceOf(Carbon::class);
    expect($batch->expiry_date)->toBeInstanceOf(Carbon::class);
});

test('belongs to product', function (): void {
    $product = Product::factory()->create();
    $batch = Batch::factory()->create(['product_id' => $product->id]);

    expect($batch->product)->toBeInstanceOf(Product::class);
    expect($batch->product->id)->toBe($product->id);
});

test('belongs to warehouse location', function (): void {
    $location = WarehouseLocation::factory()->create();
    $batch = Batch::factory()->create(['warehouse_location_id' => $location->id]);

    expect($batch->warehouseLocation)->toBeInstanceOf(WarehouseLocation::class);
    expect($batch->warehouseLocation->id)->toBe($location->id);
});

test('belongs to received by user', function (): void {
    $user = User::factory()->create();
    $batch = Batch::factory()->create(['received_by_user_id' => $user->id]);

    expect($batch->receivedBy)->toBeInstanceOf(User::class);
    expect($batch->receivedBy->id)->toBe($user->id);
});

test('scope ordered returns batches by expiry date', function (): void {
    $batch1 = Batch::factory()->create(['expiry_date' => '2026-12-31']);
    $batch2 = Batch::factory()->create(['expiry_date' => '2026-06-30']);
    $batch3 = Batch::factory()->create(['expiry_date' => '2026-09-15']);

    $ordered = Batch::ordered()->get();

    expect($ordered->first()->id)->toBe($batch2->id);
    expect($ordered->last()->id)->toBe($batch1->id);
});

test('scope active returns only active batches', function (): void {
    Batch::factory()->create(['status' => 'received']);
    Batch::factory()->create(['status' => 'in_storage']);
    Batch::factory()->create(['status' => 'blocked']);
    Batch::factory()->create(['status' => 'expired']);

    $active = Batch::active()->get();

    expect($active)->toHaveCount(2);
    expect($active->pluck('status')->toArray())->toContain('received', 'in_storage');
});

test('scope expired returns only expired batches', function (): void {
    Batch::factory()->create(['expiry_date' => now()->subDays(5)]);
    Batch::factory()->create(['expiry_date' => now()->addDays(5)]);
    Batch::factory()->create(['expiry_date' => now()->subDay()]);

    $expired = Batch::expired()->get();

    expect($expired)->toHaveCount(2);
});

test('scope expiring within days returns batches expiring soon', function (): void {
    Batch::factory()->create(['expiry_date' => now()->addDays(3)]);
    Batch::factory()->create(['expiry_date' => now()->addDays(5)]);
    Batch::factory()->create(['expiry_date' => now()->addDays(10)]);
    Batch::factory()->create(['expiry_date' => now()->subDays(1)]);

    $expiringSoon = Batch::expiringWithinDays(7)->get();

    expect($expiringSoon)->toHaveCount(2);
});

test('scope blocked returns only blocked batches', function (): void {
    Batch::factory()->create(['status' => 'blocked']);
    Batch::factory()->create(['status' => 'received']);
    Batch::factory()->create(['status' => 'blocked']);

    $blocked = Batch::blocked()->get();

    expect($blocked)->toHaveCount(2);
});

test('scope recalled returns only recalled batches', function (): void {
    Batch::factory()->create(['status' => 'recalled']);
    Batch::factory()->create(['status' => 'received']);
    Batch::factory()->create(['status' => 'recalled']);

    $recalled = Batch::recalled()->get();

    expect($recalled)->toHaveCount(2);
});

test('is expired returns true for expired batch', function (): void {
    $batch = Batch::factory()->create(['expiry_date' => now()->subDay()]);

    expect($batch->isExpired())->toBeTrue();
});

test('is expired returns false for non-expired batch', function (): void {
    $batch = Batch::factory()->create(['expiry_date' => now()->addDay()]);

    expect($batch->isExpired())->toBeFalse();
});

test('days until expiry calculates correctly', function (): void {
    $batch = Batch::factory()->create(['expiry_date' => now()->addDays(10)]);

    expect($batch->daysUntilExpiry())->toBe(10);
});

test('days until expiry returns zero for expired batch', function (): void {
    $batch = Batch::factory()->create(['expiry_date' => now()->subDays(5)]);

    expect($batch->daysUntilExpiry())->toBe(0);
});

test('is fully consumed returns true when remaining quantity is zero', function (): void {
    $batch = Batch::factory()->create(['remaining_quantity' => 0]);

    expect($batch->isFullyConsumed())->toBeTrue();
});

test('has quantity available returns true when remaining quantity is positive', function (): void {
    $batch = Batch::factory()->create(['remaining_quantity' => 50]);

    expect($batch->hasQuantityAvailable())->toBeTrue();
});

test('has quantity available returns false when remaining quantity is zero', function (): void {
    $batch = Batch::factory()->create(['remaining_quantity' => 0]);

    expect($batch->hasQuantityAvailable())->toBeFalse();
});

test('is local returns true for local batch', function (): void {
    $batch = Batch::factory()->local()->create();

    expect($batch->isLocal())->toBeTrue();
    expect($batch->isImported())->toBeFalse();
});

test('is imported returns true for imported batch', function (): void {
    $batch = Batch::factory()->imported()->create();

    expect($batch->isImported())->toBeTrue();
    expect($batch->isLocal())->toBeFalse();
});

test('casts json fields correctly', function (): void {
    $batch = Batch::factory()->create([
        'linked_documents' => ['doc1.pdf', 'doc2.pdf'],
        'temperature_history' => [
            ['timestamp' => now(), 'temp' => 2.5],
        ],
    ]);

    expect($batch->linked_documents)->toBeArray();
    expect($batch->temperature_history)->toBeArray();
});
