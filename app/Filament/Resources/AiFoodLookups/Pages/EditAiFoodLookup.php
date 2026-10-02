<?php

namespace App\Filament\Resources\AiFoodLookups\Pages;

use App\Filament\Resources\AiFoodLookups\AiFoodLookupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAiFoodLookup extends EditRecord
{
    protected static string $resource = AiFoodLookupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
