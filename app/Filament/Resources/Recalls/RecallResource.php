<?php

namespace App\Filament\Resources\Recalls;

use App\Filament\Resources\Recalls\Pages\CreateRecall;
use App\Filament\Resources\Recalls\Pages\EditRecall;
use App\Filament\Resources\Recalls\Pages\ListRecalls;
use App\Filament\Resources\Recalls\Schemas\RecallForm;
use App\Filament\Resources\Recalls\Tables\RecallsTable;
use App\Models\Recall;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RecallResource extends Resource
{
    protected static ?string $model = Recall::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static UnitEnum|string|null $navigationGroup = 'Recall Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RecallForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecallsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecalls::route('/'),
            'create' => CreateRecall::route('/create'),
            'edit' => EditRecall::route('/{record}/edit'),
        ];
    }
}
