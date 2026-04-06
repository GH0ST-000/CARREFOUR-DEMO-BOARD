<?php

namespace Database\Factories;

use App\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseLocation>
 */
class WarehouseLocationFactory extends Factory
{
    protected $model = WarehouseLocation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => fake()->unique()->bothify('WH-###-??'),
            'type' => fake()->randomElement([
                'central_warehouse',
                'regional_warehouse',
                'branch',
                'storage_unit',
                'zone',
            ]),
            'parent_id' => null,
            'temp_min' => fake()->optional()->randomFloat(2, -20, 10),
            'temp_max' => fake()->optional()->randomFloat(2, 0, 25),
            'responsible_user_id' => null,
            'inspection_frequency_hours' => fake()->optional()->numberBetween(4, 24),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->optional()->address(),
            'has_sensor' => fake()->boolean(30),
            'is_active' => fake()->boolean(90),
        ];
    }

    public function centralWarehouse(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'central_warehouse',
            'parent_id' => null,
        ]);
    }

    public function regionalWarehouse(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'regional_warehouse',
        ]);
    }

    public function branch(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'branch',
        ]);
    }

    public function storageUnit(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'storage_unit',
        ]);
    }

    public function zone(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'zone',
        ]);
    }

    public function temperatureControlled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'temp_min' => 0,
            'temp_max' => 4,
            'type' => 'storage_unit',
        ]);
    }

    public function frozen(): static
    {
        return $this->state(fn (array $attributes): array => [
            'temp_min' => -18,
            'temp_max' => -15,
            'type' => 'storage_unit',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function withSensor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'has_sensor' => true,
        ]);
    }
}
