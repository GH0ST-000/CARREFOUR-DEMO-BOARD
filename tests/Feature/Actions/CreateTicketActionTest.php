<?php

declare(strict_types=1);

use App\Actions\Ticket\CreateTicketAction;
use App\Models\Ticket;
use App\Models\User;

test('can create ticket with all fields', function (): void {
    $user = User::factory()->create();

    $data = [
        'title' => 'Cannot access my account',
        'description' => 'I have been locked out of my account after multiple failed login attempts.',
        'priority' => 'high',
        'user_id' => $user->id,
    ];

    $action = app(CreateTicketAction::class);
    $ticket = $action->execute($data);

    expect($ticket)->toBeInstanceOf(Ticket::class);
    expect($ticket->title)->toBe('Cannot access my account');
    expect($ticket->priority)->toBe('high');
    expect($ticket->user_id)->toBe($user->id);
    expect($ticket->status)->toBe('open');
    expect($ticket->exists)->toBeTrue();
});

test('can create ticket with default status', function (): void {
    $user = User::factory()->create();

    $data = [
        'title' => 'General inquiry',
        'description' => 'How do I change my password?',
        'user_id' => $user->id,
    ];

    $action = app(CreateTicketAction::class);
    $ticket = $action->execute($data);

    expect($ticket->status)->toBe('open');
    expect($ticket->assigned_to)->toBeNull();
    expect($ticket->closed_at)->toBeNull();
});
