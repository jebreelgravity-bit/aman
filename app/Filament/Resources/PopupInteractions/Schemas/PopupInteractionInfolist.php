<?php

namespace App\Filament\Resources\PopupInteractions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PopupInteractionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('popup_id')
                    ->numeric(),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('action')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
