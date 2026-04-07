<?php

declare(strict_types=1);

namespace App\Actions\TicketMessage;

use App\Models\TicketMessage;
use App\Services\ActionLogService;

final readonly class CreateTicketMessageAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): TicketMessage
    {
        $message = TicketMessage::create($data);

        $this->actionLogService->logModelCreated($message);

        return $message;
    }
}
