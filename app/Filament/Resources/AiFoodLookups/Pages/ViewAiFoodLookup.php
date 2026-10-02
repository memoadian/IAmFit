<?php

namespace App\Filament\Resources\AiFoodLookups\Pages;

use App\Filament\Resources\AiFoodLookups\AiFoodLookupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAiFoodLookup extends ViewRecord
{
    protected static string $resource = AiFoodLookupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
