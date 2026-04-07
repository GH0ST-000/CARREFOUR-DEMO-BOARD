<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

test('ticket message belongs to ticket', function (): void {
    $ticket = Ticket::factory()->create();
    $message = TicketMessage::factory()->create(['ticket_id' => $ticket->id]);

    expect($message->ticket)->toBeInstanceOf(Ticket::class);
    expect($message->ticket->id)->toBe($ticket->id);
});

test('ticket message belongs to user', function (): void {
    $user = User::factory()->create(['name' => 'Support Agent']);
    $message = TicketMessage::factory()->create(['user_id' => $user->id]);

    expect($message->user)->toBeInstanceOf(User::class);
    expect($message->user->name)->toBe('Support Agent');
});

test('ticket message has correct fillable attributes', function (): void {
    $ticket = Ticket::factory()->create();
    $user = User::factory()->create();

    $message = TicketMessage::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'body' => 'This is a test message.',
    ]);

    expect($message->ticket_id)->toBe($ticket->id);
    expect($message->user_id)->toBe($user->id);
    expect($message->body)->toBe('This is a test message.');
});

test('scopeOrdered returns messages in ascending created_at order', function (): void {
    $ticket = Ticket::factory()->create();

    $first = TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'created_at' => now()->subMinutes(10),
    ]);
    $second = TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'created_at' => now(),
    ]);

    $messages = TicketMessage::ordered()->get();

    expect($messages[0]->id)->toBe($first->id);
    expect($messages[1]->id)->toBe($second->id);
});

test('ticket message factory creates valid message', function (): void {
    $message = TicketMessage::factory()->create();

    expect($message)->toBeInstanceOf(TicketMessage::class);
    expect($message->exists)->toBeTrue();
    expect($message->ticket)->toBeInstanceOf(Ticket::class);
    expect($message->user)->toBeInstanceOf(User::class);
});
