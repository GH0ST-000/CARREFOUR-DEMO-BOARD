<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

describe('API Authentication', function (): void {
    beforeEach(function (): void {
        $this->seed(RolePermissionSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $this->user->assignRole('Recall Admin');
    });

    test('user can login with valid credentials', function (): void {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);

        expect($response->json('access_token'))->toBeString();
    });

    test('user cannot login with invalid email', function (): void {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'wrong@nikora.ge',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('user cannot login with invalid password', function (): void {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('login requires email field', function (): void {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('login requires password field', function (): void {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('email must be valid email format', function (): void {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('user can refresh token', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);

        expect($response->json('access_token'))->toBeString()
            ->and($response->json('access_token'))->not->toBe($token);
    });

    test('refresh requires valid token', function (): void {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401);
    });

    test('refresh rejects invalid token', function (): void {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->postJson('/api/auth/refresh');

        $response->assertStatus(401);
    });

    test('user can logout', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);
    });

    test('logout requires authentication', function (): void {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    });

    test('token cannot be used after logout', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertStatus(401);
    });

    test('user can get their profile', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'branch_id',
                    'roles',
                    'permissions',
                ],
            ])
            ->assertJson([
                'data' => [
                    'email' => 'test@nikora.ge',
                ],
            ]);

        expect($response->json('data.roles'))->toContain('Recall Admin');
    });

    test('me endpoint requires authentication', function (): void {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    });

    test('user profile includes branch information', function (): void {
        $this->seed(BranchSeeder::class);

        $userWithBranch = User::factory()->create([
            'email' => 'branch-user@nikora.ge',
            'password' => Hash::make('password'),
            'branch_id' => 1,
        ]);
        $userWithBranch->assignRole('Recall Admin');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'branch-user@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertStatus(200);
        expect($response->json('data.branch_id'))->toBe(1);
    });
});
