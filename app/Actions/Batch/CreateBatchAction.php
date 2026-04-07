<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use App\Services\ActionLogService;

final readonly class CreateBatchAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Batch
    {
        $data['remaining_quantity'] = $data['quantity'];

        $batch = Batch::create($data);

        $this->actionLogService->logModelCreated($batch);

        return $batch;
    }
}
