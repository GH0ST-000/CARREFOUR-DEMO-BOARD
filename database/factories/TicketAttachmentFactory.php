<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketAttachment>
 */
final class TicketAttachmentFactory extends Factory
{
    protected $model = TicketAttachment::class;

    public function definition(): array
    {
        $extensions = ['pdf', 'png', 'jpg', 'docx', 'txt'];
        $extension = fake()->randomElement($extensions);

        return [
            'ticket_id' => Ticket::factory(),
            'file_path' => 'tickets/attachments/'.fake()->uuid().'.'.$extension,
            'file_name' => fake()->words(2, true).'.'.$extension,
            'file_size' => fake()->numberBetween(1024, 10485760),
            'mime_type' => match ($extension) {
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
            },
        ];
    }
}
