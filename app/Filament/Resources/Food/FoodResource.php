<?php

namespace App\Filament\Resources\Food;

use App\Filament\Resources\Food\Pages\CreateFood;
use App\Filament\Resources\Food\Pages\EditFood;
use App\Filament\Resources\Food\Pages\ListFood;
use App\Filament\Resources\Food\Pages\ViewFood;
use App\Filament\Resources\Food\RelationManagers\PortionsRelationManager;
use App\Filament\Resources\Food\Schemas\FoodForm;
use App\Filament\Resources\Food\Schemas\FoodInfolist;
use App\Filament\Resources\Food\Tables\FoodTable;
use App\Models\Food;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FoodResource extends Resource
{
    protected static ?string $model = Food::class;

    protected static string|UnitEnum|null $navigationGroup = 'Nutrición';

    protected static ?string $navigationLabel = 'Alimentos';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'alimento';

    protected static ?string $pluralModelLabel = 'alimentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    public static function form(Schema $schema): Schema
    {
        return FoodForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FoodInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FoodTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PortionsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Food::query()->whereNull('verified_at')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFood::route('/'),
            'create' => CreateFood::route('/create'),
            'view' => ViewFood::route('/{record}'),
            'edit' => EditFood::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
