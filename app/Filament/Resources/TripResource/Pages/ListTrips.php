<?php

namespace App\Filament\Resources\TripResource\Pages;

use App\Filament\Resources\TripResource;
use App\Filament\Exports\TripExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrips extends ListRecords
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(TripExporter::class)
                ->label('📥 تصدير Excel')
                ->color('success'),
            Actions\CreateAction::make(),
        ];
    }
}
