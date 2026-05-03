<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages\CreateActivityLog;
use App\Filament\Resources\ActivityLogResource\Pages\EditActivityLog;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogResource\Schemas\ActivityLogForm;
use App\Filament\Resources\ActivityLogResource\Tables\ActivityLogsTable;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static \UnitEnum|string|null $navigationGroup = 'السجلات والنظام';
    protected static ?string $modelLabel = 'سجل نشاط';
    protected static ?string $pluralModelLabel = 'سجلات الأنشطة';
    protected static ?string $navigationLabel = 'سجلات النظام';
    

    

    public static function form(Schema $schema): Schema
    {
        return ActivityLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
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
            'index' => ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
