<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Builder;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class DriverResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'قوابطنة أمان (السائقين)';

    protected static ?string $modelLabel = 'سائق';

    protected static ?string $pluralModelLabel = 'قوابطنة أمان (السائقين)';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة السائقين';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'driver');
    }


    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20),



                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('كلمة المرور')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->same('password')
                            ->dehydrated(false)
                            ->required(fn(string $context): bool => $context === 'create'),
                    ])
                    ->columns(2)
                    ->visible(fn(string $context): bool => $context === 'create' || request()->has('password')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->copyable(),



                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('customerTrips_count')
                    ->label('الرحلات كعميل')
                    ->counts('customerTrips')
                    ->sortable()
                    ->visible(fn($record) => $record?->role === 'customer'),

                Tables\Columns\TextColumn::make('driverTrips_count')
                    ->label('الرحلات كسائق')
                    ->counts('driverTrips')
                    ->sortable()
                    ->visible(fn($record) => $record?->role === 'driver'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([


                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),

                \Filament\Actions\Action::make('suspend')
                    ->label('تعليق الحساب')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تعليق حساب السائق')
                    ->modalDescription('هل أنت متأكد من تعليق هذا الحساب؟ لن يتمكن السائق من استقبال رحلات جديدة.')
                    ->action(function (User $record) {
                        $record->update(['is_active' => false]);
                        Notification::make()->title('تم تعليق حساب السائق')->icon('heroicon-o-no-symbol')->danger()->send();
                    })
                    ->visible(fn(User $record) => $record->is_active),

                \Filament\Actions\Action::make('activate')
                    ->label('تفعيل الحساب')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()->title('تم تفعيل حساب السائق')->icon('heroicon-o-check-circle')->success()->send();
                    })
                    ->visible(fn(User $record) => !$record->is_active),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('bulk_activate')
                        ->label('تفعيل المحددين')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each(fn($r) => $r->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion(),

                    \Filament\Actions\BulkAction::make('bulk_suspend')
                        ->label('تعليق المحددين')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each(fn($r) => $r->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DriverTripsRelationManager::class,
            RelationManagers\DriverRatingsRelationManager::class,
            RelationManagers\DriverTransactionsRelationManager::class,
            RelationManagers\DriverRewardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'view' => Pages\ViewDriver::route('/{record}'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }

    // ─── بحث شامل ───
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'الهاتف' => $record->phone ?? '—',
            'الحالة' => $record->is_active ? '🟢 نشط' : '🔴 معلّق',
        ];
    }

    // ─── عداد التنقل ───
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('is_active', true)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
