<?php

namespace App\Filament\Resources\Food\Tables;

use App\Enums\FoodSource;
use App\Models\Food;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FoodTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('brand')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('source')
                    ->badge()
                    ->formatStateUsing(fn (FoodSource $state): string => match ($state) {
                        FoodSource::Off => 'Open Food Facts',
                        FoodSource::Usda => 'USDA',
                        FoodSource::Ai => 'IA (estimado)',
                        FoodSource::Manual => 'Manual',
                    }),
                TextColumn::make('kcal')
                    ->numeric()
                    ->suffix(' kcal')
                    ->sortable(),
                TextColumn::make('protein_g')
                    ->label('Proteína')
                    ->numeric()
                    ->suffix(' g')
                    ->toggleable(),
                TextColumn::make('carb_g')
                    ->label('Carbos')
                    ->numeric()
                    ->suffix(' g')
                    ->toggleable(),
                TextColumn::make('fat_g')
                    ->label('Grasa')
                    ->numeric()
                    ->suffix(' g')
                    ->toggleable(),
                TextColumn::make('verified_at')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Verificado' : 'Estimado')
                    ->color(fn ($state): string => $state ? 'success' : 'warning')
                    ->sortable()
                    ->placeholder('Estimado'),
                TextColumn::make('verifiedBy.name')
                    ->label('Verificado por')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('estimated')
                    ->label('Solo sin verificar')
                    ->query(fn (Builder $query): Builder => $query->whereNull('verified_at'))
                    ->toggle(),
                SelectFilter::make('source')
                    ->label('Fuente')
                    ->options([
                        FoodSource::Off->value => 'Open Food Facts',
                        FoodSource::Usda->value => 'USDA',
                        FoodSource::Ai->value => 'IA (estimado)',
                        FoodSource::Manual->value => 'Manual',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                self::verifyAction(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkAction::make('verify')
                    ->label('Verificar seleccionados')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each->update([
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Alimentos verificados')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    private static function verifyAction(): Action
    {
        return Action::make('verify')
            ->label('Verificar')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn (Food $record): bool => ! $record->isVerified())
            ->requiresConfirmation()
            ->action(function (Food $record): void {
                $record->update([
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Alimento verificado')
                    ->success()
                    ->send();
            });
    }
}
