<?php

namespace App\Filament\Resources\PopupInteractions\Pages;

use App\Filament\Resources\PopupInteractions\PopupInteractionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPopupInteraction extends ViewRecord
{
    protected static string $resource = PopupInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
