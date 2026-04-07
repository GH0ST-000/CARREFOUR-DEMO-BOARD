<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class GetRecentAdditionsAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $days, int $limit): array
    {
        $since = Carbon::now()->subDays($days);

        /** @var Collection<int, Manufacturer> $recentManufacturers */
        $recentManufacturers = Manufacturer::query()
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        /** @var Collection<int, Product> $recentProducts */
        $recentProducts = Product::query()
            ->with('manufacturer:id,full_name,short_name')
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return [
            'manufacturers' => [
                'count' => $recentManufacturers->count(),
                'items' => $recentManufacturers->map(fn (Manufacturer $m): array => [
                    'id' => $m->id,
                    'name' => $m->full_name,
                    'short_name' => $m->short_name,
                    'country' => $m->country,
                    'created_at' => $m->created_at->toISOString(),
                ])->values(),
            ],
            'products' => [
                'count' => $recentProducts->count(),
                'items' => $recentProducts->map(fn (Product $p): array => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'category' => $p->category,
                    'manufacturer' => $p->manufacturer !== null ? [
                        'id' => $p->manufacturer->id,
                        'name' => $p->manufacturer->full_name,
                    ] : null,
                    'created_at' => $p->created_at->toISOString(),
                ])->values(),
            ],
        ];
    }
}
