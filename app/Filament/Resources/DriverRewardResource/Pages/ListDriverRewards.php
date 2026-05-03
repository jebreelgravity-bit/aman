<?php

namespace App\Filament\Resources\DriverRewardResource\Pages;

use App\Filament\Resources\DriverRewardResource;
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
