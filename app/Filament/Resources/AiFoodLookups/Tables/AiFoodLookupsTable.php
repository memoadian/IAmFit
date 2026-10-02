<?php

namespace App\Filament\Resources\AiFoodLookups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiFoodLookupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('query')
                    ->searchable(),
                TextColumn::make('query_hash')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state?->value) {
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'done' => 'Completado',
                        'failed' => 'Fallido',
                        default => (string) $state?->value,
                    })
                    ->color(fn ($state): string => match ($state?->value) {
                        'done' => 'success',
                        'failed' => 'danger',
                        'processing' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('resolved_by')
                    ->badge()
                    ->searchable(),
                TextColumn::make('food.name')
                    ->searchable(),
                TextColumn::make('requestedBy.name')
                    ->label('Solicitado por')
                    ->placeholder('—'),
                TextColumn::make('prompt_tokens')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('completion_tokens')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
