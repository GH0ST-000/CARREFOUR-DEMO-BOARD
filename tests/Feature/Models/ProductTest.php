<?php

use App\Models\Manufacturer;
use App\Models\Product;

test('product has correct fillable attributes', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Test Product',
        'sku' => 'SKU-001',
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
        'allergens' => ['milk'],
        'risk_indicators' => ['perishable'],
        'required_documents' => ['quality_certificate'],
        'manufacturer_id' => $manufacturer->id,
        'is_active' => true,
    ];

    $product = Product::create($data);

    expect($product->name)->toBe('Test Product');
    expect($product->sku)->toBe('SKU-001');
    expect($product->manufacturer_id)->toBe($manufacturer->id);
});

test('product belongs to manufacturer', function (): void {
    $manufacturer = Manufacturer::factory()->create(['full_name' => 'Test Manufacturer']);
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    expect($product->manufacturer)->toBeInstanceOf(Manufacturer::class);
    expect($product->manufacturer->full_name)->toBe('Test Manufacturer');
});

test('product casts attributes correctly', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create([
        'storage_temp_min' => 0,
        'storage_temp_max' => 4,
        'shelf_life_days' => 30,
        'allergens' => ['milk', 'eggs'],
        'risk_indicators' => ['perishable'],
        'required_documents' => ['quality_certificate'],
        'is_active' => 1,
        'manufacturer_id' => $manufacturer->id,
    ]);

    expect($product->storage_temp_min)->toBeFloat();
    expect($product->storage_temp_max)->toBeFloat();
    expect($product->shelf_life_days)->toBeInt();
    expect($product->allergens)->toBeArray();
    expect($product->risk_indicators)->toBeArray();
    expect($product->required_documents)->toBeArray();
    expect($product->is_active)->toBeBool();
    expect($product->is_active)->toBeTrue();
});

test('scopeActive filters active products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(3)->create(['is_active' => true, 'manufacturer_id' => $manufacturer->id]);
    Product::factory()->count(2)->create(['is_active' => false, 'manufacturer_id' => $manufacturer->id]);

    $activeProducts = Product::active()->get();

    expect($activeProducts)->toHaveCount(3);
    expect($activeProducts->every(fn ($p): bool => $p->is_active))->toBeTrue();
});

test('scopeOrdered returns products in correct order', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->create(['name' => 'Zebra Product', 'manufacturer_id' => $manufacturer->id]);
    Product::factory()->create(['name' => 'Apple Product', 'manufacturer_id' => $manufacturer->id]);
    Product::factory()->create(['name' => 'Mango Product', 'manufacturer_id' => $manufacturer->id]);

    $products = Product::ordered()->get();

    expect($products[0]->name)->toBe('Apple Product');
    expect($products[1]->name)->toBe('Mango Product');
    expect($products[2]->name)->toBe('Zebra Product');
});

test('scopeLocal filters local products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(3)->create(['origin_type' => 'local', 'manufacturer_id' => $manufacturer->id]);
    Product::factory()->count(2)->create(['origin_type' => 'imported', 'manufacturer_id' => $manufacturer->id]);

    $localProducts = Product::local()->get();

    expect($localProducts)->toHaveCount(3);
    expect($localProducts->every(fn ($p): bool => $p->origin_type === 'local'))->toBeTrue();
});

test('scopeImported filters imported products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(3)->create(['origin_type' => 'local', 'manufacturer_id' => $manufacturer->id]);
    Product::factory()->count(2)->create(['origin_type' => 'imported', 'manufacturer_id' => $manufacturer->id]);

    $importedProducts = Product::imported()->get();

    expect($importedProducts)->toHaveCount(2);
    expect($importedProducts->every(fn ($p): bool => $p->origin_type === 'imported'))->toBeTrue();
});

test('isLocal returns true for local products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['origin_type' => 'local', 'manufacturer_id' => $manufacturer->id]);

    expect($product->isLocal())->toBeTrue();
    expect($product->isImported())->toBeFalse();
});

test('isImported returns true for imported products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['origin_type' => 'imported', 'manufacturer_id' => $manufacturer->id]);

    expect($product->isImported())->toBeTrue();
    expect($product->isLocal())->toBeFalse();
});

test('hasTemperatureRequirement returns true when temp range is set', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $product1 = Product::factory()->create([
        'storage_temp_min' => 0.0,
        'storage_temp_max' => 4.0,
        'manufacturer_id' => $manufacturer->id,
    ]);

    $product2 = Product::factory()->create([
        'storage_temp_min' => null,
        'storage_temp_max' => null,
        'manufacturer_id' => $manufacturer->id,
    ]);

    expect($product1->hasTemperatureRequirement())->toBeTrue();
    expect($product2->hasTemperatureRequirement())->toBeFalse();
});

test('isTemperatureInRange validates temperature correctly', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $product = Product::factory()->create([
        'storage_temp_min' => 0.0,
        'storage_temp_max' => 4.0,
        'manufacturer_id' => $manufacturer->id,
    ]);

    expect($product->isTemperatureInRange(2.0))->toBeTrue();
    expect($product->isTemperatureInRange(0.0))->toBeTrue();
    expect($product->isTemperatureInRange(4.0))->toBeTrue();
    expect($product->isTemperatureInRange(-1.0))->toBeFalse();
    expect($product->isTemperatureInRange(5.0))->toBeFalse();
});

test('isTemperatureInRange returns true for products without temperature requirement', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $product = Product::factory()->create([
        'storage_temp_min' => null,
        'storage_temp_max' => null,
        'manufacturer_id' => $manufacturer->id,
    ]);

    expect($product->isTemperatureInRange(25.0))->toBeTrue();
    expect($product->isTemperatureInRange(-20.0))->toBeTrue();
});

test('product can be created with factory', function (): void {
    $product = Product::factory()->create();

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->exists)->toBeTrue();
    expect($product->manufacturer)->toBeInstanceOf(Manufacturer::class);
});

test('product factory can create local products', function (): void {
    $product = Product::factory()->local()->create();

    expect($product->origin_type)->toBe('local');
    expect($product->country_of_origin)->toBe('Georgia');
});

test('product factory can create imported products', function (): void {
    $product = Product::factory()->imported()->create();

    expect($product->origin_type)->toBe('imported');
});

test('product factory can create temperature controlled products', function (): void {
    $product = Product::factory()->temperatureControlled()->create();

    expect($product->storage_temp_min)->toBe(0.0);
    expect($product->storage_temp_max)->toBe(4.0);
    expect($product->category)->toBe('Dairy');
});

test('product factory can create frozen products', function (): void {
    $product = Product::factory()->frozen()->create();

    expect($product->storage_temp_min)->toBe(-18.0);
    expect($product->storage_temp_max)->toBe(-15.0);
    expect($product->category)->toBe('Frozen');
});
