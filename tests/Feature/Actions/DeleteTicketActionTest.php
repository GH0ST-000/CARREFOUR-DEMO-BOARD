<?php

declare(strict_types=1);

use App\Actions\Ticket\DeleteTicketAction;
use App\Models\Ticket;

test('can delete a ticket', function (): void {
    $ticket = Ticket::factory()->create();

    $action = app(DeleteTicketAction::class);
    $result = $action->execute($ticket);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});
