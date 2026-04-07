<?php

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Services\ActionLogService;

final readonly class DeleteTicketAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(Ticket $ticket): bool
    {
        $this->actionLogService->logModelDeleted($ticket);

        return (bool) $ticket->delete();
    }
}
