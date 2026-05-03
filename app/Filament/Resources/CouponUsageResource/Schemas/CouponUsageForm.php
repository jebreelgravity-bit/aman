<?php

namespace App\Filament\Resources\CouponUsageResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CouponUsageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('coupon_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('trip_id')
                    ->required()
                    ->numeric(),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric(),
            ]);
    }
}
