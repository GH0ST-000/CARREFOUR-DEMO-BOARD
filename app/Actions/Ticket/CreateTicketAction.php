<?php

declare(strict_types=1);

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Services\ActionLogService;

final readonly class CreateTicketAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Ticket
    {
        $ticket = Ticket::create(array_merge([
            'status' => 'open',
            'priority' => 'medium',
        ], $data));

        $this->actionLogService->logModelCreated($ticket);

        return $ticket;
    }
}
