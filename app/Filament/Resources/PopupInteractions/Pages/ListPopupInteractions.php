<?php

namespace App\Filament\Resources\PopupInteractions\Pages;

use App\Filament\Resources\PopupInteractions\PopupInteractionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPopupInteractions extends ListRecords
{
    protected static string $resource = PopupInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
