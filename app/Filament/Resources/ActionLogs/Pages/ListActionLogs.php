<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActionLogs\Pages;

use App\Filament\Resources\ActionLogs\ActionLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListActionLogs extends ListRecords
{
    protected static string $resource = ActionLogResource::class;
}
