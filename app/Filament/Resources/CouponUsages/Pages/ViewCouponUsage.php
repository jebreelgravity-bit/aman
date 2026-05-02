<?php

namespace App\Filament\Resources\CouponUsages\Pages;

use App\Filament\Resources\CouponUsages\CouponUsageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCouponUsage extends ViewRecord
{
    protected static string $resource = CouponUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
