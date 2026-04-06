<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        $productionDate = fake()->dateTimeBetween('-60 days', '-7 days');
        $expiryDate = fake()->dateTimeBetween('+7 days', '+365 days');

        return [
            'batch_number' => fake()->unique()->bothify('BATCH-####-????'),
            'import_declaration_number' => fake()->optional()->bothify('IMP-########'),
            'local_production_number' => fake()->optional()->bothify('LOC-########'),
            'production_date' => $productionDate,
            'expiry_date' => $expiryDate,
            'receiving_datetime' => fake()->optional()->dateTimeBetween($productionDate, 'now'),
            'quantity' => $quantity = fake()->randomFloat(2, 10, 1000),
            'remaining_quantity' => fake()->randomFloat(2, 0, $quantity),
            'unit' => fake()->randomElement(['kg', 'l', 'pcs', 'box']),
            'status' => fake()->randomElement([
                'pending',
                'received',
                'in_storage',
                'in_transit',
                'blocked',
            ]),
            'warehouse_location_id' => null,
            'receiving_temperature' => fake()->optional()->randomFloat(2, -20, 25),
            'packaging_condition' => fake()->optional()->sentence(),
            'product_id' => Product::factory(),
            'received_by_user_id' => null,
            'linked_documents' => fake()->optional()->randomElements(
                ['invoice_001.pdf', 'certificate_002.pdf', 'lab_report_003.pdf'],
                fake()->numberBetween(1, 3)
            ),
            'temperature_history' => null,
            'movement_history' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes): array => [
            'local_production_number' => fake()->bothify('LOC-########'),
            'import_declaration_number' => null,
        ]);
    }

    public function imported(): static
    {
        return $this->state(fn (array $attributes): array => [
            'import_declaration_number' => fake()->bothify('IMP-########'),
            'local_production_number' => null,
        ]);
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'received',
            'receiving_datetime' => now(),
            'received_by_user_id' => User::factory(),
        ]);
    }

    public function inStorage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'in_storage',
            'warehouse_location_id' => WarehouseLocation::factory(),
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'blocked',
        ]);
    }

    public function recalled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'recalled',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'expired',
            'expiry_date' => now()->subDays(1),
        ]);
    }

    public function expiringIn(int $days): static
    {
        return $this->state(fn (array $attributes): array => [
            'expiry_date' => now()->addDays($days),
        ]);
    }

    public function fullyConsumed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'remaining_quantity' => 0,
        ]);
    }
}
