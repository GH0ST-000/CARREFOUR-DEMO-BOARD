<?php

use App\Actions\Recall\ApproveRecallAction;
use App\Models\Branch;
use App\Models\Recall;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(BranchSeeder::class);
});

test('quality manager can approve recalls', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole('Quality Manager');

    $branch = Branch::first();
    $recall = Recall::create([
        'product_name' => 'Test Product',
        'batch_number' => 'BATCH-001',
        'reason' => 'Quality issue',
        'status' => 'pending',
        'branch_id' => $branch->id,
        'created_by' => $manager->id,
    ]);

    expect($manager->can('approve', $recall))->toBeTrue();

    app(ApproveRecallAction::class)->execute($recall, $manager, 'approved');

    $recall->refresh();

    expect($recall->status)->toBe('approved')
        ->and($recall->approved_by)->toBe($manager->id)
        ->and($recall->approved_at)->not->toBeNull();
});

test('branch manager cannot approve recalls', function (): void {
    $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
    $branchManager->assignRole('Branch Manager');

    $recall = Recall::create([
        'product_name' => 'Test Product',
        'batch_number' => 'BATCH-002',
        'reason' => 'Quality issue',
        'status' => 'pending',
        'branch_id' => $branchManager->branch_id,
        'created_by' => $branchManager->id,
    ]);

    expect($branchManager->can('approve', $recall))->toBeFalse();
});

test('recall admin can approve recalls', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Recall Admin');

    $branch = Branch::first();
    $recall = Recall::create([
        'product_name' => 'Test Product',
        'batch_number' => 'BATCH-003',
        'reason' => 'Safety issue',
        'status' => 'pending',
        'branch_id' => $branch->id,
        'created_by' => $admin->id,
    ]);

    expect($admin->can('approve', $recall))->toBeTrue();

    app(ApproveRecallAction::class)->execute($recall, $admin, 'approved');

    $recall->refresh();

    expect($recall->status)->toBe('approved')
        ->and($recall->approved_by)->toBe($admin->id);
});

test('warehouse operator can create but not approve recalls', function (): void {
    $operator = User::factory()->create(['branch_id' => Branch::first()->id]);
    $operator->assignRole('Warehouse Operator');

    expect($operator->can('create', Recall::class))->toBeTrue();

    $recall = Recall::create([
        'product_name' => 'Test Product',
        'batch_number' => 'BATCH-004',
        'reason' => 'Damage',
        'status' => 'pending',
        'branch_id' => $operator->branch_id,
        'created_by' => $operator->id,
    ]);

    expect($operator->can('approve', $recall))->toBeFalse();
});
