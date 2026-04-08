<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
final class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type' => 'direct',
            'last_message_at' => null,
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'direct',
        ]);
    }

    public function withLastMessageAt(?DateTimeInterface $date = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_message_at' => $date ?? now(),
        ]);
    }
}
