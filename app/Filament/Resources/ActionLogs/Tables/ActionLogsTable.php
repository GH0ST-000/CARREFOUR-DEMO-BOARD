<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActionLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ActionLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System'),
                TextColumn::make('action_type')
                    ->badge()
                    ->colors([
                        'success' => 'create',
                        'info' => 'update',
                        'danger' => 'delete',
                        'warning' => 'status_change',
                        'primary' => 'login',
                        'gray' => 'logout',
                    ]),
                TextColumn::make('entity_type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entity_id')
                    ->sortable(),
                TextColumn::make('module')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(60),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action_type')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'status_change' => 'Status Change',
                    ]),
                SelectFilter::make('module')
                    ->options([
                        'dashboard' => 'Dashboard',
                        'manufacturers' => 'Manufacturers',
                        'products' => 'Products',
                        'batches' => 'Batches',
                        'warehouse-locations' => 'Warehouse Locations',
                        'receivings' => 'Receivings',
                        'tickets' => 'Tickets',
                        'users' => 'Users',
                    ]),
                SelectFilter::make('entity_type')
                    ->options([
                        'manufacturer' => 'Manufacturer',
                        'product' => 'Product',
                        'batch' => 'Batch',
                        'warehouse_location' => 'Warehouse Location',
                        'receiving' => 'Receiving',
                        'ticket' => 'Ticket',
                        'ticket_message' => 'Ticket Message',
                        'user' => 'User',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
