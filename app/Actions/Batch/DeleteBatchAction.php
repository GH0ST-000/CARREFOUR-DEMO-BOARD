<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use App\Services\ActionLogService;

final readonly class DeleteBatchAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(Batch $batch): bool
    {
        $this->actionLogService->logModelDeleted($batch);

        return (bool) $batch->delete();
    }
}
