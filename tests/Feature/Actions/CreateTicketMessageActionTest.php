<?php

declare(strict_types=1);

use App\Actions\TicketMessage\CreateTicketMessageAction;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

test('can create a ticket message', function (): void {
    $ticket = Ticket::factory()->create();
    $user = User::factory()->create();

    $data = [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'body' => 'This is a support reply.',
    ];

    $action = app(CreateTicketMessageAction::class);
    $message = $action->execute($data);

    expect($message)->toBeInstanceOf(TicketMessage::class);
    expect($message->ticket_id)->toBe($ticket->id);
    expect($message->user_id)->toBe($user->id);
    expect($message->body)->toBe('This is a support reply.');
    expect($message->exists)->toBeTrue();
});
