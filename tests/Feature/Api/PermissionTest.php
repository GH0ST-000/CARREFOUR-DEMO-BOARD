<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

describe('API Permissions', function (): void {
    beforeEach(function (): void {
        $this->seed(RolePermissionSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);
    });

    test('authenticated user can get their permissions', function (): void {
        $this->user->assignRole('Quality Manager');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'guard_name',
                    ],
                ],
            ]);

        $permissionNames = collect($response->json('data'))->pluck('name')->toArray();

        expect($permissionNames)->toContain('view_any_user')
            ->and($permissionNames)->toContain('approve_recall')
            ->and($permissionNames)->not->toContain('create_user');
    });

    test('recall admin has all permissions', function (): void {
        $this->user->assignRole('Recall Admin');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/permissions');

        $response->assertStatus(200);

        $permissionCount = count($response->json('data'));
        expect($permissionCount)->toBeGreaterThan(50);
    });

    test('warehouse operator has limited permissions', function (): void {
        $this->user->assignRole('Warehouse Operator');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/permissions');

        $response->assertStatus(200);

        $permissionNames = collect($response->json('data'))->pluck('name')->toArray();

        expect($permissionNames)->toContain('view_inventory')
            ->and($permissionNames)->toContain('create_inventory')
            ->and($permissionNames)->not->toContain('delete_user')
            ->and($permissionNames)->not->toContain('approve_recall');
    });

    test('unauthenticated user cannot get permissions', function (): void {
        $response = $this->getJson('/api/permissions');

        $response->assertStatus(401);
    });

    test('permissions endpoint returns all user permissions including role permissions', function (): void {
        $this->user->assignRole('Auditor');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/permissions');

        $response->assertStatus(200);

        $permissionNames = collect($response->json('data'))->pluck('name')->toArray();

        expect($permissionNames)->toContain('view_any_audit')
            ->and($permissionNames)->toContain('view_audit')
            ->and($permissionNames)->toContain('view_any_recall');
    });

    test('branch manager permissions are branch-scoped', function (): void {
        $this->seed(BranchSeeder::class);

        $branchManager = User::factory()->create([
            'email' => 'branch-manager@nikora.ge',
            'password' => Hash::make('password'),
            'branch_id' => 1,
        ]);
        $branchManager->assignRole('Branch Manager');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'branch-manager@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/permissions');

        $response->assertStatus(200);

        $permissionNames = collect($response->json('data'))->pluck('name')->toArray();

        expect($permissionNames)->toContain('view_own_branch_user')
            ->and($permissionNames)->toContain('view_own_branch_inventory')
            ->and($permissionNames)->not->toContain('view_any_user');
    });

    test('permissions response includes guard name', function (): void {
        $this->user->assignRole('Auditor');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/permissions');

        $response->assertStatus(200);

        $firstPermission = $response->json('data.0');
        expect($firstPermission)->toHaveKey('guard_name')
            ->and($firstPermission['guard_name'])->toBe('web');
    });
});
