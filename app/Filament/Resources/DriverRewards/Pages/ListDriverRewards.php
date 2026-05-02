<?php

namespace App\Filament\Resources\DriverRewards\Pages;

use App\Filament\Resources\DriverRewards\DriverRewardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDriverRewards extends ListRecords
{
    protected static string $resource = DriverRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
