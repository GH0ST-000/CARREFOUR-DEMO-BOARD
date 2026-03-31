<?php

namespace App\Filament\Resources\Recalls\Pages;

use App\Filament\Resources\Recalls\RecallResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecall extends CreateRecord
{
    protected static string $resource = RecallResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var int $userId */
        $userId = auth()->id();
        $data['created_by'] = $userId;

        return $data;
    }
}
