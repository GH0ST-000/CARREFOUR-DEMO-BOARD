<?php

declare(strict_types=1);

namespace App\Actions\WarehouseLocation;

use App\Models\WarehouseLocation;
use App\Services\ActionLogService;

final readonly class DeleteWarehouseLocationAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(WarehouseLocation $location): bool
    {
        $this->actionLogService->logModelDeleted($location);

        return (bool) $location->delete();
    }
}
