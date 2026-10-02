<?php

namespace App\Filament\Resources\AiFoodLookups\Schemas;

use App\Enums\LookupStatus;
use App\Enums\ResolvedBy;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AiFoodLookupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('query')
                    ->required(),
                TextInput::make('query_hash')
                    ->required(),
                Select::make('status')
                    ->options(LookupStatus::class)
                    ->default('pending')
                    ->required(),
                Select::make('resolved_by')
                    ->options(ResolvedBy::class),
                Select::make('food_id')
                    ->relationship('food', 'name'),
                TextInput::make('requested_by')
                    ->numeric(),
                TextInput::make('raw'),
                Textarea::make('error')
                    ->columnSpanFull(),
                TextInput::make('prompt_tokens')
                    ->numeric(),
                TextInput::make('completion_tokens')
                    ->numeric(),
            ]);
    }
}
