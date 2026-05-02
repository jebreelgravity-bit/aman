<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'customer';
        return parent::mutateFormDataBeforeCreate($data);
    }
    protected static string $resource = CustomerResource::class;
}
