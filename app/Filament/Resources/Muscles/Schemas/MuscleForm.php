<?php

namespace App\Filament\Resources\Muscles\Schemas;

use App\Enums\MuscleGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MuscleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('group')
                    ->label('Grupo')
                    ->options(MuscleGroup::class)
                    ->required(),
            ]);
    }
}
