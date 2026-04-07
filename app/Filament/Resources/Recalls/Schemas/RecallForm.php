<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recalls\Schemas;

use App\Models\Recall;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class RecallForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('batch_number')
                    ->required()
                    ->maxLength(255),
                Textarea::make('reason')
                    ->required()
                    ->rows(4),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->default(function (): ?int {
                        $user = auth()->user();
                        if (! $user instanceof User) {
                            return null;
                        }

                        return $user->branch_id;
                    })
                    ->disabled(function (): bool {
                        $user = auth()->user();
                        if (! $user instanceof User) {
                            return true;
                        }

                        return ! $user->hasRole(['Recall Admin', 'Quality Manager']);
                    }),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->default('pending')
                    ->disabled(function (): bool {
                        $user = auth()->user();
                        if (! $user instanceof User) {
                            return true;
                        }

                        return ! $user->can('approve', Recall::class);
                    })
                    ->required(),
            ]);
    }
}
