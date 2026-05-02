<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use App\Filament\Exports\ComplaintExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComplaints extends ListRecords
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(ComplaintExporter::class)
                ->label('📥 تصدير Excel')
                ->color('success'),
            Actions\CreateAction::make(),
        ];
    }
}
