<?php

namespace App\Filament\Resources\PopupInteractions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PopupInteractionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('popup_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Select::make('action')
                    ->options(['viewed' => 'Viewed', 'clicked' => 'Clicked', 'dismissed' => 'Dismissed'])
                    ->required(),
            ]);
    }
}
