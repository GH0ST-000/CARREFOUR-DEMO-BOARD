<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Http\Resources\BatchResource;
use App\Models\Batch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class GetExpiringBatchesAction
{
    public function execute(int $days, int $perPage): AnonymousResourceCollection
    {
        $batches = Batch::query()
            ->expiringWithinDays($days)
            ->with(['product.manufacturer', 'warehouseLocation', 'receivedBy'])
            ->whereIn('status', ['received', 'in_storage'])
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->cursorPaginate(perPage: $perPage);

        return BatchResource::collection($batches);
    }
}
