<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopupInteractionResource\Pages\CreatePopupInteraction;
use App\Filament\Resources\PopupInteractionResource\Pages\EditPopupInteraction;
use App\Filament\Resources\PopupInteractionResource\Pages\ListPopupInteractions;
use App\Filament\Resources\PopupInteractionResource\Pages\ViewPopupInteraction;
use App\Filament\Resources\PopupInteractionResource\Schemas\PopupInteractionForm;
use App\Filament\Resources\PopupInteractionResource\Schemas\PopupInteractionInfolist;
use App\Filament\Resources\PopupInteractionResource\Tables\PopupInteractionsTable;
use App\Models\PopupInteraction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PopupInteractionResource extends Resource
{
    protected static ?string $model = PopupInteraction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;
    protected static \UnitEnum|string|null $navigationGroup = 'التسويق والعروض';
    protected static ?string $modelLabel = 'تفاعل مع إعلان';
    protected static ?string $pluralModelLabel = 'تفاعلات الإعلانات';
    protected static ?string $navigationLabel = 'تفاعلات الإعلانات';

    public static function form(Schema $schema): Schema
    {
        return PopupInteractionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PopupInteractionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PopupInteractionsTable::configure($table);
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
            'index' => ListPopupInteractions::route('/'),
            'create' => CreatePopupInteraction::route('/create'),
            'view' => ViewPopupInteraction::route('/{record}'),
            'edit' => EditPopupInteraction::route('/{record}/edit'),
        ];
    }
}
