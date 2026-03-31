<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Main Branch', 'code' => 'MB-001', 'location' => 'Tbilisi Center', 'is_active' => true],
            ['name' => 'Saburtalo Branch', 'code' => 'SB-002', 'location' => 'Saburtalo District', 'is_active' => true],
            ['name' => 'Vake Branch', 'code' => 'VK-003', 'location' => 'Vake District', 'is_active' => true],
            ['name' => 'Gldani Branch', 'code' => 'GL-004', 'location' => 'Gldani District', 'is_active' => true],
            ['name' => 'Batumi Branch', 'code' => 'BT-005', 'location' => 'Batumi', 'is_active' => true],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
