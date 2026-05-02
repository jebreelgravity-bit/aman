<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DynamicPricingRuleResource\Pages;
use App\Models\DynamicPricingRule;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DynamicPricingRuleResource extends Resource
{
    protected static ?string $model = DynamicPricingRule::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'التسعير الديناميكي';

    protected static ?string $modelLabel = 'قاعدة تسعير';

    protected static ?string $pluralModelLabel = 'قواعد التسعير الديناميكي';

    protected static \UnitEnum|string|null $navigationGroup = 'الإعدادات المالية';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات القاعدة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم القاعدة')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('priority')
                            ->label('الأولوية')
                            ->numeric()
                            ->default(0)
                            ->helperText('القواعد ذات الأولوية الأعلى تطبق أولاً'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('شروط التطبيق')
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('وقت البدء')
                            ->seconds(false),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('وقت الانتهاء')
                            ->seconds(false),

                        Forms\Components\Select::make('days_of_week')
                            ->label('أيام الأسبوع')
                            ->options([
                                0 => 'الأحد',
                                1 => 'الإثنين',
                                2 => 'الثلاثاء',
                                3 => 'الأربعاء',
                                4 => 'الخميس',
                                5 => 'الجمعة',
                                6 => 'السبت',
                            ])
                            ->multiple()
                            ->native(false),

                        Forms\Components\Select::make('categories')
                            ->label('الفئات')
                            ->options([
                                'economy' => 'توفير',
                                'vip' => 'VIP',
                                'bus' => 'باص',
                            ])
                            ->multiple()
                            ->native(false),

                        Forms\Components\TagsInput::make('cities')
                            ->label('المدن')
                            ->placeholder('أضف مدينة')
                            ->helperText('اضغط Enter بعد كل مدينة'),

                        Forms\Components\TagsInput::make('areas')
                            ->label('المناطق')
                            ->placeholder('أضف منطقة')
                            ->helperText('اضغط Enter بعد كل منطقة'),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('تعديل السعر')
                    ->schema([
                        Forms\Components\Select::make('adjustment_type')
                            ->label('نوع التعديل')
                            ->options([
                                'multiplier' => 'مضاعف (مثال: 1.5 = زيادة 50%)',
                                'percentage' => 'نسبة مئوية (مثال: 20 = زيادة 20%)',
                                'fixed_increase' => 'زيادة ثابتة (مثال: 500 ر.ي)',
                            ])
                            ->required()
                            ->reactive()
                            ->native(false),

                        Forms\Components\TextInput::make('adjustment_value')
                            ->label('قيمة التعديل')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText(fn($get) => match ($get('adjustment_type')) {
                                'multiplier' => 'أدخل رقم (1.5 يعني زيادة 50%)',
                                'percentage' => 'أدخل نسبة مئوية (20 يعني زيادة 20%)',
                                'fixed_increase' => 'أدخل المبلغ بالريال',
                                default => '',
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم القاعدة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('time_range')
                    ->label('الوقت')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->start_time && $record->end_time
                        ? substr($record->start_time, 0, 5) . ' - ' . substr($record->end_time, 0, 5)
                        : 'طوال اليوم'
                    ),

                Tables\Columns\BadgeColumn::make('adjustment_type')
                    ->label('نوع التعديل')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'multiplier' => 'مضاعف',
                        'percentage' => 'نسبة مئوية',
                        'fixed_increase' => 'زيادة ثابتة',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'multiplier',
                        'info' => 'percentage',
                        'success' => 'fixed_increase',
                    ]),

                Tables\Columns\TextColumn::make('adjustment_value')
                    ->label('القيمة')
                    ->formatStateUsing(fn($record) => match ($record->adjustment_type) {
                        'multiplier' => '×' . $record->adjustment_value,
                        'percentage' => '+' . $record->adjustment_value . '%',
                        'fixed_increase' => '+' . number_format($record->adjustment_value, 0) . ' ر.ي',
                        default => $record->adjustment_value,
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('الأولوية')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

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
            ->defaultSort('priority', 'desc');
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
            'index' => Pages\ListDynamicPricingRules::route('/'),
            'create' => Pages\CreateDynamicPricingRule::route('/create'),
            'edit' => Pages\EditDynamicPricingRule::route('/{record}/edit'),
        ];
    }
}
