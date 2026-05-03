<?php

namespace App\Filament\Resources;

use App\Models\LongDistanceTrip;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\LongDistanceTripResource\Pages;

class LongDistanceTripResource extends Resource
{
    protected static ?string $model = LongDistanceTrip::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'الرحلات الطويلة';
    protected static ?string $modelLabel = 'رحلة طويلة';
    protected static ?string $pluralModelLabel = 'الرحلات الطويلة';
    protected static \UnitEnum|string|null $navigationGroup = 'إدارة الرحلات';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            \Filament\Schemas\Components\Section::make('بيانات الرحلة')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم الرحلة')
                        ->placeholder('مثال: صنعاء → عدن')
                        ->required()->maxLength(255)->columnSpanFull(),

                    Forms\Components\TextInput::make('origin_city')
                        ->label('مدينة الانطلاق')->required(),

                    Forms\Components\TextInput::make('destination_city')
                        ->label('مدينة الوصول')->required(),

                    Forms\Components\TextInput::make('distance_km')
                        ->label('المسافة (كم)')
                        ->numeric()->step(0.01),

                    Forms\Components\Select::make('vehicle_type')
                        ->label('نوع المركبة')
                        ->options([
                            'سيارة عادية' => 'سيارة عادية',
                            'Rav4'        => 'Rav4',
                            'باص صغير'   => 'باص صغير',
                            'باص كبير'   => 'باص كبير',
                            'Hiace'       => 'Hiace',
                            'VIP'         => 'VIP',
                        ])->native(false),
                ])->columns(2),

            \Filament\Schemas\Components\Section::make('التسعير والسعة')
                ->schema([
                    Forms\Components\TextInput::make('base_price')
                        ->label('السعر الأساسي (ريال)')
                        ->numeric()->required()->prefix('ر.س'),

                    Forms\Components\TextInput::make('max_passengers')
                        ->label('أقصى عدد ركاب')
                        ->numeric()->default(4)->minValue(1)->maxValue(50),
                ])->columns(2),

            \Filament\Schemas\Components\Section::make('جدول المغادرة')
                ->schema([
                    Forms\Components\TimePicker::make('departure_time')
                        ->label('وقت الانطلاق'),

                    Forms\Components\Select::make('frequency')
                        ->label('التكرار')
                        ->options([
                            'daily'  => 'يومي',
                            'weekly' => 'أسبوعي',
                            'custom' => 'مخصص',
                        ])->native(false)->default('daily'),

                    Forms\Components\CheckboxList::make('departure_days')
                        ->label('أيام الانطلاق')
                        ->options([
                            '0' => 'الأحد', '1' => 'الاثنين', '2' => 'الثلاثاء',
                            '3' => 'الأربعاء', '4' => 'الخميس', '5' => 'الجمعة', '6' => 'السبت',
                        ])->columns(4)->columnSpanFull(),
                ])->columns(2),

            \Filament\Schemas\Components\Section::make('السائقون المخصصون')
                ->description('حدد السائقين المتاحين لهذه الرحلة')
                ->schema([
                    Forms\Components\Select::make('drivers')
                        ->label('السائقون')
                        ->multiple()
                        ->relationship('drivers', 'name', fn($q) => $q->where('role', 'driver'))
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),

            \Filament\Schemas\Components\Section::make('ملاحظات والحالة')
                ->schema([
                    Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
                    Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                    Forms\Components\Textarea::make('notes')->label('ملاحظات')->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('الرحلة')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('origin_city')->label('من')->badge()->color('info'),
            Tables\Columns\TextColumn::make('destination_city')->label('إلى')->badge()->color('warning'),
            Tables\Columns\TextColumn::make('base_price')->label('السعر (ر.س)')
                ->money('SAR')->sortable(),
            Tables\Columns\TextColumn::make('vehicle_type')->label('المركبة'),
            Tables\Columns\TextColumn::make('departure_time')->label('موعد الانطلاق'),
            Tables\Columns\TextColumn::make('drivers_count')->counts('drivers')->label('السائقون'),
            Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('is_active')->label('الحالة'),
        ])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->defaultSort('sort_order');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLongDistanceTrips::route('/'),
            'create' => Pages\CreateLongDistanceTrip::route('/create'),
            'edit'   => Pages\EditLongDistanceTrip::route('/{record}/edit'),
        ];
    }
}
