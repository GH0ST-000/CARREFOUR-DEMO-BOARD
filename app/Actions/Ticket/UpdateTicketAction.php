<?php

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Services\ActionLogService;

final readonly class UpdateTicketAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Ticket $ticket, array $data): Ticket
    {
        $ticket->update($data);

        $this->actionLogService->logModelUpdated($ticket, $ticket->getChanges());

        return $ticket->fresh();
    }
}
