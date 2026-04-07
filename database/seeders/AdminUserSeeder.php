<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Recall Admin',
            'email' => 'admin@nikora.ge',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Recall Admin');

        $qualityManager = User::create([
            'name' => 'Quality Manager',
            'email' => 'quality@nikora.ge',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $qualityManager->assignRole('Quality Manager');

        $firstBranch = Branch::first();
        if ($firstBranch) {
            $branchManager = User::create([
                'name' => 'Branch Manager',
                'email' => 'branch@nikora.ge',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'branch_id' => $firstBranch->id,
            ]);
            $branchManager->assignRole('Branch Manager');

            $warehouseOperator = User::create([
                'name' => 'Warehouse Operator',
                'email' => 'warehouse@nikora.ge',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'branch_id' => $firstBranch->id,
            ]);
            $warehouseOperator->assignRole('Warehouse Operator');
        }

        $auditor = User::create([
            'name' => 'Auditor',
            'email' => 'auditor@nikora.ge',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $auditor->assignRole('Auditor');
    }
}
