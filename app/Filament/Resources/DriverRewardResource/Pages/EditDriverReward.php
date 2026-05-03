<?php

namespace App\Filament\Resources\DriverRewardResource\Pages;

use App\Filament\Resources\DriverRewardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDriverReward extends EditRecord
{
    protected static string $resource = DriverRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
