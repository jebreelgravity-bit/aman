<?php

namespace App\Filament\Resources\PopupInteractions\Pages;

use App\Filament\Resources\PopupInteractions\PopupInteractionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPopupInteraction extends EditRecord
{
    protected static string $resource = PopupInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
