<?php

declare(strict_types=1);

use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Support\Carbon;

test('action log has correct fillable attributes', function (): void {
    $user = User::factory()->create();

    $data = [
        'user_id' => $user->id,
        'action_type' => 'create',
        'entity_type' => 'product',
        'entity_id' => 1,
        'module' => 'products',
        'description' => 'Product #1 created',
    ];

    $log = ActionLog::create($data);

    expect($log->user_id)->toBe($user->id);
    expect($log->action_type)->toBe('create');
    expect($log->entity_type)->toBe('product');
    expect($log->entity_id)->toBe(1);
    expect($log->module)->toBe('products');
    expect($log->description)->toBe('Product #1 created');
});

test('action log belongs to user', function (): void {
    $user = User::factory()->create(['name' => 'John Doe']);
    $log = ActionLog::factory()->create(['user_id' => $user->id]);

    expect($log->user)->toBeInstanceOf(User::class);
    expect($log->user->name)->toBe('John Doe');
});

test('action log can have null user for system actions', function (): void {
    $log = ActionLog::factory()->systemAction()->create();

    expect($log->user_id)->toBeNull();
    expect($log->user)->toBeNull();
});

test('action log casts metadata to array', function (): void {
    $log = ActionLog::factory()->withMetadata([
        'old_status' => 'open',
        'new_status' => 'closed',
    ])->create();

    expect($log->metadata)->toBeArray();
    expect($log->metadata['old_status'])->toBe('open');
    expect($log->metadata['new_status'])->toBe('closed');
});

test('action log casts created_at to datetime', function (): void {
    $log = ActionLog::factory()->create();

    expect($log->created_at)->toBeInstanceOf(Carbon::class);
});

test('scopeOrdered returns logs in descending created_at order', function (): void {
    $old = ActionLog::factory()->create(['created_at' => now()->subDays(2)]);
    $new = ActionLog::factory()->create(['created_at' => now()]);
    $mid = ActionLog::factory()->create(['created_at' => now()->subDay()]);

    $logs = ActionLog::ordered()->get();

    expect($logs[0]->id)->toBe($new->id);
    expect($logs[1]->id)->toBe($mid->id);
    expect($logs[2]->id)->toBe($old->id);
});

test('scopeForUser filters logs by user', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    ActionLog::factory()->count(3)->create(['user_id' => $user1->id]);
    ActionLog::factory()->count(5)->create(['user_id' => $user2->id]);

    expect(ActionLog::forUser($user1->id)->count())->toBe(3);
    expect(ActionLog::forUser($user2->id)->count())->toBe(5);
});

test('scopeActionType filters logs by action type', function (): void {
    ActionLog::factory()->count(3)->forActionType('create')->create();
    ActionLog::factory()->count(2)->forActionType('update')->create();

    expect(ActionLog::actionType('create')->count())->toBe(3);
    expect(ActionLog::actionType('update')->count())->toBe(2);
});

test('scopeEntityType filters logs by entity type', function (): void {
    ActionLog::factory()->count(4)->forEntityType('product')->create();
    ActionLog::factory()->count(2)->forEntityType('ticket')->create();

    expect(ActionLog::entityType('product')->count())->toBe(4);
    expect(ActionLog::entityType('ticket')->count())->toBe(2);
});

test('scopeModule filters logs by module', function (): void {
    ActionLog::factory()->count(3)->forModule('products')->create();
    ActionLog::factory()->count(4)->forModule('tickets')->create();

    expect(ActionLog::module('products')->count())->toBe(3);
    expect(ActionLog::module('tickets')->count())->toBe(4);
});

test('scopeDateFrom filters logs from given date', function (): void {
    ActionLog::factory()->count(2)->create(['created_at' => '2026-01-15 10:00:00']);
    ActionLog::factory()->count(3)->create(['created_at' => '2026-03-15 10:00:00']);

    expect(ActionLog::dateFrom('2026-03-01')->count())->toBe(3);
});

test('scopeDateTo filters logs until given date', function (): void {
    ActionLog::factory()->count(2)->create(['created_at' => '2026-01-15 10:00:00']);
    ActionLog::factory()->count(3)->create(['created_at' => '2026-03-15 10:00:00']);

    expect(ActionLog::dateTo('2026-02-01')->count())->toBe(2);
});

test('factory creates valid action log', function (): void {
    $log = ActionLog::factory()->create();

    expect($log)->toBeInstanceOf(ActionLog::class);
    expect($log->exists)->toBeTrue();
    expect($log->action_type)->toBeString();
    expect($log->entity_type)->toBeString();
    expect($log->module)->toBeString();
    expect($log->description)->toBeString();
});

test('factory forModule state works', function (): void {
    $log = ActionLog::factory()->forModule('tickets')->create();

    expect($log->module)->toBe('tickets');
});

test('factory forActionType state works', function (): void {
    $log = ActionLog::factory()->forActionType('delete')->create();

    expect($log->action_type)->toBe('delete');
});

test('factory withMetadata state works', function (): void {
    $log = ActionLog::factory()->withMetadata(['key' => 'value'])->create();

    expect($log->metadata)->toBe(['key' => 'value']);
});

test('action log does not have updated_at timestamp', function (): void {
    $log = ActionLog::factory()->create();

    expect($log->updated_at)->toBeNull();
});
