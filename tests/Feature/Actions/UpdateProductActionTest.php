<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductAction;
use App\Models\Manufacturer;
use App\Models\Product;

test('can update product fields', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    $data = [
        'name' => 'Updated Product Name',
        'is_active' => false,
    ];

    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->execute($product, $data);

    expect($updatedProduct->name)->toBe('Updated Product Name');
    expect($updatedProduct->is_active)->toBeFalse();
});

test('can update single field', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'manufacturer_id' => $manufacturer->id,
    ]);

    $data = ['name' => 'New Name'];

    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->execute($product, $data);

    expect($updatedProduct->name)->toBe('New Name');
});

test('returns fresh model instance', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    $data = ['name' => 'Fresh Test'];

    $action = app(UpdateProductAction::class);
    $result = $action->execute($product, $data);

    expect($result)->toBeInstanceOf(Product::class);
    expect($result->name)->toBe('Fresh Test');
    expect($result->wasRecentlyCreated)->toBeFalse();
});

test('can update temperature range', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create([
        'storage_temp_min' => 0.0,
        'storage_temp_max' => 4.0,
        'manufacturer_id' => $manufacturer->id,
    ]);

    $data = [
        'storage_temp_min' => -18.0,
        'storage_temp_max' => -15.0,
    ];

    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->execute($product, $data);

    expect($updatedProduct->storage_temp_min)->toBe(-18.0);
    expect($updatedProduct->storage_temp_max)->toBe(-15.0);
});

test('can update allergens and risk indicators', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create([
        'allergens' => ['milk'],
        'risk_indicators' => ['perishable'],
        'manufacturer_id' => $manufacturer->id,
    ]);

    $data = [
        'allergens' => ['milk', 'eggs', 'nuts'],
        'risk_indicators' => ['perishable', 'fragile'],
    ];

    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->execute($product, $data);

    expect($updatedProduct->allergens)->toBe(['milk', 'eggs', 'nuts']);
    expect($updatedProduct->risk_indicators)->toBe(['perishable', 'fragile']);
});
