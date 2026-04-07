<?php

declare(strict_types=1);

namespace App\Actions\WarehouseLocation;

use App\Models\WarehouseLocation;
use App\Services\ActionLogService;

final readonly class CreateWarehouseLocationAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): WarehouseLocation
    {
        $warehouseLocation = WarehouseLocation::create($data);

        $this->actionLogService->logModelCreated($warehouseLocation);

        return $warehouseLocation;
    }
}
