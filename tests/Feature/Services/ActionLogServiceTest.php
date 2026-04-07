<?php

declare(strict_types=1);

use App\Models\ActionLog;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActionLogService;

beforeEach(function (): void {
    $this->service = new ActionLogService;
});

test('log creates an action log entry', function (): void {
    $user = User::factory()->create();

    $log = $this->service->log(
        actionType: 'create',
        entityType: 'product',
        entityId: 1,
        module: 'products',
        description: 'Product #1 created',
        userId: $user->id,
    );

    expect($log)->toBeInstanceOf(ActionLog::class);
    expect($log->user_id)->toBe($user->id);
    expect($log->action_type)->toBe('create');
    expect($log->entity_type)->toBe('product');
    expect($log->entity_id)->toBe(1);
    expect($log->module)->toBe('products');
    expect($log->description)->toBe('Product #1 created');
});

test('log creates entry with metadata', function (): void {
    $log = $this->service->log(
        actionType: 'update',
        entityType: 'ticket',
        entityId: 5,
        module: 'tickets',
        description: 'Ticket #5 updated',
        metadata: ['field' => 'status', 'old' => 'open', 'new' => 'closed'],
    );

    expect($log->metadata)->toBe(['field' => 'status', 'old' => 'open', 'new' => 'closed']);
});

test('log sanitizes sensitive fields from metadata', function (): void {
    $log = $this->service->log(
        actionType: 'update',
        entityType: 'user',
        entityId: 1,
        module: 'users',
        description: 'User updated',
        metadata: ['name' => 'John', 'password' => 'secret123', 'token' => 'abc'],
    );

    expect($log->metadata)->toBe(['name' => 'John']);
    expect($log->metadata)->not->toHaveKey('password');
    expect($log->metadata)->not->toHaveKey('token');
});

test('logModelCreated logs create action for a model', function (): void {
    $user = User::factory()->create();
    auth('api')->login($user);

    $product = Product::factory()->create();

    $log = $this->service->logModelCreated($product);

    expect($log->action_type)->toBe('create');
    expect($log->entity_type)->toBe('product');
    expect($log->entity_id)->toBe($product->id);
    expect($log->module)->toBe('products');
    expect($log->description)->toContain('created');
});

test('logModelUpdated logs update action with changes', function (): void {
    $user = User::factory()->create();
    auth('api')->login($user);

    $ticket = Ticket::factory()->create();

    $log = $this->service->logModelUpdated($ticket, ['status' => 'closed']);

    expect($log->action_type)->toBe('update');
    expect($log->entity_type)->toBe('ticket');
    expect($log->entity_id)->toBe($ticket->id);
    expect($log->module)->toBe('tickets');
    expect($log->metadata['changes'])->toBe(['status' => 'closed']);
});

test('logModelDeleted logs delete action', function (): void {
    $user = User::factory()->create();
    auth('api')->login($user);

    $ticket = Ticket::factory()->create();
    $ticketId = $ticket->id;

    $log = $this->service->logModelDeleted($ticket);

    expect($log->action_type)->toBe('delete');
    expect($log->entity_type)->toBe('ticket');
    expect($log->entity_id)->toBe($ticketId);
    expect($log->module)->toBe('tickets');
    expect($log->description)->toContain('deleted');
});

test('logStatusChange logs status change with old and new values', function (): void {
    $user = User::factory()->create();
    auth('api')->login($user);

    $ticket = Ticket::factory()->open()->create();

    $log = $this->service->logStatusChange($ticket, 'open', 'closed');

    expect($log->action_type)->toBe('status_change');
    expect($log->entity_type)->toBe('ticket');
    expect($log->metadata)->toBe([
        'old_status' => 'open',
        'new_status' => 'closed',
    ]);
    expect($log->description)->toContain('open');
    expect($log->description)->toContain('closed');
});

test('logLogin logs login event', function (): void {
    $user = User::factory()->create();

    $log = $this->service->logLogin($user->id);

    expect($log->action_type)->toBe('login');
    expect($log->entity_type)->toBe('user');
    expect($log->entity_id)->toBe($user->id);
    expect($log->user_id)->toBe($user->id);
    expect($log->module)->toBe('users');
});

test('logLogout logs logout event', function (): void {
    $user = User::factory()->create();

    $log = $this->service->logLogout($user->id);

    expect($log->action_type)->toBe('logout');
    expect($log->entity_type)->toBe('user');
    expect($log->entity_id)->toBe($user->id);
    expect($log->user_id)->toBe($user->id);
    expect($log->module)->toBe('users');
});

test('log with null user_id for system actions', function (): void {
    $log = $this->service->log(
        actionType: 'create',
        entityType: 'product',
        entityId: 1,
        module: 'products',
        description: 'System action',
        userId: null,
    );

    expect($log->user_id)->toBeNull();
});

test('logModelCreated with custom description', function (): void {
    $user = User::factory()->create();
    auth('api')->login($user);

    $product = Product::factory()->create();

    $log = $this->service->logModelCreated($product, 'Custom product created');

    expect($log->description)->toBe('Custom product created');
});

test('sanitizes nested sensitive fields in metadata', function (): void {
    $log = $this->service->log(
        actionType: 'update',
        entityType: 'user',
        entityId: 1,
        module: 'users',
        description: 'User updated',
        metadata: [
            'user' => [
                'name' => 'John',
                'password' => 'secret',
                'api_key' => 'key123',
            ],
        ],
    );

    expect($log->metadata['user'])->toBe(['name' => 'John']);
});
