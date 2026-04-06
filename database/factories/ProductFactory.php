<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'barcode' => fake()->optional()->ean13(),
            'qr_code' => fake()->optional()->uuid(),
            'brand' => fake()->optional()->company(),
            'category' => fake()->randomElement([
                'Dairy',
                'Meat',
                'Vegetables',
                'Fruits',
                'Bakery',
                'Beverages',
                'Frozen',
                'Canned',
            ]),
            'unit' => fake()->randomElement(['kg', 'g', 'l', 'ml', 'pcs', 'box', 'pack']),
            'origin_type' => fake()->randomElement(['local', 'imported']),
            'country_of_origin' => fake()->country(),
            'storage_temp_min' => fake()->optional()->randomFloat(2, -20, 10),
            'storage_temp_max' => fake()->optional()->randomFloat(2, 0, 25),
            'shelf_life_days' => fake()->numberBetween(7, 365),
            'inventory_policy' => fake()->randomElement(['fifo', 'fefo']),
            'allergens' => fake()->optional()->randomElements(
                ['milk', 'eggs', 'nuts', 'gluten', 'soy', 'fish', 'shellfish'],
                fake()->numberBetween(0, 3)
            ),
            'risk_indicators' => fake()->optional()->randomElements(
                ['perishable', 'fragile', 'temperature_sensitive', 'allergen_warning'],
                fake()->numberBetween(0, 2)
            ),
            'required_documents' => fake()->optional()->randomElements(
                ['certificate_of_origin', 'quality_certificate', 'lab_results', 'import_declaration'],
                fake()->numberBetween(1, 3)
            ),
            'manufacturer_id' => Manufacturer::factory(),
            'is_active' => fake()->boolean(90),
        ];
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes): array => [
            'origin_type' => 'local',
            'country_of_origin' => 'Georgia',
        ]);
    }

    public function imported(): static
    {
        return $this->state(fn (array $attributes): array => [
            'origin_type' => 'imported',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function temperatureControlled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'storage_temp_min' => 0,
            'storage_temp_max' => 4,
            'category' => 'Dairy',
        ]);
    }

    public function frozen(): static
    {
        return $this->state(fn (array $attributes): array => [
            'storage_temp_min' => -18,
            'storage_temp_max' => -15,
            'category' => 'Frozen',
        ]);
    }
}
