<?php

declare(strict_types=1);

use App\Actions\Ticket\UpdateTicketAction;
use App\Models\Ticket;
use App\Models\User;

test('can update ticket fields', function (): void {
    $ticket = Ticket::factory()->create(['title' => 'Old Title']);

    $action = app(UpdateTicketAction::class);
    $updated = $action->execute($ticket, ['title' => 'New Title']);

    expect($updated->title)->toBe('New Title');
    expect($updated->id)->toBe($ticket->id);
});

test('can update ticket status', function (): void {
    $ticket = Ticket::factory()->open()->create();

    $action = app(UpdateTicketAction::class);
    $updated = $action->execute($ticket, ['status' => 'in_progress']);

    expect($updated->status)->toBe('in_progress');
});

test('can assign ticket to agent', function (): void {
    $agent = User::factory()->create();
    $ticket = Ticket::factory()->create();

    $action = app(UpdateTicketAction::class);
    $updated = $action->execute($ticket, ['assigned_to' => $agent->id]);

    expect($updated->assigned_to)->toBe($agent->id);
});

test('returns fresh model after update', function (): void {
    $ticket = Ticket::factory()->create(['priority' => 'low']);

    $action = app(UpdateTicketAction::class);
    $updated = $action->execute($ticket, ['priority' => 'high']);

    expect($updated->priority)->toBe('high');
});
