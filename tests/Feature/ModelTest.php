<?php

use App\Models\Branch;
use App\Models\Recall;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;
use Filament\Panel;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(BranchSeeder::class);
});

describe('User Model', function (): void {
    test('user has branch relationship', function (): void {
        $branch = Branch::first();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        expect($user->branch)->toBeInstanceOf(Branch::class)
            ->and($user->branch->id)->toBe($branch->id);
    });

    test('user casts are properly defined', function (): void {
        $user = User::factory()->create([
            'email_verified_at' => '2024-01-01 10:00:00',
        ]);

        expect($user->email_verified_at)->toBeInstanceOf(DateTime::class)
            ->and($user->password)->toBeString();
    });

    test('user can access panel', function (): void {
        $user = User::factory()->create();

        $panel = app(Panel::class);

        expect($user->canAccessPanel($panel))->toBeTrue();
    });
});

describe('Branch Model', function (): void {
    test('branch has users relationship', function (): void {
        $branch = Branch::first();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        expect($branch->users)->toHaveCount(1)
            ->and($branch->users->first()->id)->toBe($user->id);
    });

    test('branch casts is_active to boolean', function (): void {
        $branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TB-001',
            'location' => 'Test Location',
            'is_active' => '1',
        ]);

        expect($branch->is_active)->toBeTrue()
            ->and($branch->is_active)->toBeBool();
    });
});

describe('Recall Model', function (): void {
    test('recall has branch relationship', function (): void {
        $branch = Branch::first();
        $user = User::factory()->create();

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test reason',
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        expect($recall->branch)->toBeInstanceOf(Branch::class)
            ->and($recall->branch->id)->toBe($branch->id);
    });

    test('recall has creator relationship', function (): void {
        $user = User::factory()->create();
        $branch = Branch::first();

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test reason',
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        expect($recall->creator)->toBeInstanceOf(User::class)
            ->and($recall->creator->id)->toBe($user->id);
    });

    test('recall has approver relationship', function (): void {
        $creator = User::factory()->create();
        $approver = User::factory()->create();
        $branch = Branch::first();

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test reason',
            'branch_id' => $branch->id,
            'created_by' => $creator->id,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        expect($recall->approver)->toBeInstanceOf(User::class)
            ->and($recall->approver->id)->toBe($approver->id);
    });

    test('recall casts approved_at to datetime', function (): void {
        $user = User::factory()->create();
        $branch = Branch::first();

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test reason',
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'approved_at' => '2024-01-01 10:00:00',
        ]);

        expect($recall->approved_at)->toBeInstanceOf(DateTime::class);
    });
});
