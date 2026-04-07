<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Carbon;

test('ticket has correct fillable attributes', function (): void {
    $user = User::factory()->create();

    $data = [
        'title' => 'Test Ticket',
        'description' => 'Test description',
        'status' => 'open',
        'priority' => 'high',
        'user_id' => $user->id,
    ];

    $ticket = Ticket::create($data);

    expect($ticket->title)->toBe('Test Ticket');
    expect($ticket->description)->toBe('Test description');
    expect($ticket->status)->toBe('open');
    expect($ticket->priority)->toBe('high');
    expect($ticket->user_id)->toBe($user->id);
});

test('ticket belongs to user', function (): void {
    $user = User::factory()->create(['name' => 'John Doe']);
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);

    expect($ticket->user)->toBeInstanceOf(User::class);
    expect($ticket->user->name)->toBe('John Doe');
});

test('ticket belongs to assignee', function (): void {
    $agent = User::factory()->create(['name' => 'Agent Smith']);
    $ticket = Ticket::factory()->create(['assigned_to' => $agent->id]);

    expect($ticket->assignee)->toBeInstanceOf(User::class);
    expect($ticket->assignee->name)->toBe('Agent Smith');
});

test('ticket has many messages', function (): void {
    $ticket = Ticket::factory()->create();
    TicketMessage::factory()->count(3)->create(['ticket_id' => $ticket->id]);

    expect($ticket->messages)->toHaveCount(3);
    expect($ticket->messages->first())->toBeInstanceOf(TicketMessage::class);
});

test('ticket has many attachments', function (): void {
    $ticket = Ticket::factory()->create();
    TicketAttachment::factory()->count(2)->create(['ticket_id' => $ticket->id]);

    expect($ticket->attachments)->toHaveCount(2);
    expect($ticket->attachments->first())->toBeInstanceOf(TicketAttachment::class);
});

test('ticket casts closed_at to datetime', function (): void {
    $ticket = Ticket::factory()->closed()->create();

    expect($ticket->closed_at)->toBeInstanceOf(Carbon::class);
});

test('scopeOpen filters open tickets', function (): void {
    Ticket::factory()->count(3)->open()->create();
    Ticket::factory()->count(2)->closed()->create();

    $openTickets = Ticket::open()->get();

    expect($openTickets)->toHaveCount(3);
    expect($openTickets->every(fn ($t): bool => $t->status === 'open'))->toBeTrue();
});

test('scopeClosed filters closed tickets', function (): void {
    Ticket::factory()->count(3)->open()->create();
    Ticket::factory()->count(2)->closed()->create();

    $closedTickets = Ticket::closed()->get();

    expect($closedTickets)->toHaveCount(2);
    expect($closedTickets->every(fn ($t): bool => $t->status === 'closed'))->toBeTrue();
});

test('scopeStatus filters by given status', function (): void {
    Ticket::factory()->count(2)->open()->create();
    Ticket::factory()->count(3)->inProgress()->create();
    Ticket::factory()->count(1)->resolved()->create();

    expect(Ticket::status('open')->count())->toBe(2);
    expect(Ticket::status('in_progress')->count())->toBe(3);
    expect(Ticket::status('resolved')->count())->toBe(1);
});

test('scopePriority filters by given priority', function (): void {
    Ticket::factory()->count(2)->lowPriority()->create();
    Ticket::factory()->count(3)->highPriority()->create();

    expect(Ticket::priority('low')->count())->toBe(2);
    expect(Ticket::priority('high')->count())->toBe(3);
});

test('scopeForUser filters tickets by user', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Ticket::factory()->count(3)->create(['user_id' => $user1->id]);
    Ticket::factory()->count(5)->create(['user_id' => $user2->id]);

    expect(Ticket::forUser($user1->id)->count())->toBe(3);
    expect(Ticket::forUser($user2->id)->count())->toBe(5);
});

test('scopeSearch searches by title and description', function (): void {
    Ticket::factory()->create(['title' => 'Server crash', 'description' => 'Generic desc']);
    Ticket::factory()->create(['title' => 'Generic title', 'description' => 'Payment failed']);
    Ticket::factory()->create(['title' => 'Something else', 'description' => 'Unrelated']);

    expect(Ticket::search('Server')->count())->toBe(1);
    expect(Ticket::search('Payment')->count())->toBe(1);
    expect(Ticket::search('Nonexistent')->count())->toBe(0);
});

test('isOpen returns true for open tickets', function (): void {
    $ticket = Ticket::factory()->open()->create();

    expect($ticket->isOpen())->toBeTrue();
    expect($ticket->isClosed())->toBeFalse();
});

test('isClosed returns true for closed tickets', function (): void {
    $ticket = Ticket::factory()->closed()->create();

    expect($ticket->isClosed())->toBeTrue();
    expect($ticket->isOpen())->toBeFalse();
});

test('isInProgress returns true for in-progress tickets', function (): void {
    $ticket = Ticket::factory()->inProgress()->create();

    expect($ticket->isInProgress())->toBeTrue();
});

test('isResolved returns true for resolved tickets', function (): void {
    $ticket = Ticket::factory()->resolved()->create();

    expect($ticket->isResolved())->toBeTrue();
});

test('isOwnedBy returns true for ticket owner', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);

    expect($ticket->isOwnedBy($user))->toBeTrue();
    expect($ticket->isOwnedBy($otherUser))->toBeFalse();
});

test('isAssignedTo returns true for assigned agent', function (): void {
    $agent = User::factory()->create();
    $otherAgent = User::factory()->create();
    $ticket = Ticket::factory()->create(['assigned_to' => $agent->id]);

    expect($ticket->isAssignedTo($agent))->toBeTrue();
    expect($ticket->isAssignedTo($otherAgent))->toBeFalse();
});

test('scopeOrdered returns tickets in descending created_at order', function (): void {
    $old = Ticket::factory()->create(['created_at' => now()->subDays(2)]);
    $new = Ticket::factory()->create(['created_at' => now()]);
    $mid = Ticket::factory()->create(['created_at' => now()->subDay()]);

    $tickets = Ticket::ordered()->get();

    expect($tickets[0]->id)->toBe($new->id);
    expect($tickets[1]->id)->toBe($mid->id);
    expect($tickets[2]->id)->toBe($old->id);
});

test('ticket factory creates valid ticket', function (): void {
    $ticket = Ticket::factory()->create();

    expect($ticket)->toBeInstanceOf(Ticket::class);
    expect($ticket->exists)->toBeTrue();
    expect($ticket->user)->toBeInstanceOf(User::class);
});

test('ticket factory can create assigned ticket', function (): void {
    $agent = User::factory()->create();
    $ticket = Ticket::factory()->assignedTo($agent)->create();

    expect($ticket->assigned_to)->toBe($agent->id);
});

test('scopeAssignedTo filters tickets by assigned agent', function (): void {
    $agent1 = User::factory()->create();
    $agent2 = User::factory()->create();

    Ticket::factory()->count(2)->create(['assigned_to' => $agent1->id]);
    Ticket::factory()->count(3)->create(['assigned_to' => $agent2->id]);

    expect(Ticket::assignedTo($agent1->id)->count())->toBe(2);
    expect(Ticket::assignedTo($agent2->id)->count())->toBe(3);
});
