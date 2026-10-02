<?php

namespace App\Filament\Resources\Muscles;

use App\Filament\Resources\Muscles\Pages\CreateMuscle;
use App\Filament\Resources\Muscles\Pages\EditMuscle;
use App\Filament\Resources\Muscles\Pages\ListMuscles;
use App\Filament\Resources\Muscles\Schemas\MuscleForm;
use App\Filament\Resources\Muscles\Tables\MusclesTable;
use App\Models\Muscle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MuscleResource extends Resource
{
    protected static ?string $model = Muscle::class;

    protected static string|UnitEnum|null $navigationGroup = 'Entrenamiento';

    protected static ?string $navigationLabel = 'Músculos';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'músculo';

    protected static ?string $pluralModelLabel = 'músculos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return MuscleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MusclesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMuscles::route('/'),
            'create' => CreateMuscle::route('/create'),
            'edit' => EditMuscle::route('/{record}/edit'),
        ];
    }
}
