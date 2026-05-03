<?php

namespace App\Filament\Resources\UserSubscriptionResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserSubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('subscription_plan_id')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('starts_at')
                    ->date(),
                TextEntry::make('expires_at')
                    ->date(),
                TextEntry::make('next_billing_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('trips_used')
                    ->numeric(),
                TextEntry::make('total_savings')
                    ->numeric(),
                TextEntry::make('verification_document')
                    ->placeholder('-'),
                TextEntry::make('verification_status')
                    ->badge(),
                TextEntry::make('rejection_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
