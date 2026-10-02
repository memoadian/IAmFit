<?php

namespace App\Filament\Resources\Food\Pages;

use App\Filament\Resources\Food\FoodResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFood extends ViewRecord
{
    protected static string $resource = FoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
