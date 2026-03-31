<?php

namespace App\Filament\Resources\Recalls\Pages;

use App\Filament\Resources\Recalls\RecallResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecalls extends ListRecords
{
    protected static string $resource = RecallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
