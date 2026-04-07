<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Recall;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(BranchSeeder::class);
});

describe('UserPolicy', function (): void {
    test('recall admin can view any user', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('viewAny', User::class))->toBeTrue();
    });

    test('branch manager can view users from own branch', function (): void {
        $branch = Branch::first();
        $branchManager = User::factory()->create(['branch_id' => $branch->id]);
        $branchManager->assignRole('Branch Manager');

        $userInSameBranch = User::factory()->create(['branch_id' => $branch->id]);

        expect($branchManager->can('view', $userInSameBranch))->toBeTrue();
    });

    test('recall admin can create users', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('create', User::class))->toBeTrue();
    });

    test('branch manager cannot create users', function (): void {
        $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('create', User::class))->toBeFalse();
    });

    test('recall admin can update any user', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $user = User::factory()->create();

        expect($admin->can('update', $user))->toBeTrue();
    });

    test('branch manager cannot update users', function (): void {
        $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
        $branchManager->assignRole('Branch Manager');

        $user = User::factory()->create();

        expect($branchManager->can('update', $user))->toBeFalse();
    });

    test('recall admin can delete users', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $user = User::factory()->create();

        expect($admin->can('delete', $user))->toBeTrue();
    });

    test('recall admin can restore users', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $user = User::factory()->create();

        expect($admin->can('restore', $user))->toBeTrue();
    });

    test('recall admin can force delete users', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $user = User::factory()->create();

        expect($admin->can('forceDelete', $user))->toBeTrue();
    });

    test('branch manager can view any users with own branch permission', function (): void {
        $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('viewAny', User::class))->toBeTrue();
    });

    test('branch manager cannot view users from other branches', function (): void {
        $branch1 = Branch::first();
        $branch2 = Branch::skip(1)->first();

        $branchManager = User::factory()->create(['branch_id' => $branch1->id]);
        $branchManager->assignRole('Branch Manager');

        $userFromOtherBranch = User::factory()->create(['branch_id' => $branch2->id]);

        expect($branchManager->can('view', $userFromOtherBranch))->toBeFalse();
    });

    test('auditor cannot view users', function (): void {
        $auditor = User::factory()->create();
        $auditor->assignRole('Auditor');

        $user = User::factory()->create();

        expect($auditor->can('viewAny', User::class))->toBeFalse()
            ->and($auditor->can('view', $user))->toBeFalse();
    });

    test('quality manager can view any user with full permission', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Quality Manager');

        $user = User::factory()->create();

        expect($manager->can('view', $user))->toBeTrue();
    });
});

describe('BranchPolicy', function (): void {
    test('recall admin can view any branch', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('viewAny', Branch::class))->toBeTrue();
    });

    test('quality manager can view branches', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Quality Manager');

        $branch = Branch::first();

        expect($manager->can('view', $branch))->toBeTrue();
    });

    test('recall admin can create branches', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('create', Branch::class))->toBeTrue();
    });

    test('branch manager cannot create branches', function (): void {
        $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('create', Branch::class))->toBeFalse();
    });

    test('recall admin can update branches', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $branch = Branch::first();

        expect($admin->can('update', $branch))->toBeTrue();
    });

    test('recall admin can delete branches', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $branch = Branch::first();

        expect($admin->can('delete', $branch))->toBeTrue();
    });

    test('recall admin can restore branches', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $branch = Branch::first();

        expect($admin->can('restore', $branch))->toBeTrue();
    });

    test('recall admin can force delete branches', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $branch = Branch::first();

        expect($admin->can('forceDelete', $branch))->toBeTrue();
    });

    test('branch manager can view own branch', function (): void {
        $branch = Branch::first();
        $branchManager = User::factory()->create(['branch_id' => $branch->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('view', $branch))->toBeTrue();
    });

    test('branch manager cannot view other branches', function (): void {
        $branch1 = Branch::first();
        $branch2 = Branch::skip(1)->first();

        $branchManager = User::factory()->create(['branch_id' => $branch1->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('view', $branch2))->toBeFalse();
    });

    test('branch manager can view any branches when has own branch permission', function (): void {
        $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('viewAny', Branch::class))->toBeTrue();
    });

    test('auditor cannot view branches', function (): void {
        $auditor = User::factory()->create();
        $auditor->assignRole('Auditor');

        $branch = Branch::first();

        expect($auditor->can('viewAny', Branch::class))->toBeFalse()
            ->and($auditor->can('view', $branch))->toBeFalse();
    });
});

describe('RolePolicy', function (): void {
    test('recall admin can view any role', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('viewAny', Role::class))->toBeTrue();
    });

    test('quality manager cannot view roles', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Quality Manager');

        $role = Role::findByName('Branch Manager');

        expect($manager->can('view', $role))->toBeFalse();
    });

    test('recall admin can create roles', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        expect($admin->can('create', Role::class))->toBeTrue();
    });

    test('branch manager cannot create roles', function (): void {
        $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('create', Role::class))->toBeFalse();
    });

    test('recall admin can update roles', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $role = Role::findByName('Branch Manager');

        expect($admin->can('update', $role))->toBeTrue();
    });

    test('recall admin can delete roles', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $role = Role::findByName('Branch Manager');

        expect($admin->can('delete', $role))->toBeTrue();
    });

    test('recall admin can restore roles', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $role = Role::findByName('Branch Manager');

        expect($admin->can('restore', $role))->toBeTrue();
    });

    test('recall admin can force delete roles', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $role = Role::findByName('Branch Manager');

        expect($admin->can('forceDelete', $role))->toBeTrue();
    });
});

describe('RecallPolicy', function (): void {
    test('warehouse operator cannot delete recalls', function (): void {
        $operator = User::factory()->create(['branch_id' => Branch::first()->id]);
        $operator->assignRole('Warehouse Operator');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => $operator->branch_id,
            'created_by' => $operator->id,
        ]);

        expect($operator->can('delete', $recall))->toBeFalse();
    });

    test('quality manager can delete recalls', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Quality Manager');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => Branch::first()->id,
            'created_by' => $manager->id,
        ]);

        expect($manager->can('delete', $recall))->toBeTrue();
    });

    test('auditor cannot restore recalls', function (): void {
        $auditor = User::factory()->create();
        $auditor->assignRole('Auditor');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => Branch::first()->id,
            'created_by' => $auditor->id,
        ]);

        expect($auditor->can('restore', $recall))->toBeFalse();
    });

    test('recall admin can restore recalls', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => Branch::first()->id,
            'created_by' => $admin->id,
        ]);

        expect($admin->can('restore', $recall))->toBeTrue();
    });

    test('recall admin can force delete recalls', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Recall Admin');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => Branch::first()->id,
            'created_by' => $admin->id,
        ]);

        expect($admin->can('forceDelete', $recall))->toBeTrue();
    });

    test('warehouse operator cannot force delete recalls', function (): void {
        $operator = User::factory()->create(['branch_id' => Branch::first()->id]);
        $operator->assignRole('Warehouse Operator');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => $operator->branch_id,
            'created_by' => $operator->id,
        ]);

        expect($operator->can('forceDelete', $recall))->toBeFalse();
    });

    test('branch manager can view any recalls with own branch permission', function (): void {
        $branchManager = User::factory()->create(['branch_id' => Branch::first()->id]);
        $branchManager->assignRole('Branch Manager');

        expect($branchManager->can('viewAny', Recall::class))->toBeTrue();
    });

    test('quality manager can update recalls', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('Quality Manager');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => Branch::first()->id,
            'created_by' => $manager->id,
        ]);

        expect($manager->can('update', $recall))->toBeTrue();
    });

    test('branch manager cannot update recalls from other branches', function (): void {
        $branch1 = Branch::first();
        $branch2 = Branch::skip(1)->first();

        $branchManager = User::factory()->create(['branch_id' => $branch1->id]);
        $branchManager->assignRole('Branch Manager');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => $branch2->id,
            'created_by' => $branchManager->id,
        ]);

        expect($branchManager->can('update', $recall))->toBeFalse();
    });

    test('auditor cannot create recalls', function (): void {
        $auditor = User::factory()->create();
        $auditor->assignRole('Auditor');

        expect($auditor->can('create', Recall::class))->toBeFalse();
    });

    test('auditor cannot update recalls', function (): void {
        $auditor = User::factory()->create();
        $auditor->assignRole('Auditor');

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => Branch::first()->id,
            'created_by' => $auditor->id,
        ]);

        expect($auditor->can('update', $recall))->toBeFalse();
    });

    test('user without view permission cannot view recall', function (): void {
        $user = User::factory()->create();

        $recall = Recall::create([
            'product_name' => 'Test Product',
            'batch_number' => 'BATCH-001',
            'reason' => 'Test',
            'branch_id' => Branch::first()->id,
            'created_by' => $user->id,
        ]);

        expect($user->can('view', $recall))->toBeFalse();
    });
});
