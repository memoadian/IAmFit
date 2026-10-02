<?php

namespace App\Filament\Resources\AiFoodLookups;

use App\Filament\Resources\AiFoodLookups\Pages\ListAiFoodLookups;
use App\Filament\Resources\AiFoodLookups\Pages\ViewAiFoodLookup;
use App\Filament\Resources\AiFoodLookups\Schemas\AiFoodLookupForm;
use App\Filament\Resources\AiFoodLookups\Schemas\AiFoodLookupInfolist;
use App\Filament\Resources\AiFoodLookups\Tables\AiFoodLookupsTable;
use App\Models\AiFoodLookup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AiFoodLookupResource extends Resource
{
    protected static ?string $model = AiFoodLookup::class;

    protected static string|UnitEnum|null $navigationGroup = 'Nutrición';

    protected static ?string $navigationLabel = 'Búsquedas IA';

    protected static ?string $modelLabel = 'búsqueda';

    protected static ?string $pluralModelLabel = 'búsquedas de alimentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    /** Solo lectura: es un log de intentos de enriquecimiento, no se edita a mano. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AiFoodLookupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AiFoodLookupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiFoodLookupsTable::configure($table);
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
            'index' => ListAiFoodLookups::route('/'),
            'view' => ViewAiFoodLookup::route('/{record}'),
        ];
    }
}
