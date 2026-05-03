<?php
namespace App\Filament\Resources\LongDistanceTripResource\Pages;
use App\Filament\Resources\LongDistanceTripResource;
use Filament\Resources\Pages\CreateRecord;
class CreateLongDistanceTrip extends CreateRecord {
    protected static string $resource = LongDistanceTripResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
