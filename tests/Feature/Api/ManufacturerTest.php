<?php

use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

describe('API Manufacturers', function (): void {
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

    test('admin can list manufacturers', function (): void {
        Manufacturer::factory()->count(3)->create();

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/manufacturers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'full_name',
                        'short_name',
                        'legal_form',
                        'identification_number',
                        'legal_address',
                        'phone',
                        'email',
                        'country',
                        'region',
                        'city',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta',
                'links',
            ]);

        expect(count($response->json('data')))->toBe(3);
    });

    test('admin can create manufacturer', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $manufacturerData = [
            'full_name' => 'Test Manufacturer LLC',
            'short_name' => 'TestMfg',
            'legal_form' => 'Limited Liability Company',
            'identification_number' => '123456789',
            'legal_address' => '123 Test Street, Test City',
            'phone' => '+995-555-123456',
            'email' => 'contact@testmfg.ge',
            'country' => 'Georgia',
            'region' => 'Tbilisi',
            'city' => 'Tbilisi',
            'is_active' => true,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/manufacturers', $manufacturerData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'full_name' => 'Test Manufacturer LLC',
                    'short_name' => 'TestMfg',
                    'identification_number' => '123456789',
                ],
            ]);

        $this->assertDatabaseHas('manufacturers', [
            'full_name' => 'Test Manufacturer LLC',
            'identification_number' => '123456789',
        ]);
    });

    test('admin can view specific manufacturer', function (): void {
        $manufacturer = Manufacturer::factory()->create();

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/manufacturers/{$manufacturer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $manufacturer->id,
                    'full_name' => $manufacturer->full_name,
                ],
            ]);
    });

    test('admin can update manufacturer', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'full_name' => 'Original Name',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/manufacturers/{$manufacturer->id}", [
                'full_name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'full_name' => 'Updated Name',
                ],
            ]);

        $this->assertDatabaseHas('manufacturers', [
            'id' => $manufacturer->id,
            'full_name' => 'Updated Name',
        ]);
    });

    test('admin can delete manufacturer', function (): void {
        $manufacturer = Manufacturer::factory()->create();

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/manufacturers/{$manufacturer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Manufacturer deleted successfully',
            ]);

        $this->assertDatabaseMissing('manufacturers', [
            'id' => $manufacturer->id,
        ]);
    });

    test('non-admin cannot create manufacturer', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'quality@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/manufacturers', [
                'full_name' => 'Test Manufacturer',
                'legal_form' => 'LLC',
                'identification_number' => '999999',
                'legal_address' => 'Test Address',
                'phone' => '+995-555-000000',
                'email' => 'test@test.ge',
                'country' => 'Georgia',
                'region' => 'Tbilisi',
            ]);

        $response->assertStatus(403);
    });

    test('unauthenticated user cannot access manufacturers', function (): void {
        $response = $this->getJson('/api/manufacturers');

        $response->assertStatus(401);
    });

    test('create manufacturer validates required fields', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/manufacturers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'full_name',
                'legal_form',
                'identification_number',
                'legal_address',
                'phone',
                'email',
                'country',
                'region',
            ]);
    });

    test('create manufacturer validates email format', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/manufacturers', [
                'full_name' => 'Test',
                'legal_form' => 'LLC',
                'identification_number' => '123',
                'legal_address' => 'Address',
                'phone' => '123',
                'email' => 'invalid-email',
                'country' => 'Georgia',
                'region' => 'Tbilisi',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('create manufacturer validates unique identification number', function (): void {
        Manufacturer::factory()->create([
            'identification_number' => 'DUPLICATE123',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/manufacturers', [
                'full_name' => 'Test Manufacturer',
                'legal_form' => 'LLC',
                'identification_number' => 'DUPLICATE123',
                'legal_address' => 'Test Address',
                'phone' => '+995-555-123456',
                'email' => 'test@test.ge',
                'country' => 'Georgia',
                'region' => 'Tbilisi',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identification_number']);
    });

    test('create manufacturer trims string fields', function (): void {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/manufacturers', [
                'full_name' => '  Test Manufacturer  ',
                'short_name' => '  TestMfg  ',
                'legal_form' => '  LLC  ',
                'identification_number' => '  123456  ',
                'legal_address' => '  Address  ',
                'phone' => '  +995-555-123456  ',
                'email' => '  test@test.ge  ',
                'country' => '  Georgia  ',
                'region' => '  Tbilisi  ',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('manufacturers', [
            'full_name' => 'Test Manufacturer',
            'short_name' => 'TestMfg',
            'legal_form' => 'LLC',
            'identification_number' => '123456',
        ]);
    });

    test('update manufacturer validates unique identification number', function (): void {
        $manufacturer1 = Manufacturer::factory()->create([
            'identification_number' => 'EXISTING123',
        ]);

        $manufacturer2 = Manufacturer::factory()->create([
            'identification_number' => 'OTHER456',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/manufacturers/{$manufacturer2->id}", [
                'identification_number' => 'EXISTING123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identification_number']);
    });

    test('update manufacturer allows partial updates with all fields', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'full_name' => 'Original',
            'short_name' => 'Orig',
            'legal_form' => 'LLC',
            'identification_number' => 'ORIGINAL123',
            'legal_address' => 'Old Address',
            'phone' => '+995-555-000000',
            'email' => 'old@test.ge',
            'country' => 'Georgia',
            'region' => 'Tbilisi',
            'city' => 'Tbilisi',
            'is_active' => true,
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/manufacturers/{$manufacturer->id}", [
                'full_name' => 'Updated Full Name',
                'short_name' => 'Updated',
                'legal_form' => 'JSC',
                'identification_number' => 'UPDATED123',
                'legal_address' => 'New Address',
                'phone' => '+995-555-999999',
                'email' => 'new@test.ge',
                'country' => 'Armenia',
                'region' => 'Yerevan',
                'city' => 'Yerevan',
                'is_active' => false,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('manufacturers', [
            'id' => $manufacturer->id,
            'full_name' => 'Updated Full Name',
            'short_name' => 'Updated',
            'legal_form' => 'JSC',
            'identification_number' => 'UPDATED123',
            'legal_address' => 'New Address',
            'phone' => '+995-555-999999',
            'email' => 'new@test.ge',
            'country' => 'Armenia',
            'region' => 'Yerevan',
            'city' => 'Yerevan',
            'is_active' => false,
        ]);
    });

    test('manufacturer list uses cursor pagination', function (): void {
        Manufacturer::factory()->count(30)->create();

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@nikora.ge',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/manufacturers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['next_cursor', 'prev_cursor'],
                'links' => ['next', 'prev'],
            ]);

        expect(count($response->json('data')))->toBe(25);
        expect($response->json('meta.next_cursor'))->not->toBeNull();
    });
});
