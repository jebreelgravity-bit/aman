<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponUsageResource\Pages\CreateCouponUsage;
use App\Filament\Resources\CouponUsageResource\Pages\EditCouponUsage;
use App\Filament\Resources\CouponUsageResource\Pages\ListCouponUsages;
use App\Filament\Resources\CouponUsageResource\Pages\ViewCouponUsage;
use App\Filament\Resources\CouponUsageResource\Schemas\CouponUsageForm;
use App\Filament\Resources\CouponUsageResource\Schemas\CouponUsageInfolist;
use App\Filament\Resources\CouponUsageResource\Tables\CouponUsagesTable;
use App\Models\CouponUsage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CouponUsageResource extends Resource
{
    protected static ?string $model = CouponUsage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;
    protected static \UnitEnum|string|null $navigationGroup = 'التسويق والعروض';
    protected static ?string $modelLabel = 'استخدام كوبون';
    protected static ?string $pluralModelLabel = 'استخدامات الكوبونات';
    protected static ?string $navigationLabel = 'استخدامات الكوبونات';

    public static function form(Schema $schema): Schema
    {
        return CouponUsageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CouponUsageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponUsagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCouponUsages::route('/'),
            'view' => ViewCouponUsage::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('created_at', '>=', now()->subDays(7))->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'استخدامات الكوبونات آخر 7 أيام';
    }
}
