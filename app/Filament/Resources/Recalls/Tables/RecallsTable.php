<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recalls\Tables;

use App\Actions\Recall\ApproveRecallAction;
use App\Models\Recall;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class RecallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['branch', 'creator', 'approver']))
            ->columns([
                TextColumn::make('product_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'completed',
                    ]),
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->sortable()
                    ->placeholder('Not approved'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ]),
                SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Recall $record): bool {
                        $user = auth()->user();
                        if (! $user instanceof User) {
                            return false;
                        }

                        return $record->status === 'pending' && $user->can('approve', $record);
                    })
                    ->action(function (Recall $record): void {
                        $user = auth()->user();
                        if ($user instanceof User) {
                            app(ApproveRecallAction::class)->execute($record, $user, 'approved');
                        }
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (Recall $record): bool {
                        $user = auth()->user();
                        if (! $user instanceof User) {
                            return false;
                        }

                        return $record->status === 'pending' && $user->can('approve', $record);
                    })
                    ->action(function (Recall $record): void {
                        $user = auth()->user();
                        if ($user instanceof User) {
                            app(ApproveRecallAction::class)->execute($record, $user, 'rejected');
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
