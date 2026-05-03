<?php

namespace App\Filament\Resources\RatingResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Schema;

class RatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات التقييم الأساسية')
                    ->description('الأطراف المعنية بهذا التقييم')
                    ->schema([
                        Select::make('trip_id')
                            ->label('الرحلة المقيمة')
                            ->relationship('trip', 'id')
                            ->searchable()
                            ->required(),

                        Select::make('customer_id')
                            ->label('الراكب المُقيِّم')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('driver_id')
                            ->label('الكابتن (السائق)')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->required(),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('التقييم بالنجوم (1 إلى 5)')
                    ->description('بعد انتهاء الرحلة، يقيّم الراكب السائق والخدمة والنظافة')
                    ->schema([
                        Select::make('driver_rating')
                            ->label('⭐ تقييم السائق')
                            ->options([
                                1 => '⭐ ضعيف (1)',
                                2 => '⭐⭐ مقبول (2)',
                                3 => '⭐⭐⭐ جيد (3)',
                                4 => '⭐⭐⭐⭐ جيد جداً (4)',
                                5 => '⭐⭐⭐⭐⭐ ممتاز (5)',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('service_rating')
                            ->label('⭐ تقييم الخدمة')
                            ->options([
                                1 => '⭐ ضعيف (1)',
                                2 => '⭐⭐ مقبول (2)',
                                3 => '⭐⭐⭐ جيد (3)',
                                4 => '⭐⭐⭐⭐ جيد جداً (4)',
                                5 => '⭐⭐⭐⭐⭐ ممتاز (5)',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('cleanliness_rating')
                            ->label('⭐ تقييم نظافة السيارة')
                            ->options([
                                1 => '⭐ ضعيف (1)',
                                2 => '⭐⭐ مقبول (2)',
                                3 => '⭐⭐⭐ جيد (3)',
                                4 => '⭐⭐⭐⭐ جيد جداً (4)',
                                5 => '⭐⭐⭐⭐⭐ ممتاز (5)',
                            ])
                            ->native(false)
                            ->helperText('اختياري'),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('ملاحظات الراكب')
                    ->description('تعليقات ووسوم لمساعدتنا في تحسين جودة الخدمة')
                    ->schema([
                        Textarea::make('comment')
                            ->label('التعليق المكتوب')
                            ->placeholder('مثال: السائق كان مهذباً والرحلة ممتازة...')
                            ->rows(3)
                            ->columnSpanFull(),

                        TagsInput::make('tags')
                            ->label('وسوم سريعة')
                            ->placeholder('اضغط Enter لإضافة وسم')
                            ->suggestions([
                                'مهذب', 'سريع', 'آمن', 'سيارة نظيفة',
                                'ملتزم بالمسار', 'تكييف ممتاز', 'محادثة لطيفة',
                                'متأخر', 'قيادة متهورة', 'سيارة متسخة',
                            ])
                            ->helperText('وسوم جاهزة: مهذب، سريع، آمن، سيارة نظيفة...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
