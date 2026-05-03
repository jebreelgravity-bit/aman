<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopupResource\Pages\CreatePopup;
use App\Filament\Resources\PopupResource\Pages\EditPopup;
use App\Filament\Resources\PopupResource\Pages\ListPopups;
use App\Filament\Resources\PopupResource\Schemas\PopupForm;
use App\Filament\Resources\PopupResource\Tables\PopupsTable;
use App\Models\Popup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedMegaphone;
    protected static \UnitEnum|string|null $navigationGroup = 'التسويق والتنبيهات';
    protected static ?string $modelLabel = 'نافذة منبثقة';
    protected static ?string $pluralModelLabel = 'النوافذ المنبثقة';
    protected static ?string $navigationLabel = 'الإعلانات المنبثقة';
    

    

    public static function form(Schema $schema): Schema
    {
        return PopupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PopupsTable::configure($table);
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
            'index' => ListPopups::route('/'),
            'create' => CreatePopup::route('/create'),
            'edit' => EditPopup::route('/{record}/edit'),
        ];
    }
}
