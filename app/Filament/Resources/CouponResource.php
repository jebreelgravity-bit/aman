<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'الكوبونات';

    protected static ?string $modelLabel = 'كوبون';

    protected static ?string $pluralModelLabel = 'الكوبونات';

    protected static \UnitEnum|string|null $navigationGroup = 'التسويق';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات الكوبون')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('الكود')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->alphaDash()
                            ->uppercase(),

                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('تفاصيل الخصم')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('نوع الخصم')
                            ->options([
                                'percentage' => 'نسبة مئوية',
                                'fixed' => 'مبلغ ثابت',
                            ])
                            ->required()
                            ->reactive()
                            ->native(false),

                        Forms\Components\TextInput::make('value')
                            ->label('القيمة')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn($get) => $get('type') === 'percentage' ? '%' : 'ر.ي'),

                        Forms\Components\TextInput::make('max_discount')
                            ->label('الحد الأقصى للخصم')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ر.ي')
                            ->visible(fn($get) => $get('type') === 'percentage'),

                        Forms\Components\TextInput::make('min_trip_amount')
                            ->label('الحد الأدنى لسعر الرحلة')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ر.ي'),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('حدود الاستخدام')
                    ->schema([
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('عدد مرات الاستخدام الكلي')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('اتركه فارغاً للاستخدام غير المحدود'),

                        Forms\Components\TextInput::make('usage_per_user')
                            ->label('عدد مرات الاستخدام لكل مستخدم')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('تاريخ البدء')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->native(false),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('الاستهداف')
                    ->schema([
                        Forms\Components\Select::make('user_type')
                            ->label('نوع المستخدم')
                            ->options([
                                'all' => 'الكل',
                                'new' => 'عملاء جدد',
                                'existing' => 'عملاء حاليون',
                            ])
                            ->default('all')
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('allowed_categories')
                            ->label('الفئات المسموحة')
                            ->options([
                                'economy' => 'توفير',
                                'vip' => 'VIP',
                                'bus' => 'باص',
                            ])
                            ->multiple()
                            ->native(false)
                            ->helperText('اتركه فارغاً للسماح بجميع الفئات'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'percentage' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'percentage',
                        'info' => 'fixed',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->type === 'percentage'
                        ? $record->value . '%'
                        : number_format($record->value, 0) . ' ر.ي'
                    ),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('مرات الاستخدام')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->used_count . ($record->usage_limit ? ' / ' . $record->usage_limit : '')
                    )
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('يبدأ في')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('ينتهي في')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'percentage' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                    ])
                    ->native(false),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
