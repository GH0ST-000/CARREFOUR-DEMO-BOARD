<?php

use App\Actions\User\RemoveRoleFromUserAction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

describe('RemoveRoleFromUserAction', function (): void {
    beforeEach(function (): void {
        $this->seed(RolePermissionSeeder::class);
        $this->action = new RemoveRoleFromUserAction;
    });

    test('removes role from user', function (): void {
        $user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Auditor');

        $result = $this->action->execute($user, 'Auditor');

        expect($result->hasRole('Auditor'))->toBeFalse()
            ->and($result->relationLoaded('roles'))->toBeTrue();
    });

    test('removing non-existent role does not cause error', function (): void {
        $user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $result = $this->action->execute($user, 'NonExistentRole');

        expect($result)->toBeInstanceOf(User::class)
            ->and($result->relationLoaded('roles'))->toBeTrue();
    });

    test('can remove one role while keeping others', function (): void {
        $user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole(['Auditor', 'Warehouse Operator']);

        $result = $this->action->execute($user, 'Auditor');

        expect($result->hasRole('Auditor'))->toBeFalse()
            ->and($result->hasRole('Warehouse Operator'))->toBeTrue();
    });

    test('returns user with loaded roles', function (): void {
        $user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole(['Quality Manager', 'Auditor']);

        $result = $this->action->execute($user, 'Auditor');

        expect($result->relationLoaded('roles'))->toBeTrue()
            ->and($result->roles->pluck('name')->toArray())->toContain('Quality Manager')
            ->and($result->roles->pluck('name')->toArray())->not->toContain('Auditor');
    });
});
