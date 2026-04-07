<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Resources\Pages\ListRecords;

final class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;
}
