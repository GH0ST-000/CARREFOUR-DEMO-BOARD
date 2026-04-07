<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Receiving;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class GetVisualizationDataAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(string $type): array
    {
        return match ($type) {
            'overview' => $this->getOverviewData(),
            'expiry_timeline' => $this->getExpiryTimeline(),
            'product_categories' => $this->getProductCategories(),
            'receiving_status' => $this->getReceivingStatus(),
            'batch_status' => $this->getBatchStatus(),
            'inventory_by_location' => $this->getInventoryByLocation(),
            default => ['error' => 'Invalid visualization type'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function getOverviewData(): array
    {
        return [
            'type' => 'overview',
            'data' => [
                'products' => Product::query()->count(),
                'active_batches' => Batch::query()->active()->count(),
                'expiring_soon' => Batch::query()->expiringWithinDays(30)->count(),
                'pending_receivings' => Receiving::query()->where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getExpiryTimeline(): array
    {
        $now = Carbon::now();
        $ranges = [
            'expired' => Batch::query()->where('expiry_date', '<', $now)->count(),
            '0-7_days' => Batch::expiringWithinDays(7)->count(),
            '8-14_days' => Batch::query()->whereBetween('expiry_date', [
                $now->copy()->addDays(8),
                $now->copy()->addDays(14),
            ])->count(),
            '15-30_days' => Batch::query()->whereBetween('expiry_date', [
                $now->copy()->addDays(15),
                $now->copy()->addDays(30),
            ])->count(),
            '31-60_days' => Batch::query()->whereBetween('expiry_date', [
                $now->copy()->addDays(31),
                $now->copy()->addDays(60),
            ])->count(),
            '60+_days' => Batch::query()->where('expiry_date', '>', $now->copy()->addDays(60))->count(),
        ];

        return [
            'type' => 'expiry_timeline',
            'data' => $ranges,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getProductCategories(): array
    {
        /** @var Collection<int, object{category: string, count: int|string}> $rawResults */
        $rawResults = Product::query()
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        $categories = $rawResults->map(fn (object $item): array => [
            'category' => (string) $item->category,
            'count' => is_int($item->count) ? $item->count : (int) $item->count,
        ]);

        return [
            'type' => 'product_categories',
            'data' => $categories,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getReceivingStatus(): array
    {
        /** @var Collection<int, object{status: string, count: int|string}> $rawResults */
        $rawResults = Receiving::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $statuses = $rawResults->map(fn (object $item): array => [
            'status' => (string) $item->status,
            'count' => is_int($item->count) ? $item->count : (int) $item->count,
        ]);

        return [
            'type' => 'receiving_status',
            'data' => $statuses,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getBatchStatus(): array
    {
        /** @var Collection<int, object{status: string, count: int|string}> $rawResults */
        $rawResults = Batch::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $statuses = $rawResults->map(fn (object $item): array => [
            'status' => (string) $item->status,
            'count' => is_int($item->count) ? $item->count : (int) $item->count,
        ]);

        return [
            'type' => 'batch_status',
            'data' => $statuses,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getInventoryByLocation(): array
    {
        /** @var Collection<int, Batch> $results */
        $results = Batch::query()
            ->select('warehouse_location_id', DB::raw('sum(remaining_quantity) as total_quantity'))
            ->whereNotNull('warehouse_location_id')
            ->where('remaining_quantity', '>', 0)
            ->groupBy('warehouse_location_id')
            ->get();

        // Load the relationship after grouping
        $results->load('warehouseLocation:id,name,code');

        $locations = $results->map(function (Batch $item): array {
            $attributes = $item->getAttributes();
            $totalQty = $attributes['total_quantity'] ?? 0;

            return [
                'location_id' => $item->warehouse_location_id,
                'location_name' => $item->warehouseLocation->name ?? 'Unknown',
                'location_code' => $item->warehouseLocation->code ?? 'N/A',
                'total_quantity' => is_numeric($totalQty) ? (float) $totalQty : 0.0,
            ];
        });

        return [
            'type' => 'inventory_by_location',
            'data' => $locations,
        ];
    }
}
