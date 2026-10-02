<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Déjala vacía para conservar la contraseña actual.'),
                DateTimePicker::make('email_verified_at')
                    ->label('Correo verificado el')
                    ->placeholder('Sin verificar'),
                Toggle::make('is_admin')
                    ->label('Administrador')
                    ->helperText('Acceso al panel de administración (raíz del sitio).')
                    ->default(false),
            ]);
    }
}
