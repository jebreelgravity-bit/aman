<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverRewardResource\Pages\CreateDriverReward;
use App\Filament\Resources\DriverRewardResource\Pages\EditDriverReward;
use App\Filament\Resources\DriverRewardResource\Pages\ListDriverRewards;
use App\Filament\Resources\DriverRewardResource\Schemas\DriverRewardForm;
use App\Filament\Resources\DriverRewardResource\Tables\DriverRewardsTable;
use App\Models\DriverReward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DriverRewardResource extends Resource
{
    protected static ?string $model = DriverReward::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedGift;
    protected static \UnitEnum|string|null $navigationGroup = 'المالية والمحافظ';
    protected static ?string $modelLabel = 'مكافأة سائق';
    protected static ?string $pluralModelLabel = 'مكافآت السائقين';
    protected static ?string $navigationLabel = 'المكافآت';
    

    

    public static function form(Schema $schema): Schema
    {
        return DriverRewardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriverRewardsTable::configure($table);
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
            'index' => ListDriverRewards::route('/'),
            'create' => CreateDriverReward::route('/create'),
            'edit' => EditDriverReward::route('/{record}/edit'),
        ];
    }
}
