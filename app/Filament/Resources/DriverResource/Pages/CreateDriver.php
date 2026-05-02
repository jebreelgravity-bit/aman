<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDriver extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'driver';
        return parent::mutateFormDataBeforeCreate($data);
    }
    protected static string $resource = DriverResource::class;
}
