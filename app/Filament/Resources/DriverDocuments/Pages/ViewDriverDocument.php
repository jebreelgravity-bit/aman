<?php

namespace App\Filament\Resources\DriverDocuments\Pages;

use App\Filament\Resources\DriverDocuments\DriverDocumentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDriverDocument extends ViewRecord
{
    protected static string $resource = DriverDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
