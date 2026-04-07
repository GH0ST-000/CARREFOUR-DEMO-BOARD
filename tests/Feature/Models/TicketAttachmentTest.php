<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketAttachment;

test('ticket attachment belongs to ticket', function (): void {
    $ticket = Ticket::factory()->create();
    $attachment = TicketAttachment::factory()->create(['ticket_id' => $ticket->id]);

    expect($attachment->ticket)->toBeInstanceOf(Ticket::class);
    expect($attachment->ticket->id)->toBe($ticket->id);
});

test('ticket attachment has correct fillable attributes', function (): void {
    $ticket = Ticket::factory()->create();

    $attachment = TicketAttachment::create([
        'ticket_id' => $ticket->id,
        'file_path' => 'tickets/attachments/test.pdf',
        'file_name' => 'document.pdf',
        'file_size' => 204800,
        'mime_type' => 'application/pdf',
    ]);

    expect($attachment->ticket_id)->toBe($ticket->id);
    expect($attachment->file_name)->toBe('document.pdf');
    expect($attachment->file_size)->toBe(204800);
    expect($attachment->mime_type)->toBe('application/pdf');
});

test('ticket attachment casts file_size to integer', function (): void {
    $attachment = TicketAttachment::factory()->create(['file_size' => '1024']);

    expect($attachment->file_size)->toBeInt();
    expect($attachment->file_size)->toBe(1024);
});

test('ticket attachment factory creates valid attachment', function (): void {
    $attachment = TicketAttachment::factory()->create();

    expect($attachment)->toBeInstanceOf(TicketAttachment::class);
    expect($attachment->exists)->toBeTrue();
    expect($attachment->ticket)->toBeInstanceOf(Ticket::class);
    expect($attachment->file_size)->toBeInt();
});
