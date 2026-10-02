<?php

namespace App\Filament\Resources\AiFoodLookups\Pages;

use App\Filament\Resources\AiFoodLookups\AiFoodLookupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiFoodLookups extends ListRecords
{
    protected static string $resource = AiFoodLookupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
