<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'admin';
        return parent::mutateFormDataBeforeCreate($data);
    }
    protected static string $resource = AdminResource::class;
}
