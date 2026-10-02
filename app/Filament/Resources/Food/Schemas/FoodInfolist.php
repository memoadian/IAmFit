<?php

namespace App\Filament\Resources\Food\Schemas;

use App\Models\Food;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FoodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('brand')
                    ->placeholder('-'),
                TextEntry::make('barcode')
                    ->placeholder('-'),
                TextEntry::make('source')
                    ->badge(),
                TextEntry::make('external_id')
                    ->placeholder('-'),
                TextEntry::make('locale')
                    ->placeholder('-'),
                TextEntry::make('kcal')
                    ->numeric(),
                TextEntry::make('protein_g')
                    ->numeric(),
                TextEntry::make('carb_g')
                    ->numeric(),
                TextEntry::make('fat_g')
                    ->numeric(),
                TextEntry::make('fiber_g')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('sugar_g')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('sat_fat_g')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('sodium_mg')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('verified_at')
                    ->label('Verificado el')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('verifiedBy.name')
                    ->label('Verificado por')
                    ->placeholder('-'),
                TextEntry::make('creator.name')
                    ->label('Creado por')
                    ->placeholder('-'),
                TextEntry::make('portions.label')
                    ->label('Porciones')
                    ->badge()
                    ->placeholder('-')
                    ->listWithLineBreaks(),
                TextEntry::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Food $record): bool => $record->trashed()),
            ]);
    }
}
