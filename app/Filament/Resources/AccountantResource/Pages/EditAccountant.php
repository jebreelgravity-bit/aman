<?php

namespace App\Filament\Resources\AccountantResource\Pages;

use App\Filament\Resources\AccountantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountant extends EditRecord
{
    protected static string $resource = AccountantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
