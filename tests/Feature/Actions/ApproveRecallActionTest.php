<?php

declare(strict_types=1);

use App\Actions\Recall\ApproveRecallAction;
use App\Models\Branch;
use App\Models\Recall;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(BranchSeeder::class);
});

test('approve recall action updates recall with approved status', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Recall Admin');

    $recall = Recall::create([
        'product_name' => 'Test Product',
        'batch_number' => 'BATCH-001',
        'reason' => 'Quality issue',
        'status' => 'pending',
        'branch_id' => Branch::first()->id,
        'created_by' => $admin->id,
    ]);

    $action = new ApproveRecallAction;
    $action->execute($recall, $admin, 'approved');

    $recall->refresh();

    expect($recall->status)->toBe('approved')
        ->and($recall->approved_by)->toBe($admin->id)
        ->and($recall->approved_at)->not->toBeNull()
        ->and($recall->approved_at)->toBeInstanceOf(Carbon::class);
});

test('approve recall action updates recall with rejected status', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole('Quality Manager');

    $recall = Recall::create([
        'product_name' => 'Test Product',
        'batch_number' => 'BATCH-002',
        'reason' => 'Safety concern',
        'status' => 'pending',
        'branch_id' => Branch::first()->id,
        'created_by' => $manager->id,
    ]);

    $action = new ApproveRecallAction;
    $action->execute($recall, $manager, 'rejected');

    $recall->refresh();

    expect($recall->status)->toBe('rejected')
        ->and($recall->approved_by)->toBe($manager->id)
        ->and($recall->approved_at)->not->toBeNull();
});

test('approve recall action can mark recall as completed', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Recall Admin');

    $recall = Recall::create([
        'product_name' => 'Test Product',
        'batch_number' => 'BATCH-003',
        'reason' => 'Completed recall',
        'status' => 'approved',
        'branch_id' => Branch::first()->id,
        'created_by' => $admin->id,
        'approved_by' => $admin->id,
        'approved_at' => now()->subDay(),
    ]);

    $action = new ApproveRecallAction;
    $action->execute($recall, $admin, 'completed');

    $recall->refresh();

    expect($recall->status)->toBe('completed');
});
