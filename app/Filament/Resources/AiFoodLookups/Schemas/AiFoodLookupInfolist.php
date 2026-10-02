<?php

namespace App\Filament\Resources\AiFoodLookups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AiFoodLookupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('query'),
                TextEntry::make('query_hash'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('resolved_by')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('food.name')
                    ->label('Food')
                    ->placeholder('-'),
                TextEntry::make('requested_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('error')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('prompt_tokens')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('completion_tokens')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
