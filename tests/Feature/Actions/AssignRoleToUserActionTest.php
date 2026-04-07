<?php

declare(strict_types=1);

use App\Actions\User\AssignRoleToUserAction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

describe('AssignRoleToUserAction', function (): void {
    beforeEach(function (): void {
        $this->seed(RolePermissionSeeder::class);
        $this->action = app(AssignRoleToUserAction::class);
    });

    test('assigns role to user', function (): void {
        $user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $result = $this->action->execute($user, 'Auditor');

        expect($result->hasRole('Auditor'))->toBeTrue()
            ->and($result->relationLoaded('roles'))->toBeTrue();
    });

    test('can assign multiple roles to same user', function (): void {
        $user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $this->action->execute($user, 'Auditor');
        $result = $this->action->execute($user, 'Warehouse Operator');

        expect($result->hasRole('Auditor'))->toBeTrue()
            ->and($result->hasRole('Warehouse Operator'))->toBeTrue();
    });

    test('returns user with loaded roles', function (): void {
        $user = User::factory()->create([
            'email' => 'test@nikora.ge',
            'password' => Hash::make('password'),
        ]);

        $result = $this->action->execute($user, 'Quality Manager');

        expect($result->relationLoaded('roles'))->toBeTrue()
            ->and($result->roles->pluck('name')->toArray())->toContain('Quality Manager');
    });
});
