<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSubscriptionResource\Pages\CreateUserSubscription;
use App\Filament\Resources\UserSubscriptionResource\Pages\EditUserSubscription;
use App\Filament\Resources\UserSubscriptionResource\Pages\ListUserSubscriptions;
use App\Filament\Resources\UserSubscriptionResource\Pages\ViewUserSubscription;
use App\Filament\Resources\UserSubscriptionResource\Schemas\UserSubscriptionForm;
use App\Filament\Resources\UserSubscriptionResource\Schemas\UserSubscriptionInfolist;
use App\Filament\Resources\UserSubscriptionResource\Tables\UserSubscriptionsTable;
use App\Models\UserSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;
    protected static \UnitEnum|string|null $navigationGroup = 'المالية والمحافظ';
    protected static ?string $modelLabel = 'اشتراك مستخدم';
    protected static ?string $pluralModelLabel = 'اشتراكات المستخدمين';
    protected static ?string $navigationLabel = 'اشتراكات المستخدمين';

    public static function form(Schema $schema): Schema
    {
        return UserSubscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserSubscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserSubscriptionsTable::configure($table);
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
            'index' => ListUserSubscriptions::route('/'),
            'create' => CreateUserSubscription::route('/create'),
            'view' => ViewUserSubscription::route('/{record}'),
            'edit' => EditUserSubscription::route('/{record}/edit'),
        ];
    }
}
