<?php

namespace App\Filament\Resources\Recalls\Pages;

use App\Filament\Resources\Recalls\RecallResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRecall extends EditRecord
{
    protected static string $resource = RecallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
