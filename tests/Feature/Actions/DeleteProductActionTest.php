<?php

declare(strict_types=1);

use App\Actions\Product\DeleteProductAction;
use App\Models\Manufacturer;
use App\Models\Product;

test('can delete product', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);
    $productId = $product->id;

    $action = app(DeleteProductAction::class);
    $result = $action->execute($product);

    expect($result)->toBeTrue();
    expect(Product::find($productId))->toBeNull();
});

test('returns true when deletion succeeds', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create(['manufacturer_id' => $manufacturer->id]);

    $action = app(DeleteProductAction::class);
    $result = $action->execute($product);

    expect($result)->toBeTrue();
});

test('removes product from database', function (): void {
    $manufacturer = Manufacturer::factory()->create();
    $product = Product::factory()->create([
        'sku' => 'DELETE-TEST-SKU',
        'manufacturer_id' => $manufacturer->id,
    ]);

    $action = app(DeleteProductAction::class);
    $action->execute($product);

    $this->assertDatabaseMissing('products', [
        'sku' => 'DELETE-TEST-SKU',
    ]);
});
