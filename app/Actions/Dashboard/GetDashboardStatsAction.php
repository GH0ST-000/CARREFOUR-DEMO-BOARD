<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Batch;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Receiving;

final class GetDashboardStatsAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $totalQuantity = Batch::query()->sum('remaining_quantity');

        return [
            'products' => [
                'total' => Product::query()->count(),
                'active' => Product::query()->active()->count(),
                'local' => Product::query()->local()->count(),
                'imported' => Product::query()->imported()->count(),
            ],
            'manufacturers' => [
                'total' => Manufacturer::query()->count(),
                'active' => Manufacturer::query()->where('is_active', true)->count(),
            ],
            'batches' => [
                'total' => Batch::query()->count(),
                'active' => Batch::query()->active()->count(),
                'expired' => Batch::query()->expired()->count(),
                'expiring_soon' => Batch::query()->expiringWithinDays(30)->count(),
                'blocked' => Batch::query()->blocked()->count(),
                'recalled' => Batch::query()->recalled()->count(),
            ],
            'receivings' => [
                'total' => Receiving::query()->count(),
                'pending' => Receiving::query()->where('status', 'pending')->count(),
                'accepted' => Receiving::query()->where('status', 'accepted')->count(),
                'rejected' => Receiving::query()->where('status', 'rejected')->count(),
                'quarantined' => Receiving::query()->where('status', 'quarantined')->count(),
            ],
            'inventory' => [
                'total_quantity' => is_numeric($totalQuantity) ? (float) $totalQuantity : 0.0,
                'total_value' => $this->calculateTotalInventoryValue(),
            ],
        ];
    }

    private function calculateTotalInventoryValue(): float
    {
        return 0.0;
    }
}
