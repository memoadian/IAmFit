<?php

namespace App\Filament\Resources\Food\Schemas;

use App\Enums\FoodSource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FoodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('brand')
                            ->label('Marca'),
                        TextInput::make('barcode')
                            ->label('Código de barras'),
                        Select::make('source')
                            ->label('Fuente')
                            ->options(FoodSource::class)
                            ->required(),
                        TextInput::make('external_id')
                            ->label('ID externo'),
                        TextInput::make('locale')
                            ->label('Mercado')
                            ->placeholder('es-MX'),
                    ]),
                Section::make('Macros por 100 g')
                    ->columns(3)
                    ->schema([
                        TextInput::make('kcal')
                            ->label('Calorías')
                            ->required()
                            ->numeric()
                            ->suffix('kcal'),
                        TextInput::make('protein_g')
                            ->label('Proteína')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->suffix('g'),
                        TextInput::make('carb_g')
                            ->label('Carbohidratos')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->suffix('g'),
                        TextInput::make('fat_g')
                            ->label('Grasa')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->suffix('g'),
                        TextInput::make('fiber_g')
                            ->label('Fibra')
                            ->numeric()
                            ->suffix('g'),
                        TextInput::make('sugar_g')
                            ->label('Azúcares')
                            ->numeric()
                            ->suffix('g'),
                        TextInput::make('sat_fat_g')
                            ->label('Grasa saturada')
                            ->numeric()
                            ->suffix('g'),
                        TextInput::make('sodium_mg')
                            ->label('Sodio')
                            ->numeric()
                            ->suffix('mg'),
                    ]),
                Section::make('Micronutrientes y verificación')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        KeyValue::make('micros')
                            ->label('Micronutrientes (clave / valor)')
                            ->columnSpanFull(),
                        DateTimePicker::make('verified_at')
                            ->label('Verificado el')
                            ->helperText('Los alimentos estimados por IA llegan sin verificar.'),
                    ]),
            ]);
    }
}
