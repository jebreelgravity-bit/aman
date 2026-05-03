<?php
namespace App\Filament\Resources\LongDistanceTripResource\Pages;
use App\Filament\Resources\LongDistanceTripResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListLongDistanceTrips extends ListRecords {
    protected static string $resource = LongDistanceTripResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
