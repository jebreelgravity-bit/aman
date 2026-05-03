<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingSettingResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\PricingSetting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PricingSettingResource extends Resource
{
    use HasRoleAccess;

    /** الأدوار المسموح لها بالوصول */
    protected static array $allowedRoles = ['admin', 'super_admin', 'financial_manager'];

    protected static ?string $model = PricingSetting::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'إعدادات الأسعار';

    protected static ?string $modelLabel = 'إعداد سعر';

    protected static ?string $pluralModelLabel = 'إعدادات الأسعار';

    protected static \UnitEnum|string|null $navigationGroup = 'الإعدادات المالية';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('إعدادات الأسعار')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label('الفئة')
                            ->options([
                                'economy' => 'توفير',
                                'vip' => 'VIP',
                                'bus' => 'باص',
                            ])
                            ->required()
                            ->disabled(fn($record) => $record !== null)
                            ->native(false),

                        Forms\Components\TextInput::make('base_fare')
                            ->label('فتحة العداد')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ر.ي')
                            ->helperText('المبلغ الأساسي قبل حساب المسافة'),

                        Forms\Components\TextInput::make('price_per_km')
                            ->label('سعر الكيلومتر')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ر.ي / كم')
                            ->helperText('السعر لكل كيلومتر'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('تفعيل أو تعطيل هذه الفئة'),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('معاينة الحساب')
                    ->schema([
                        Forms\Components\Placeholder::make('calculation_preview')
                            ->label('مثال على الحساب')
                            ->content(function ($get) {
                                $baseFare = $get('base_fare') ?? 0;
                                $pricePerKm = $get('price_per_km') ?? 0;
                                $distance = 10;
                                $total = $baseFare + ($pricePerKm * $distance);

                                return "لرحلة 10 كم: {$baseFare} + ({$pricePerKm} × 10) = " . number_format($total, 0) . " ر.ي";
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ])
                    ->size('lg'),

                Tables\Columns\TextColumn::make('base_fare')
                    ->label('فتحة العداد')
                    ->money('YER')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_per_km')
                    ->label('سعر الكيلومتر')
                    ->formatStateUsing(fn($state) => number_format($state, 0) . ' ر.ي / كم')
                    ->sortable(),

                Tables\Columns\TextColumn::make('example_10km')
                    ->label('مثال (10 كم)')
                    ->formatStateUsing(
                        fn($record) =>
                        number_format($record->base_fare + ($record->price_per_km * 10), 0) . ' ر.ي'
                    )
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('category');
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
            'index' => Pages\ListPricingSettings::route('/'),
            'edit' => Pages\EditPricingSetting::route('/{record}/edit'),
        ];
    }
}
