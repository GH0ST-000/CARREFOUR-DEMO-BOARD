<?php

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Services\ActionLogService;

final readonly class CloseTicketAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(Ticket $ticket): Ticket
    {
        $oldStatus = $ticket->status;

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->actionLogService->logStatusChange($ticket, $oldStatus, 'closed');

        return $ticket->fresh();
    }
}
