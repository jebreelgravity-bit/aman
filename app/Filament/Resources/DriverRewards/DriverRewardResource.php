<?php

namespace App\Filament\Resources\DriverRewards;

use App\Filament\Resources\DriverRewards\Pages\CreateDriverReward;
use App\Filament\Resources\DriverRewards\Pages\EditDriverReward;
use App\Filament\Resources\DriverRewards\Pages\ListDriverRewards;
use App\Filament\Resources\DriverRewards\Schemas\DriverRewardForm;
use App\Filament\Resources\DriverRewards\Tables\DriverRewardsTable;
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
