<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use App\Models\Product;
use App\Services\ActionLogService;
use App\Services\NotificationService;
use App\Support\ApiActor;
use Illuminate\Support\Carbon;

final readonly class UpdateBatchAction
{
    public function __construct(
        private ActionLogService $actionLogService,
        private NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Batch $batch, array $data): Batch
    {
        if (array_key_exists('expiry_date', $data) && $data['expiry_date'] === null) {
            unset($data['expiry_date']);
        }

        $hasProductionDate = array_key_exists('production_date', $data);
        $hasProductId = array_key_exists('product_id', $data);
        $hasExpiryDate = array_key_exists('expiry_date', $data);

        if (! $hasExpiryDate && ($hasProductionDate || $hasProductId)) {
            $productId = $hasProductId ? $data['product_id'] : $batch->product_id;

            /** @var Product $product */
            $product = Product::query()->findOrFail($productId);

            $productionDateValue = $hasProductionDate ? $data['production_date'] : $batch->production_date;
            /** @var Carbon $productionDate */
            $productionDate = $productionDateValue instanceof Carbon
                ? $productionDateValue
                : Carbon::parse((string) $productionDateValue)->startOfDay();

            $data['expiry_date'] = $productionDate->copy()->addDays($product->shelf_life_days)->toDateString();
        }

        $batch->update($data);

        $this->actionLogService->logModelUpdated($batch, $batch->getChanges());

        $this->notificationService->notifyBatchUpdated($batch, ApiActor::id());

        return $batch;
    }
}
