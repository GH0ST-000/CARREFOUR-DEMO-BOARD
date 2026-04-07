<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Recall;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(BranchSeeder::class);
});

test('branch manager can only view users from their own branch', function (): void {
    $branch1 = Branch::first();
    $branch2 = Branch::skip(1)->first();

    $branchManager = User::factory()->create(['branch_id' => $branch1->id]);
    $branchManager->assignRole('Branch Manager');

    $userInSameBranch = User::factory()->create(['branch_id' => $branch1->id]);
    $userInDifferentBranch = User::factory()->create(['branch_id' => $branch2->id]);

    expect($branchManager->can('view', $userInSameBranch))->toBeTrue()
        ->and($branchManager->can('view', $userInDifferentBranch))->toBeFalse();
});

test('branch manager can only view recalls from their own branch', function (): void {
    $branch1 = Branch::first();
    $branch2 = Branch::skip(1)->first();

    $branchManager = User::factory()->create(['branch_id' => $branch1->id]);
    $branchManager->assignRole('Branch Manager');

    $recallInSameBranch = Recall::create([
        'product_name' => 'Product A',
        'batch_number' => 'BATCH-001',
        'reason' => 'Test',
        'branch_id' => $branch1->id,
        'created_by' => $branchManager->id,
    ]);

    $recallInDifferentBranch = Recall::create([
        'product_name' => 'Product B',
        'batch_number' => 'BATCH-002',
        'reason' => 'Test',
        'branch_id' => $branch2->id,
        'created_by' => $branchManager->id,
    ]);

    expect($branchManager->can('view', $recallInSameBranch))->toBeTrue()
        ->and($branchManager->can('view', $recallInDifferentBranch))->toBeFalse();
});

test('recall admin can view all branches and recalls', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Recall Admin');

    $branch1 = Branch::first();
    $branch2 = Branch::skip(1)->first();

    $recall1 = Recall::create([
        'product_name' => 'Product A',
        'batch_number' => 'BATCH-001',
        'reason' => 'Test',
        'branch_id' => $branch1->id,
        'created_by' => $admin->id,
    ]);

    $recall2 = Recall::create([
        'product_name' => 'Product B',
        'batch_number' => 'BATCH-002',
        'reason' => 'Test',
        'branch_id' => $branch2->id,
        'created_by' => $admin->id,
    ]);

    expect($admin->can('view', $recall1))->toBeTrue()
        ->and($admin->can('view', $recall2))->toBeTrue()
        ->and($admin->can('viewAny', User::class))->toBeTrue();
});

test('quality manager has full view access but branch scoped to own branch', function (): void {
    $qualityManager = User::factory()->create();
    $qualityManager->assignRole('Quality Manager');

    $branch1 = Branch::first();
    $branch2 = Branch::skip(1)->first();

    $recall = Recall::create([
        'product_name' => 'Product A',
        'batch_number' => 'BATCH-001',
        'reason' => 'Test',
        'branch_id' => $branch1->id,
        'created_by' => $qualityManager->id,
    ]);

    expect($qualityManager->can('view', $recall))->toBeTrue()
        ->and($qualityManager->can('viewAny', Recall::class))->toBeTrue();
});
