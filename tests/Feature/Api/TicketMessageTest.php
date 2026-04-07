<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
        'create_ticket',
        'view_any_ticket',
        'view_ticket',
        'update_ticket',
    ]);
});

test('can list messages for a ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);
    TicketMessage::factory()->count(5)->create([
        'ticket_id' => $ticket->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/tickets/{$ticket->id}/messages");

    $response->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'ticket_id',
                    'user_id',
                    'body',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can add a message to a ticket', function (): void {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->user->id]);

    $data = [
        'body' => 'Here is some additional information about the issue.',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/messages", $data);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'ticket_id' => $ticket->id,
                'user_id' => $this->user->id,
                'body' => 'Here is some additional information about the issue.',
            ],
        ]);

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'user_id' => $this->user->id,
        'body' => 'Here is some additional information about the issue.',
    ]);
});

test('cannot add message to closed ticket', function (): void {
    $ticket = Ticket::factory()->closed()->create(['user_id' => $this->user->id]);

    $data = [
        'body' => 'Trying to add a message to a closed ticket.',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/messages", $data);

    $response->assertUnprocessable();
});

test('cannot add message without body', function (): void {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/messages", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['body']);
});

test('customer can add message to own ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    $ticket = Ticket::factory()->open()->create(['user_id' => $customer->id]);

    $data = [
        'body' => 'Follow-up from customer.',
    ];

    $response = $this->actingAs($customer, 'api')
        ->postJson("/api/tickets/{$ticket->id}/messages", $data);

    $response->assertCreated();
});

test('customer cannot add message to other users ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    $otherTicket = Ticket::factory()->open()->create();

    $data = [
        'body' => 'Trying to message someone else ticket.',
    ];

    $response = $this->actingAs($customer, 'api')
        ->postJson("/api/tickets/{$otherTicket->id}/messages", $data);

    $response->assertForbidden();
});

test('messages are ordered chronologically', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    $first = TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $this->user->id,
        'body' => 'First message',
        'created_at' => now()->subMinutes(10),
    ]);

    $second = TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $this->user->id,
        'body' => 'Second message',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/tickets/{$ticket->id}/messages");

    $response->assertOk();

    $data = $response->json('data');
    expect($data[0]['id'])->toBe($first->id);
    expect($data[1]['id'])->toBe($second->id);
});

test('trims message body whitespace', function (): void {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->user->id]);

    $data = [
        'body' => '  Trimmed message content  ',
    ];

    $response = $this->actingAs($customer ?? $this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/messages", $data);

    $response->assertCreated();
    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'body' => 'Trimmed message content',
    ]);
});

test('messages include user information', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);
    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/tickets/{$ticket->id}/messages");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user' => ['id', 'name', 'email'],
                ],
            ],
        ]);
});
