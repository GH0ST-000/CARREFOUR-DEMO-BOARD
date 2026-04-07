<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
final class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(2, true),
            'status' => 'open',
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'user_id' => User::factory(),
            'assigned_to' => null,
            'closed_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'open',
            'closed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'in_progress',
            'closed_at' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'resolved',
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes): array => [
            'priority' => 'low',
        ]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes): array => [
            'priority' => 'medium',
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes): array => [
            'priority' => 'high',
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'assigned_to' => $user->id,
        ]);
    }
}
