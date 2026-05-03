<?php

namespace App\Filament\Resources;

use App\Models\WebBanner;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\WebBannerResource\Pages;

class WebBannerResource extends Resource
{
    protected static ?string $model = WebBanner::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'الإعلانات والبانرات';
    protected static ?string $modelLabel = 'بانر';
    protected static ?string $pluralModelLabel = 'الإعلانات والبانرات';
    protected static \UnitEnum|string|null $navigationGroup = 'التسويق والعروض';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            \Filament\Schemas\Components\Section::make('محتوى الإعلان')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('العنوان')
                        ->required()->maxLength(255),

                    Forms\Components\TextInput::make('subtitle')
                        ->label('العنوان الفرعي')
                        ->maxLength(255),

                    Forms\Components\FileUpload::make('image')
                        ->label('الصورة (اختياري)')
                        ->image()->directory('banners')->maxSize(2048)->nullable(),

                    Forms\Components\TextInput::make('link')
                        ->label('الرابط عند النقر')
                        ->url()->nullable(),
                ])->columns(2),

            \Filament\Schemas\Components\Section::make('التصميم والموضع')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('نوع البانر')
                        ->options([
                            'top_bar' => 'شريط علوي',
                            'popup'   => 'إعلان منبثق (Popup)',
                            'card'    => 'بطاقة إعلانية',
                            'full'    => 'بانر كامل الشاشة',
                        ])->required()->native(false),

                    Forms\Components\Select::make('position')
                        ->label('مكان الظهور')
                        ->options([
                            'home'    => 'الصفحة الرئيسية',
                            'booking' => 'صفحة الحجز',
                            'all'     => 'كل الصفحات',
                        ])->required()->native(false),

                    Forms\Components\ColorPicker::make('bg_color')
                        ->label('لون الخلفية')->default('#FFD700'),

                    Forms\Components\ColorPicker::make('text_color')
                        ->label('لون النص')->default('#000000'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('الترتيب')->numeric()->default(0),
                ])->columns(2),

            \Filament\Schemas\Components\Section::make('الجدول الزمني')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط')->default(true),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('تاريخ البداية'),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('تاريخ الانتهاء'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label('الصورة')->square(),
            Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable(),
            Tables\Columns\BadgeColumn::make('type')->label('النوع')->formatStateUsing(fn($s) => match($s) {
                'top_bar' => 'شريط علوي', 'popup' => 'Popup',
                'card' => 'بطاقة', 'full' => 'كامل', default => $s,
            }),
            Tables\Columns\BadgeColumn::make('position')->label('المكان'),
            Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            Tables\Columns\TextColumn::make('end_date')->label('ينتهي')->date('Y-m-d'),
            Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('is_active')->label('الحالة'),
            Tables\Filters\SelectFilter::make('type')->label('النوع')
                ->options(['top_bar'=>'شريط','popup'=>'Popup','card'=>'بطاقة','full'=>'كامل']),
        ])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWebBanners::route('/'),
            'create' => Pages\CreateWebBanner::route('/create'),
            'edit'   => Pages\EditWebBanner::route('/{record}/edit'),
        ];
    }
}
