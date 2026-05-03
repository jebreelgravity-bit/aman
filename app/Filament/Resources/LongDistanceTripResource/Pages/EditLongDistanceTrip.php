<?php
namespace App\Filament\Resources\LongDistanceTripResource\Pages;
use App\Filament\Resources\LongDistanceTripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditLongDistanceTrip extends EditRecord {
    protected static string $resource = LongDistanceTripResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
