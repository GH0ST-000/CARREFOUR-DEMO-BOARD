<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActionLogs;

use App\Filament\Resources\ActionLogs\Pages\ListActionLogs;
use App\Filament\Resources\ActionLogs\Pages\ViewActionLog;
use App\Filament\Resources\ActionLogs\Tables\ActionLogsTable;
use App\Models\ActionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class ActionLogResource extends Resource
{
    protected static ?string $model = ActionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Action Logs';

    public static function table(Table $table): Table
    {
        return ActionLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActionLogs::route('/'),
            'view' => ViewActionLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
