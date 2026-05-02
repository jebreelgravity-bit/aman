<?php

namespace App\Filament\Resources\Ratings;

use App\Filament\Resources\Ratings\Pages\CreateRating;
use App\Filament\Resources\Ratings\Pages\EditRating;
use App\Filament\Resources\Ratings\Pages\ListRatings;
use App\Filament\Resources\Ratings\Schemas\RatingForm;
use App\Filament\Resources\Ratings\Tables\RatingsTable;
use App\Models\Rating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-star';
    protected static \UnitEnum|string|null $navigationGroup = 'إدارة الجودة';
    protected static ?string $modelLabel = 'تقييم';
    protected static ?string $pluralModelLabel = 'التقييمات';
    protected static ?string $navigationLabel = 'تقييمات الرحلات';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RatingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatingsTable::configure($table);
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
            'index' => ListRatings::route('/'),
            'create' => CreateRating::route('/create'),
            'edit' => EditRating::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'تقييمات اليوم';
    }
}
