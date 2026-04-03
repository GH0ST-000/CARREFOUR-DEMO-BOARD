<?php

use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

describe('ManufacturerPolicy', function (): void {
    beforeEach(function (): void {
        $this->seed(RolePermissionSeeder::class);
    });

    test('recall admin can view any manufacturers', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('viewAny', Manufacturer::class))->toBeTrue();
    });

    test('recall admin can view manufacturer', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');
        $manufacturer = Manufacturer::factory()->create();

        expect($admin->can('view', $manufacturer))->toBeTrue();
    });

    test('recall admin can create manufacturer', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('create', Manufacturer::class))->toBeTrue();
    });

    test('recall admin can update manufacturer', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');
        $manufacturer = Manufacturer::factory()->create();

        expect($admin->can('update', $manufacturer))->toBeTrue();
    });

    test('recall admin can delete manufacturer', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');
        $manufacturer = Manufacturer::factory()->create();

        expect($admin->can('delete', $manufacturer))->toBeTrue();
    });

    test('recall admin can restore manufacturer', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');
        $manufacturer = Manufacturer::factory()->create();

        expect($admin->can('restore', $manufacturer))->toBeTrue();
    });

    test('recall admin can force delete manufacturer', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');
        $manufacturer = Manufacturer::factory()->create();

        expect($admin->can('forceDelete', $manufacturer))->toBeTrue();
    });

    test('quality manager cannot view any manufacturers', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Quality Manager');

        expect($manager->can('viewAny', Manufacturer::class))->toBeFalse();
    });

    test('quality manager cannot create manufacturer', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Quality Manager');

        expect($manager->can('create', Manufacturer::class))->toBeFalse();
    });

    test('branch manager cannot access manufacturers', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Branch Manager');
        $manufacturer = Manufacturer::factory()->create();

        expect($manager->can('viewAny', Manufacturer::class))->toBeFalse()
            ->and($manager->can('view', $manufacturer))->toBeFalse()
            ->and($manager->can('create', Manufacturer::class))->toBeFalse();
    });
});
