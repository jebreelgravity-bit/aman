<?php
namespace App\Filament\Resources\WebBannerResource\Pages;
use App\Filament\Resources\WebBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditWebBanner extends EditRecord {
    protected static string $resource = WebBannerResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
