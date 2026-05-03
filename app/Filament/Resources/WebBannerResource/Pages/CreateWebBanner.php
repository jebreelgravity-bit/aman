<?php
namespace App\Filament\Resources\WebBannerResource\Pages;
use App\Filament\Resources\WebBannerResource;
use Filament\Resources\Pages\CreateRecord;
class CreateWebBanner extends CreateRecord {
    protected static string $resource = WebBannerResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
