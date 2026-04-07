<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
final class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->company().' LLC',
            'short_name' => fake()->companySuffix(),
            'legal_form' => fake()->randomElement(['LLC', 'JSC', 'CJSC', 'Partnership', 'Cooperative']),
            'identification_number' => fake()->unique()->numerify('##########'),
            'legal_address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'country' => fake()->country(),
            'region' => fake()->state(),
            'city' => fake()->city(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
