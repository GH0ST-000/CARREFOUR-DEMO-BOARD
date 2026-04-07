<?php

declare(strict_types=1);

use App\Models\ActionLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
        'view_any_action_log',
        'view_action_log',
    ]);
});

test('can list action logs with cursor pagination', function (): void {
    ActionLog::factory()->count(30)->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs?per_page=10');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'action_type',
                    'entity_type',
                    'entity_id',
                    'module',
                    'description',
                    'metadata',
                    'created_at',
                ],
            ],
            'meta' => [
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ],
        ])
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->has('data', 10)
            ->where('meta.per_page', 10)
            ->etc()
        );
});

test('can filter action logs by user_id', function (): void {
    $otherUser = User::factory()->create();

    ActionLog::factory()->count(3)->create(['user_id' => $this->user->id]);
    ActionLog::factory()->count(5)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/action-logs?user_id={$this->user->id}");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can filter action logs by action_type', function (): void {
    ActionLog::factory()->count(3)->forActionType('create')->create();
    ActionLog::factory()->count(2)->forActionType('update')->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs?action_type=create');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can filter action logs by entity_type', function (): void {
    ActionLog::factory()->count(4)->forEntityType('product')->create();
    ActionLog::factory()->count(2)->forEntityType('ticket')->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs?entity_type=product');

    $response->assertOk()
        ->assertJsonCount(4, 'data');
});

test('can filter action logs by module', function (): void {
    ActionLog::factory()->count(3)->forModule('products')->create();
    ActionLog::factory()->count(4)->forModule('tickets')->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs?module=products');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can filter action logs by date range', function (): void {
    ActionLog::factory()->count(2)->create(['created_at' => '2026-01-15 10:00:00']);
    ActionLog::factory()->count(3)->create(['created_at' => '2026-03-15 10:00:00']);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs?date_from=2026-03-01&date_to=2026-04-01');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can combine multiple filters', function (): void {
    ActionLog::factory()->count(2)->forModule('products')->forActionType('create')->create();
    ActionLog::factory()->count(3)->forModule('products')->forActionType('update')->create();
    ActionLog::factory()->count(4)->forModule('tickets')->forActionType('create')->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs?module=products&action_type=create');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('can view a single action log', function (): void {
    $log = ActionLog::factory()->create([
        'user_id' => $this->user->id,
        'action_type' => 'create',
        'entity_type' => 'product',
        'entity_id' => 1,
        'module' => 'products',
        'description' => 'Product #1 created',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/action-logs/{$log->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $log->id,
                'action_type' => 'create',
                'entity_type' => 'product',
                'entity_id' => 1,
                'module' => 'products',
                'description' => 'Product #1 created',
            ],
        ]);
});

test('action log show includes user relationship', function (): void {
    $log = ActionLog::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/action-logs/{$log->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
});

test('can search action logs', function (): void {
    ActionLog::factory()->create([
        'description' => 'Product #10 created',
        'module' => 'products',
    ]);
    ActionLog::factory()->create([
        'description' => 'Ticket #5 updated',
        'module' => 'tickets',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs/search?q=Product');

    $response->assertOk();

    $data = $response->json('data');
    expect(collect($data)->pluck('description')->filter(fn ($d): bool => str_contains($d, 'Product'))->count())->toBeGreaterThanOrEqual(1);
});

test('can search with action_type filter', function (): void {
    ActionLog::factory()->create([
        'description' => 'Product #1 created',
        'module' => 'products',
        'action_type' => 'create',
    ]);
    ActionLog::factory()->create([
        'description' => 'Product #2 updated',
        'module' => 'products',
        'action_type' => 'update',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs/search?q=Product&action_type=create');

    $response->assertOk();
});

test('can search with user_id filter', function (): void {
    ActionLog::factory()->create([
        'description' => 'Product #1 created',
        'user_id' => $this->user->id,
    ]);
    $otherUser = User::factory()->create();
    ActionLog::factory()->create([
        'description' => 'Product #2 created',
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/action-logs/search?q=Product&user_id={$this->user->id}");

    $response->assertOk();
});

test('can search with module filter', function (): void {
    ActionLog::factory()->create([
        'description' => 'Product #1 created',
        'module' => 'products',
    ]);
    ActionLog::factory()->create([
        'description' => 'Ticket #1 created',
        'module' => 'tickets',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs/search?q=created&module=products');

    $response->assertOk();
});

test('cannot access action logs without authentication', function (): void {
    $response = $this->getJson('/api/action-logs');
    $response->assertUnauthorized();
});

test('cannot access action logs without permission', function (): void {
    $userWithoutPermission = User::factory()->create();

    $response = $this->actingAs($userWithoutPermission, 'api')
        ->getJson('/api/action-logs');

    $response->assertForbidden();
});

test('cannot view single action log without permission', function (): void {
    $userWithoutPermission = User::factory()->create();
    $log = ActionLog::factory()->create();

    $response = $this->actingAs($userWithoutPermission, 'api')
        ->getJson("/api/action-logs/{$log->id}");

    $response->assertForbidden();
});

test('cannot search action logs without permission', function (): void {
    $userWithoutPermission = User::factory()->create();

    $response = $this->actingAs($userWithoutPermission, 'api')
        ->getJson('/api/action-logs/search?q=test');

    $response->assertForbidden();
});

test('per_page is clamped between 1 and 100', function (): void {
    ActionLog::factory()->count(150)->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs?per_page=200');

    $response->assertOk()
        ->assertJsonCount(100, 'data')
        ->assertJson(['meta' => ['per_page' => 100]]);
});

test('action logs are returned in descending order', function (): void {
    $old = ActionLog::factory()->create(['created_at' => now()->subDays(2)]);
    $new = ActionLog::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/action-logs');

    $response->assertOk();

    $data = $response->json('data');
    expect($data[0]['id'])->toBe($new->id);
    expect($data[1]['id'])->toBe($old->id);
});

test('action log with metadata displays correctly', function (): void {
    $log = ActionLog::factory()->withMetadata([
        'old_status' => 'open',
        'new_status' => 'closed',
    ])->create();

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/action-logs/{$log->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'metadata' => [
                    'old_status' => 'open',
                    'new_status' => 'closed',
                ],
            ],
        ]);
});
