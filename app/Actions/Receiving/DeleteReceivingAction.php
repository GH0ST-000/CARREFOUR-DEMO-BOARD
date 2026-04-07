<?php

declare(strict_types=1);

namespace App\Actions\Receiving;

use App\Models\Receiving;
use App\Services\ActionLogService;

final readonly class DeleteReceivingAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(Receiving $receiving): bool
    {
        $this->actionLogService->logModelDeleted($receiving);

        return (bool) $receiving->delete();
    }
}
