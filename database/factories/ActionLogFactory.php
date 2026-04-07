<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActionLog>
 */
final class ActionLogFactory extends Factory
{
    protected $model = ActionLog::class;

    public function definition(): array
    {
        $modules = ['manufacturers', 'products', 'batches', 'warehouse-locations', 'receivings', 'tickets', 'users', 'dashboard'];
        $actionTypes = ['create', 'update', 'delete', 'login', 'logout', 'status_change'];
        $entityTypes = ['manufacturer', 'product', 'batch', 'warehouse_location', 'receiving', 'ticket', 'user'];

        return [
            'user_id' => User::factory(),
            'action_type' => fake()->randomElement($actionTypes),
            'entity_type' => fake()->randomElement($entityTypes),
            'entity_id' => fake()->numberBetween(1, 100),
            'module' => fake()->randomElement($modules),
            'description' => fake()->sentence(),
            'metadata' => null,
            'created_at' => now(),
        ];
    }

    public function forModule(string $module): static
    {
        return $this->state(fn (array $attributes): array => [
            'module' => $module,
        ]);
    }

    public function forActionType(string $actionType): static
    {
        return $this->state(fn (array $attributes): array => [
            'action_type' => $actionType,
        ]);
    }

    public function forEntityType(string $entityType): static
    {
        return $this->state(fn (array $attributes): array => [
            'entity_type' => $entityType,
        ]);
    }

    public function systemAction(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes): array => [
            'metadata' => $metadata,
        ]);
    }
}
