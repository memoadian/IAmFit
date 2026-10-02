<?php

namespace App\Filament\Resources\Exercises\Schemas;

use App\Enums\Equipment;
use App\Enums\Mechanic;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ExerciseForm
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
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Select::make('primary_muscle_id')
                    ->label('Músculo primario')
                    ->relationship('primaryMuscle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('equipment')
                    ->label('Equipo')
                    ->options(Equipment::class)
                    ->default('other')
                    ->required(),
                Select::make('mechanic')
                    ->label('Mecánica')
                    ->options(Mechanic::class)
                    ->default('compound')
                    ->required(),
                Toggle::make('is_public')
                    ->label('Público')
                    ->helperText('Visible para todos los usuarios de la app.')
                    ->default(true)
                    ->required(),
            ]);
    }
}
