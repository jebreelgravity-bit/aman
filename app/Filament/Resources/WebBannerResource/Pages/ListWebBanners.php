<?php
namespace App\Filament\Resources\WebBannerResource\Pages;
use App\Filament\Resources\WebBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListWebBanners extends ListRecords {
    protected static string $resource = WebBannerResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
