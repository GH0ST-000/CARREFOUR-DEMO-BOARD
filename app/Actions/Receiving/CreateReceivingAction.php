<?php

declare(strict_types=1);

namespace App\Actions\Receiving;

use App\Models\Receiving;
use App\Services\ActionLogService;

final readonly class CreateReceivingAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Receiving
    {
        $receiving = Receiving::create($data);

        $this->actionLogService->logModelCreated($receiving);

        return $receiving;
    }
}
