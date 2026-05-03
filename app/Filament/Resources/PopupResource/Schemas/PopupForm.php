<?php

namespace App\Filament\Resources\PopupResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PopupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('المحتوى الإعلاني')
                    ->schema([
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required(),

                        FileUpload::make('image_url')
                            ->label('الصورة')
                            ->image()
                            ->directory('popups'),

                        Textarea::make('content')
                            ->label('نص الإعلان')
                            ->required()
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('إجراءات الإعلان')
                    ->schema([
                        TextInput::make('button_text')
                            ->label('نص الزر')
                            ->default(null),

                        TextInput::make('button_url')
                            ->label('رابط الزر')
                            ->url()
                            ->default(null),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('الاستهداف والظهور')
                    ->schema([
                        Select::make('target_audience')
                            ->label('الجمهور المستهدف')
                            ->options([
                                'all' => 'الجميع',
                                'customers' => 'الركاب فقط',
                                'drivers' => 'الكباتن فقط',
                                'new_users' => 'العملاء الجدد فقط (أقل من 7 أيام)',
                                'inactive_users' => 'العملاء غير النشطين (أكثر من 30 يوم بدون رحلة)',
                            ])
                            ->default('all')
                            ->native(false)
                            ->required(),

                        Select::make('display_frequency')
                            ->label('تكرار الظهور')
                            ->options([
                                'once' => 'مرة واحدة فقط',
                                'daily' => 'مرة واحدة يومياً',
                                'always' => 'دائماً'
                            ])
                            ->default('once')
                            ->native(false)
                            ->required(),

                        Textarea::make('target_cities')
                            ->label('المدن المستهدفة')
                            ->default(null)
                            ->helperText('اتركه فارغاً لاستهداف جميع المدن')
                            ->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('الجدولة والإحصائيات')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('يبدأ في (تاريخ العرض)'),

                        DateTimePicker::make('expires_at')
                            ->label('ينتهي في'),

                        TextInput::make('priority')
                            ->label('الأولوية (الترتيب)')
                            ->required()
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('نشط للاستخدام')
                            ->default(true)
                            ->required(),

                        TextInput::make('views_count')
                            ->label('عدد المشاهدات')
                            ->disabled()
                            ->numeric()
                            ->default(0),

                        TextInput::make('clicks_count')
                            ->label('عدد النقرات')
                            ->disabled()
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }
}
