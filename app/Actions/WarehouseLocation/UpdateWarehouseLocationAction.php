<?php

declare(strict_types=1);

namespace App\Actions\WarehouseLocation;

use App\Models\WarehouseLocation;
use App\Services\ActionLogService;

final readonly class UpdateWarehouseLocationAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(WarehouseLocation $location, array $data): WarehouseLocation
    {
        $location->update($data);

        $this->actionLogService->logModelUpdated($location, $location->getChanges());

        return $location->fresh();
    }
}
