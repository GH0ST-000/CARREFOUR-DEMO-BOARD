<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

describe('API Role Management', function (): void {
    beforeEach(function (): void {
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('Recall Admin');

        $this->qualityManager = User::factory()->create([
            'email' => 'quality@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $this->qualityManager->assignRole('Quality Manager');
    });

    test('admin can view all roles', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'guard_name',
                        'permissions',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $roleNames = collect($response->json('data'))->pluck('name')->toArray();

        expect($roleNames)->toContain('Recall Admin')
            ->and($roleNames)->toContain('Quality Manager')
            ->and($roleNames)->toContain('Branch Manager')
            ->and($roleNames)->toContain('Warehouse Operator')
            ->and($roleNames)->toContain('Auditor');
    });

    test('admin can view specific role with permissions', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');
        $role = Role::where('name', 'Quality Manager')->first();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'guard_name',
                    'permissions' => [
                        '*' => [
                            'name',
                            'guard_name',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'Quality Manager',
                ],
            ]);

        $permissions = collect($response->json('data.permissions'))->pluck('name')->toArray();
        expect($permissions)->toContain('approve_recall');
    });

    test('non-admin cannot view roles', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'quality@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/roles');

        $response->assertStatus(403);
    });

    test('unauthenticated user cannot view roles', function (): void {
        $response = $this->getJson('/api/roles');

        $response->assertStatus(401);
    });

    test('admin can assign role to user', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'newuser@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/users/{$targetUser->id}/roles", [
                'role' => 'Auditor',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                ],
            ]);

        expect($response->json('data.roles'))->toContain('Auditor');

        $targetUser->refresh();
        expect($targetUser->hasRole('Auditor'))->toBeTrue();
    });

    test('admin can remove role from user', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $targetUser->assignRole('Auditor');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/users/{$targetUser->id}/roles/Auditor");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Role removed successfully',
            ]);

        $targetUser->refresh();
        expect($targetUser->hasRole('Auditor'))->toBeFalse();
    });

    test('non-admin cannot assign roles', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'newuser@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'quality@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/users/{$targetUser->id}/roles", [
                'role' => 'Auditor',
            ]);

        $response->assertStatus(403);
    });

    test('non-admin cannot remove roles', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $targetUser->assignRole('Auditor');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'quality@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/users/{$targetUser->id}/roles/Auditor");

        $response->assertStatus(403);
    });

    test('assigning role validates role exists', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'newuser@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/users/{$targetUser->id}/roles", [
                'role' => 'NonExistentRole',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    });

    test('assigning role requires role field', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'newuser@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/users/{$targetUser->id}/roles", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    });

    test('admin can assign multiple roles to same user', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'newuser@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/users/{$targetUser->id}/roles", ['role' => 'Auditor']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/users/{$targetUser->id}/roles", ['role' => 'Warehouse Operator']);

        $targetUser->refresh();

        expect($targetUser->hasRole('Auditor'))->toBeTrue()
            ->and($targetUser->hasRole('Warehouse Operator'))->toBeTrue();
    });

    test('role response includes all permissions', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');
        $role = Role::where('name', 'Quality Manager')->first();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/roles/{$role->id}");

        $response->assertStatus(200);

        $permissions = collect($response->json('data.permissions'))->pluck('name')->toArray();

        expect($permissions)->toContain('approve_recall')
            ->and($permissions)->toContain('view_any_user')
            ->and(count($permissions))->toBeGreaterThan(10);
    });

    test('removing non-existent role does not cause error', function (): void {
        $targetUser = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/users/{$targetUser->id}/roles/NonExistentRole");

        $response->assertStatus(200);
    });
});
