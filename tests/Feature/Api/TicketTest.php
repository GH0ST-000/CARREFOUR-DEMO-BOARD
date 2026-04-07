<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
        'create_ticket',
        'view_any_ticket',
        'view_ticket',
        'update_ticket',
        'delete_ticket',
    ]);
});

test('can list tickets with cursor pagination', function (): void {
    Ticket::factory()->count(30)->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/tickets?per_page=10');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'user_id',
                    'assigned_to',
                    'closed_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ])
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 10)
            ->where('meta.per_page', 10)
            ->etc()
        );
});

test('customer can only see own tickets when lacking view_any_ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    Ticket::factory()->count(3)->create(['user_id' => $customer->id]);
    Ticket::factory()->count(5)->create();

    $response = $this->actingAs($customer, 'api')
        ->getJson('/api/tickets');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin can see all tickets with view_any_ticket', function (): void {
    Ticket::factory()->count(3)->create(['user_id' => $this->user->id]);
    Ticket::factory()->count(5)->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/tickets');

    $response->assertOk()
        ->assertJsonCount(8, 'data');
});

test('can filter tickets by status', function (): void {
    Ticket::factory()->count(3)->open()->create(['user_id' => $this->user->id]);
    Ticket::factory()->count(2)->closed()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/tickets?status=open');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can filter tickets by priority', function (): void {
    Ticket::factory()->count(2)->highPriority()->create(['user_id' => $this->user->id]);
    Ticket::factory()->count(3)->lowPriority()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/tickets?priority=high');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('can search tickets by keyword', function (): void {
    Ticket::factory()->create([
        'title' => 'Server is down',
        'user_id' => $this->user->id,
    ]);
    Ticket::factory()->create([
        'title' => 'Billing question',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/tickets?search=Server');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('can create a ticket', function (): void {
    $data = [
        'title' => 'Cannot login to my account',
        'description' => 'I have been trying to login but keep getting an error.',
        'priority' => 'high',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/tickets', $data);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'title' => 'Cannot login to my account',
                'description' => 'I have been trying to login but keep getting an error.',
                'status' => 'open',
                'priority' => 'high',
                'user_id' => $this->user->id,
            ],
        ]);

    $this->assertDatabaseHas('tickets', [
        'title' => 'Cannot login to my account',
        'user_id' => $this->user->id,
        'status' => 'open',
    ]);
});

test('can create ticket with default priority', function (): void {
    $data = [
        'title' => 'General question',
        'description' => 'How do I update my profile?',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/tickets', $data);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'status' => 'open',
                'priority' => 'medium',
            ],
        ]);
});

test('cannot create ticket without title', function (): void {
    $data = [
        'description' => 'Some description',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/tickets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('cannot create ticket without description', function (): void {
    $data = [
        'title' => 'Some title',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/tickets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['description']);
});

test('cannot create ticket with invalid priority', function (): void {
    $data = [
        'title' => 'Some title',
        'description' => 'Some description',
        'priority' => 'urgent',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/tickets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['priority']);
});

test('can view a ticket with details', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/tickets/{$ticket->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
            ],
        ]);
});

test('customer can view own ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    $ticket = Ticket::factory()->create(['user_id' => $customer->id]);

    $response = $this->actingAs($customer, 'api')
        ->getJson("/api/tickets/{$ticket->id}");

    $response->assertOk();
});

test('customer cannot view other users ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    $otherTicket = Ticket::factory()->create();

    $response = $this->actingAs($customer, 'api')
        ->getJson("/api/tickets/{$otherTicket->id}");

    $response->assertForbidden();
});

test('can update ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    $data = [
        'title' => 'Updated title',
        'status' => 'in_progress',
        'priority' => 'high',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/tickets/{$ticket->id}", $data);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $ticket->id,
                'title' => 'Updated title',
                'status' => 'in_progress',
                'priority' => 'high',
            ],
        ]);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'title' => 'Updated title',
        'status' => 'in_progress',
    ]);
});

test('can assign ticket to support agent', function (): void {
    $agent = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    $data = [
        'assigned_to' => $agent->id,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/tickets/{$ticket->id}", $data);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'assigned_to' => $agent->id,
            ],
        ]);
});

test('customer can only update title and description of own ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    $ticket = Ticket::factory()->create([
        'user_id' => $customer->id,
        'status' => 'open',
        'priority' => 'low',
    ]);

    $data = [
        'title' => 'Updated by customer',
        'description' => 'New description',
        'status' => 'resolved',
        'priority' => 'high',
    ];

    $response = $this->actingAs($customer, 'api')
        ->putJson("/api/tickets/{$ticket->id}", $data);

    $response->assertOk();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'title' => 'Updated by customer',
        'description' => 'New description',
        'status' => 'open',
        'priority' => 'low',
    ]);
});

test('can delete ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/tickets/{$ticket->id}");

    $response->assertOk()
        ->assertJson([
            'message' => 'Ticket deleted successfully',
        ]);

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

test('can close a ticket', function (): void {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/close");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $ticket->id,
                'status' => 'closed',
            ],
        ]);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
    ]);

    $ticket->refresh();
    expect($ticket->closed_at)->not->toBeNull();
});

test('cannot close an already closed ticket', function (): void {
    $ticket = Ticket::factory()->closed()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/close");

    $response->assertUnprocessable();
});

test('can reopen a closed ticket', function (): void {
    $ticket = Ticket::factory()->closed()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/reopen");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $ticket->id,
                'status' => 'open',
            ],
        ]);

    $ticket->refresh();
    expect($ticket->closed_at)->toBeNull();
});

test('cannot reopen a ticket that is not closed', function (): void {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tickets/{$ticket->id}/reopen");

    $response->assertUnprocessable();
});

test('cannot access tickets without authentication', function (): void {
    $response = $this->getJson('/api/tickets');
    $response->assertUnauthorized();
});

test('cannot create ticket without permission', function (): void {
    $userWithoutPermission = User::factory()->create();

    $data = [
        'title' => 'Test ticket',
        'description' => 'Test description',
    ];

    $response = $this->actingAs($userWithoutPermission, 'api')
        ->postJson('/api/tickets', $data);

    $response->assertForbidden();
});

test('trims string fields on create', function (): void {
    $data = [
        'title' => '  Server Issue  ',
        'description' => '  The server is not responding  ',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/tickets', $data);

    $response->assertCreated();
    $this->assertDatabaseHas('tickets', [
        'title' => 'Server Issue',
        'description' => 'The server is not responding',
    ]);
});

test('per_page is clamped between 1 and 100', function (): void {
    Ticket::factory()->count(150)->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/tickets?per_page=200');

    $response->assertOk()
        ->assertJsonCount(100, 'data')
        ->assertJson(['meta' => ['per_page' => 100]]);
});

test('customer can close own ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    $ticket = Ticket::factory()->open()->create(['user_id' => $customer->id]);

    $response = $this->actingAs($customer, 'api')
        ->postJson("/api/tickets/{$ticket->id}/close");

    $response->assertOk()
        ->assertJson(['data' => ['status' => 'closed']]);
});

test('customer can reopen own ticket', function (): void {
    $customer = User::factory()->create();
    $customer->givePermissionTo('create_ticket');

    $ticket = Ticket::factory()->closed()->create(['user_id' => $customer->id]);

    $response = $this->actingAs($customer, 'api')
        ->postJson("/api/tickets/{$ticket->id}/reopen");

    $response->assertOk()
        ->assertJson(['data' => ['status' => 'open']]);
});

test('setting status to closed via update sets closed_at', function (): void {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/tickets/{$ticket->id}", ['status' => 'closed']);

    $response->assertOk()
        ->assertJson(['data' => ['status' => 'closed']]);

    $ticket->refresh();
    expect($ticket->closed_at)->not->toBeNull();
});

test('ticket show includes attachments', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);
    TicketAttachment::factory()->count(2)->create(['ticket_id' => $ticket->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/tickets/{$ticket->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'attachments' => [
                    '*' => [
                        'id',
                        'ticket_id',
                        'file_path',
                        'file_name',
                        'file_size',
                        'mime_type',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ])
        ->assertJsonCount(2, 'data.attachments');
});
