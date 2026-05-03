<?php

namespace App\Filament\Resources\AccountantResource\Pages;

use App\Filament\Resources\AccountantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountant extends ViewRecord
{
    protected static string $resource = AccountantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
