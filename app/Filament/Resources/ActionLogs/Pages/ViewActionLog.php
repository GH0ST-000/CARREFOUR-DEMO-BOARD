<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActionLogs\Pages;

use App\Filament\Resources\ActionLogs\ActionLogResource;
use App\Models\ActionLog;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewActionLog extends ViewRecord
{
    protected static string $resource = ActionLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('System'),
                TextEntry::make('action_type')
                    ->badge(),
                TextEntry::make('entity_type'),
                TextEntry::make('entity_id'),
                TextEntry::make('module')
                    ->badge(),
                TextEntry::make('description'),
                KeyValueEntry::make('metadata')
                    ->visible(fn (ActionLog $record): bool => $record->metadata !== null),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
