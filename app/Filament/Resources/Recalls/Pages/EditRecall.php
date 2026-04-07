<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recalls\Pages;

use App\Filament\Resources\Recalls\RecallResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditRecall extends EditRecord
{
    protected static string $resource = RecallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
