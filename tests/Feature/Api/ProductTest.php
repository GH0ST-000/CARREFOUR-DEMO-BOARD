<?php

declare(strict_types=1);

use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
        'view_any_product',
        'view_product',
        'create_product',
        'update_product',
        'delete_product',
    ]);
});

test('can list products with cursor pagination', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(30)->create(['manufacturer_id' => $manufacturer->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/products?per_page=10');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'sku',
                    'barcode',
                    'qr_code',
                    'brand',
                    'category',
                    'unit',
                    'origin_type',
                    'country_of_origin',
                    'storage_temp_min',
                    'storage_temp_max',
                    'shelf_life_days',
                    'inventory_policy',
                    'allergens',
                    'risk_indicators',
                    'required_documents',
                    'manufacturer_id',
                    'manufacturer',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ])
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 10)
            ->where('meta.per_page', 10)
            ->etc()
        );
});

test('can create product with all required fields', function (): void {
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
        'storage_temp_min' => 0,
        'storage_temp_max' => 4,
        'shelf_life_days' => 30,
        'inventory_policy' => 'fefo',
        'allergens' => ['milk', 'eggs'],
        'risk_indicators' => ['perishable', 'temperature_sensitive'],
        'required_documents' => ['quality_certificate', 'lab_results'],
        'manufacturer_id' => $manufacturer->id,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/products', $data);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'name' => 'Test Product',
                'sku' => 'SKU-TEST-001',
                'barcode' => '1234567890123',
                'category' => 'Dairy',
                'origin_type' => 'local',
                'inventory_policy' => 'fefo',
                'is_active' => true,
            ],
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'sku' => 'SKU-TEST-001',
    ]);
});

test('can create product with minimal fields', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Minimal Product',
        'sku' => 'SKU-MIN-001',
        'category' => 'Beverages',
        'unit' => 'l',
        'origin_type' => 'imported',
        'country_of_origin' => 'Turkey',
        'shelf_life_days' => 365,
        'inventory_policy' => 'fifo',
        'manufacturer_id' => $manufacturer->id,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/products', $data);

    $response->assertCreated();
    $this->assertDatabaseHas('products', ['sku' => 'SKU-MIN-001']);
});

test('cannot create product with duplicate sku', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->create(['sku' => 'SKU-DUPLICATE', 'manufacturer_id' => $manufacturer->id]);

    $data = [
        'name' => 'Another Product',
        'sku' => 'SKU-DUPLICATE',
        'category' => 'Dairy',
        'unit' => 'kg',
        'origin_type' => 'local',
        'country_of_origin' => 'Georgia',
        'shelf_life_days' => 30,
        'inventory_policy' => 'fefo',
        'manufacturer_id' => $manufacturer->id,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/products', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['sku']);
});

test('cannot create product with invalid temperature range', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Test Product',
        'sku' => 'SKU-001',
        'category' => 'Dairy',
        'unit' => 'kg',
        'origin_type' => 'local',
        'country_of_origin' => 'Georgia',
        'storage_temp_min' => 10,
        'storage_temp_max' => 5,
        'shelf_life_days' => 30,
        'inventory_policy' => 'fefo',
        'manufacturer_id' => $manufacturer->id,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/products', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['storage_temp_max']);
});

test('cannot create product with invalid origin type', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Test Product',
        'sku' => 'SKU-001',
        'category' => 'Dairy',
        'unit' => 'kg',
        'origin_type' => 'invalid',
        'country_of_origin' => 'Georgia',
        'shelf_life_days' => 30,
        'inventory_policy' => 'fefo',
        'manufacturer_id' => $manufacturer->id,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/products', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['origin_type']);
});

test('can view product with manufacturer', function (): void {
    $manufacturer = Manufacturer::factory()->create(['full_name' => 'Test Manufacturer']);
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/products/{$product->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'manufacturer' => [
                    'id' => $manufacturer->id,
                    'full_name' => 'Test Manufacturer',
                ],
            ],
        ]);
});

test('can update product', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    $data = [
        'name' => 'Updated Product Name',
        'is_active' => false,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/products/{$product->id}", $data);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => 'Updated Product Name',
                'is_active' => false,
            ],
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product Name',
        'is_active' => false,
    ]);
});

test('can update product sku without conflict', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['sku' => 'OLD-SKU', 'manufacturer_id' => $manufacturer->id]);

    $data = ['sku' => 'NEW-SKU'];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/products/{$product->id}", $data);

    $response->assertOk();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'sku' => 'NEW-SKU',
    ]);
});

test('cannot update product with existing sku', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product1 = Product::factory()->create(['sku' => 'SKU-001', 'manufacturer_id' => $manufacturer->id]);
    $product2 = Product::factory()->create(['sku' => 'SKU-002', 'manufacturer_id' => $manufacturer->id]);

    $data = ['sku' => 'SKU-001'];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/products/{$product2->id}", $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['sku']);
});

test('can delete product', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/products/{$product->id}");

    $response->assertOk()
        ->assertJson([
            'message' => 'Product deleted successfully',
        ]);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('cannot access products without authentication', function (): void {
    $response = $this->getJson('/api/products');
    $response->assertUnauthorized();
});

test('cannot create product without permission', function (): void {
    $userWithoutPermission = User::factory()->create();
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => 'Test Product',
        'sku' => 'SKU-001',
        'category' => 'Dairy',
        'unit' => 'kg',
        'origin_type' => 'local',
        'country_of_origin' => 'Georgia',
        'shelf_life_days' => 30,
        'inventory_policy' => 'fefo',
        'manufacturer_id' => $manufacturer->id,
    ];

    $response = $this->actingAs($userWithoutPermission, 'api')
        ->postJson('/api/products', $data);

    $response->assertForbidden();
});

test('trims string fields on create', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $data = [
        'name' => '  Test Product  ',
        'sku' => '  SKU-001  ',
        'category' => '  Dairy  ',
        'unit' => '  kg  ',
        'origin_type' => 'local',
        'country_of_origin' => '  Georgia  ',
        'shelf_life_days' => 30,
        'inventory_policy' => 'fefo',
        'manufacturer_id' => $manufacturer->id,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/products', $data);

    $response->assertCreated();
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'sku' => 'SKU-001',
        'category' => 'Dairy',
    ]);
});

test('products are paginated with correct per_page', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(50)->create(['manufacturer_id' => $manufacturer->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/products?per_page=20');

    $response->assertOk()
        ->assertJsonCount(20, 'data')
        ->assertJson(['meta' => ['per_page' => 20]]);
});

test('per_page is clamped between 1 and 100', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(150)->create(['manufacturer_id' => $manufacturer->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/products?per_page=200');

    $response->assertOk()
        ->assertJsonCount(100, 'data')
        ->assertJson(['meta' => ['per_page' => 100]]);
});

test('can filter local products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(3)->local()->create(['manufacturer_id' => $manufacturer->id]);
    Product::factory()->count(2)->imported()->create(['manufacturer_id' => $manufacturer->id]);

    $localProducts = Product::local()->get();
    expect($localProducts)->toHaveCount(3);
    expect($localProducts->every(fn ($p): bool => $p->origin_type === 'local'))->toBeTrue();
});

test('can filter imported products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(3)->local()->create(['manufacturer_id' => $manufacturer->id]);
    Product::factory()->count(2)->imported()->create(['manufacturer_id' => $manufacturer->id]);

    $importedProducts = Product::imported()->get();
    expect($importedProducts)->toHaveCount(2);
    expect($importedProducts->every(fn ($p): bool => $p->origin_type === 'imported'))->toBeTrue();
});

test('can filter active products', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    Product::factory()->count(3)->active()->create(['manufacturer_id' => $manufacturer->id]);
    Product::factory()->count(2)->inactive()->create(['manufacturer_id' => $manufacturer->id]);

    $activeProducts = Product::active()->get();
    expect($activeProducts)->toHaveCount(3);
    expect($activeProducts->every(fn ($p): bool => $p->is_active))->toBeTrue();
});

test('can update nullable fields to null', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create([
        'barcode' => '1234567890',
        'qr_code' => 'QR-123',
        'brand' => 'Test Brand',
        'manufacturer_id' => $manufacturer->id,
    ]);

    $data = [
        'barcode' => null,
        'qr_code' => null,
        'brand' => null,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/products/{$product->id}", $data);

    $response->assertOk();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'barcode' => null,
        'qr_code' => null,
        'brand' => null,
    ]);
});

test('trims whitespace in nullable fields on update', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    $data = [
        'barcode' => '  123456  ',
        'qr_code' => '  QR-001  ',
        'brand' => '  Brand Name  ',
        'category' => '  Dairy  ',
        'unit' => '  kg  ',
        'country_of_origin' => '  Georgia  ',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/products/{$product->id}", $data);

    $response->assertOk();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'barcode' => '123456',
        'qr_code' => 'QR-001',
        'brand' => 'Brand Name',
        'category' => 'Dairy',
        'unit' => 'kg',
        'country_of_origin' => 'Georgia',
    ]);
});
