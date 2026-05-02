<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Models\Trip;
use App\Models\User;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'الرحلات';

    protected static ?string $modelLabel = 'رحلة';

    protected static ?string $pluralModelLabel = 'الرحلات';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة الرحلات';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات الرحلة')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('العميل')
                            ->relationship('customer', 'name', fn($query) => $query->where('role', 'customer'))
                            ->searchable()
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('driver_id')
                            ->label('السائق')
                            ->relationship('driver', 'name', fn($query) => $query->where('role', 'driver'))
                            ->searchable()
                            ->native(false),

                        Forms\Components\Select::make('category')
                            ->label('الفئة')
                            ->options([
                                'economy' => 'توفير',
                                'vip' => 'VIP',
                                'bus' => 'باص',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'accepted' => 'مقبولة',
                                'started' => 'جارية',
                                'completed' => 'مكتملة',
                                'cancelled' => 'ملغاة',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('موقع الانطلاق')
                    ->schema([
                        Forms\Components\TextInput::make('pickup_address')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('pickup_latitude')
                            ->label('خط العرض')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('pickup_longitude')
                            ->label('خط الطول')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('موقع الوصول')
                    ->schema([
                        Forms\Components\TextInput::make('dropoff_address')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('dropoff_latitude')
                            ->label('خط العرض')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('dropoff_longitude')
                            ->label('خط الطول')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('تفاصيل السعر')
                    ->schema([
                        Forms\Components\TextInput::make('distance_km')
                            ->label('المسافة (كم)')
                            ->numeric()
                            ->required()
                            ->minValue(0),

                        Forms\Components\TextInput::make('estimated_price')
                            ->label('السعر المتوقع')
                            ->numeric()
                            ->prefix('ر.ي'),

                        Forms\Components\TextInput::make('final_price')
                            ->label('السعر النهائي')
                            ->numeric()
                            ->prefix('ر.ي'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الرحلة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label('السائق')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد')
                    ->color(fn($state) => $state === 'غير محدد' ? 'gray' : null),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('الفئة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'economy' => 'توفير',
                        'vip' => 'VIP',
                        'bus' => 'باص',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'economy',
                        'warning' => 'vip',
                        'primary' => 'bus',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'accepted' => 'مقبولة',
                        'started' => 'جارية',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'accepted',
                        'primary' => 'started',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('distance_km')
                    ->label('المسافة')
                    ->numeric(2)
                    ->suffix(' كم')
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('السعر')
                    ->money('YER')
                    ->sortable()
                    ->default('غير محدد'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'accepted' => 'مقبولة',
                        'started' => 'جارية',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('category')
                    ->label('الفئة')
                    ->options([
                        'economy' => 'توفير',
                        'vip' => 'VIP',
                        'bus' => 'باص',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),

                \Filament\Actions\Action::make('accept_trip')
                    ->label('قبول')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('driver_id')
                            ->label('تعيين سائق')
                            ->options(User::where('role', 'driver')->where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->native(false)
                            ->searchable(),
                    ])
                    ->action(function (Trip $record, array $data) {
                        $record->update(['status' => 'accepted', 'driver_id' => $data['driver_id']]);
                        Notification::make()->title('تم قبول الرحلة وتعيين سائق')->success()->send();
                    })
                    ->visible(fn(Trip $record) => $record->status === 'pending'),

                \Filament\Actions\Action::make('start_trip')
                    ->label('بدء')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function (Trip $record) {
                        $record->update(['status' => 'started', 'started_at' => now()]);
                        Notification::make()->title('بدأت الرحلة')->info()->send();
                    })
                    ->visible(fn(Trip $record) => $record->status === 'accepted'),

                \Filament\Actions\Action::make('complete_trip')
                    ->label('إنهاء')
                    ->icon('heroicon-o-flag')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('إنهاء الرحلة')
                    ->modalDescription('سيتم إنشاء معاملة مالية تلقائياً.')
                    ->action(function (Trip $record) {
                        $record->update(['status' => 'completed', 'completed_at' => now()]);

                        // إنشاء معاملة مالية تلقائية
                        $price = $record->final_price ?? $record->estimated_price ?? 0;
                        $commission = round($price * 0.20, 2);

                        Transaction::create([
                            'trip_id' => $record->id,
                            'driver_id' => $record->driver_id,
                            'customer_id' => $record->customer_id,
                            'trip_price' => $price,
                            'app_commission' => $commission,
                            'driver_earnings' => $price - $commission,
                            'payment_method' => 'cash',
                            'payment_status' => 'pending',
                        ]);

                        Notification::make()->title('اكتملت الرحلة + تم إنشاء المعاملة المالية')->success()->send();
                    })
                    ->visible(fn(Trip $record) => $record->status === 'started'),

                \Filament\Actions\Action::make('cancel_trip')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (Trip $record, array $data) {
                        $record->update([
                            'status' => 'cancelled',
                            'cancellation_reason' => $data['cancellation_reason'],
                        ]);
                        Notification::make()->title('تم إلغاء الرحلة')->danger()->send();
                    })
                    ->visible(fn(Trip $record) => in_array($record->status, ['pending', 'accepted'])),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات الرحلة')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('رقم الرحلة'),
                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('العميل'),
                        Infolists\Components\TextEntry::make('driver.name')
                            ->label('السائق')
                            ->default('غير محدد'),
                        Infolists\Components\TextEntry::make('category')
                            ->label('الفئة')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'economy' => 'توفير',
                                'vip' => 'VIP',
                                'bus' => 'باص',
                                default => $state,
                            })
                            ->badge(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'pending' => 'قيد الانتظار',
                                'accepted' => 'مقبولة',
                                'started' => 'جارية',
                                'completed' => 'مكتملة',
                                'cancelled' => 'ملغاة',
                                default => $state,
                            })
                            ->badge(),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('المسار')
                    ->schema([
                        Infolists\Components\TextEntry::make('pickup_address')
                            ->label('موقع الانطلاق'),
                        Infolists\Components\TextEntry::make('dropoff_address')
                            ->label('موقع الوصول'),
                        Infolists\Components\TextEntry::make('distance_km')
                            ->label('المسافة')
                            ->suffix(' كم'),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('التفاصيل المالية')
                    ->schema([
                        Infolists\Components\TextEntry::make('estimated_price')
                            ->label('السعر المتوقع')
                            ->money('YER'),
                        Infolists\Components\TextEntry::make('final_price')
                            ->label('السعر النهائي')
                            ->money('YER'),
                    ])
                    ->columns(2),
            ]);
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
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'view' => Pages\ViewTrip::route('/{record}'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }

    // ─── بحث شامل ───
    protected static ?string $recordTitleAttribute = 'pickup_address';

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'pickup_address', 'dropoff_address', 'customer.name', 'driver.name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'العميل' => $record->customer?->name ?? '—',
            'السائق' => $record->driver?->name ?? 'غير محدد',
            'الحالة' => match ($record->status) {
                'pending' => '⏳ انتظار', 'accepted' => '✅ مقبولة', 'started' => '🚗 جارية',
                'completed' => '✔️ مكتملة', 'cancelled' => '❌ ملغاة', default => $record->status,
            },
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'started'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
