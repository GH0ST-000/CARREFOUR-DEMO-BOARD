<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use App\Models\Product;
use App\Services\ActionLogService;
use App\Services\NotificationService;
use App\Support\ApiActor;
use Illuminate\Support\Carbon;

final readonly class CreateBatchAction
{
    public function __construct(
        private ActionLogService $actionLogService,
        private NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Batch
    {
        if (! isset($data['expiry_date']) || $data['expiry_date'] === null) {
            /** @var Product $product */
            $product = Product::query()->findOrFail($data['product_id']);

            /** @var Carbon $productionDate */
            $productionDate = $data['production_date'] instanceof Carbon
                ? $data['production_date']
                : Carbon::parse((string) $data['production_date'])->startOfDay();

            $data['expiry_date'] = $productionDate->copy()->addDays($product->shelf_life_days)->toDateString();
        }

        $data['remaining_quantity'] = $data['quantity'];

        $model = Batch::query()->create($data);

        $this->actionLogService->logModelCreated($model);

        $this->notificationService->notifyBatchCreated($model, ApiActor::id());

        return $model;
    }
}
