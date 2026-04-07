<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Receiving;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receiving>
 */
final class ReceivingFactory extends Factory
{
    protected $model = Receiving::class;

    public function definition(): array
    {
        return [
            'receipt_number' => fake()->unique()->bothify('RCP-####-????'),
            'receipt_datetime' => fake()->dateTimeBetween('-30 days', 'now'),
            'supplier_invoice_number' => fake()->optional()->bothify('INV-########'),
            'batch_id' => Batch::factory(),
            'warehouse_location_id' => WarehouseLocation::factory(),
            'received_quantity' => $quantity = fake()->randomFloat(2, 10, 1000),
            'unit' => fake()->randomElement(['kg', 'l', 'pcs', 'box']),
            'recorded_temperature' => fake()->optional()->randomFloat(2, -5, 25),
            'temperature_compliant' => fake()->boolean(85),
            'temperature_notes' => fake()->optional()->sentence(),
            'packaging_condition' => fake()->randomElement([
                'excellent',
                'good',
                'acceptable',
                'damaged',
            ]),
            'quality_notes' => fake()->optional()->sentence(),
            'documents_verified' => fake()->boolean(80),
            'missing_documents' => fake()->optional()->randomElements(
                ['certificate_of_origin', 'quality_certificate', 'lab_results'],
                fake()->numberBetween(1, 2)
            ),
            'status' => fake()->randomElement(['pending', 'accepted']),
            'rejection_reason' => null,
            'photos' => fake()->optional()->randomElements(
                ['photo1.jpg', 'photo2.jpg', 'photo3.jpg'],
                fake()->numberBetween(1, 3)
            ),
            'received_by_user_id' => User::factory(),
            'verified_by_user_id' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'pending',
            'verified_by_user_id' => null,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'accepted',
            'verified_by_user_id' => User::factory(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(),
            'verified_by_user_id' => User::factory(),
        ]);
    }

    public function quarantined(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'quarantined',
            'quality_notes' => fake()->sentence(),
        ]);
    }

    public function temperatureNonCompliant(): static
    {
        return $this->state(fn (array $attributes): array => [
            'temperature_compliant' => false,
            'temperature_notes' => fake()->sentence(),
        ]);
    }

    public function withPhotos(): static
    {
        return $this->state(fn (array $attributes): array => [
            'photos' => ['photo1.jpg', 'photo2.jpg', 'photo3.jpg'],
        ]);
    }

    public function withMissingDocuments(): static
    {
        return $this->state(fn (array $attributes): array => [
            'documents_verified' => false,
            'missing_documents' => ['certificate_of_origin', 'lab_results'],
        ]);
    }
}
