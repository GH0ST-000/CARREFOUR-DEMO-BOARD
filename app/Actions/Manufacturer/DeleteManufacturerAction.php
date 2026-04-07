<?php

declare(strict_types=1);

namespace App\Actions\Manufacturer;

use App\Models\Manufacturer;
use App\Services\ActionLogService;

final readonly class DeleteManufacturerAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(Manufacturer $manufacturer): bool
    {
        $this->actionLogService->logModelDeleted($manufacturer);

        return (bool) $manufacturer->delete();
    }
}
