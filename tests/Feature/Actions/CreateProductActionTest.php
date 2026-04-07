<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductAction;
use App\Models\Manufacturer;
use App\Models\Product;

test('can create product with all fields', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Test Product',
        'sku' => 'SKU-TEST-001',
        'barcode' => '1234567890123',
        'qr_code' => 'QR-001',
        'brand' => 'Test Brand',
        'category' => 'Dairy',
        'unit' => 'kg',
        'origin_type' => 'local',
        'country_of_origin' => 'Georgia',
        'storage_temp_min' => 0.0,
        'storage_temp_max' => 4.0,
        'shelf_life_days' => 30,
        'inventory_policy' => 'fefo',
        'allergens' => ['milk', 'eggs'],
        'risk_indicators' => ['perishable'],
        'required_documents' => ['quality_certificate'],
        'manufacturer_id' => $manufacturer->id,
        'is_active' => true,
    ];

    $action = app(CreateProductAction::class);
    $product = $action->execute($data);

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('Test Product');
    expect($product->sku)->toBe('SKU-TEST-001');
    expect($product->manufacturer_id)->toBe($manufacturer->id);
    expect($product->exists)->toBeTrue();
});

test('can create product with minimal required fields', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Minimal Product',
        'sku' => 'SKU-MIN-001',
        'category' => 'Beverages',
        'unit' => 'l',
        'origin_type' => 'local',
        'country_of_origin' => 'Georgia',
        'shelf_life_days' => 365,
        'inventory_policy' => 'fifo',
        'manufacturer_id' => $manufacturer->id,
    ];

    $action = app(CreateProductAction::class);
    $product = $action->execute($data);

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('Minimal Product');
    expect($product->barcode)->toBeNull();
    expect($product->storage_temp_min)->toBeNull();
});

test('creates product with correct data types', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Type Test Product',
        'sku' => 'SKU-TYPE-001',
        'category' => 'Meat',
        'unit' => 'kg',
        'origin_type' => 'imported',
        'country_of_origin' => 'Turkey',
        'storage_temp_min' => -18.5,
        'storage_temp_max' => -15.0,
        'shelf_life_days' => 180,
        'inventory_policy' => 'fefo',
        'allergens' => ['soy'],
        'manufacturer_id' => $manufacturer->id,
        'is_active' => false,
    ];

    $action = app(CreateProductAction::class);
    $product = $action->execute($data);

    expect($product->storage_temp_min)->toBeFloat();
    expect($product->storage_temp_max)->toBeFloat();
    expect($product->shelf_life_days)->toBeInt();
    expect($product->allergens)->toBeArray();
    expect($product->is_active)->toBeBool();
    expect($product->is_active)->toBeFalse();
});
