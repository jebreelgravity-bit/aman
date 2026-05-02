<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists;
use Filament\Schemas\Schema;

class ViewComplaint extends ViewRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                \Filament\Schemas\Components\Section::make('📋 بيانات الشكوى')
                    ->schema([
                        Infolists\Components\TextEntry::make('ticket_number')
                            ->label('رقم التذكرة')
                            ->badge()
                            ->color('primary')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('subject')
                            ->label('الموضوع')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('category')
                            ->label('التصنيف')
                            ->badge()
                            ->formatStateUsing(fn($state) => match ($state) {
                                'delay' => '⏰ تأخير', 'behavior' => '👤 سلوك', 'pricing' => '💰 تسعير',
                                'safety' => '🛡️ أمان', 'cleanliness' => '🧹 نظافة', 'other' => '📌 أخرى',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('priority')
                            ->label('الأولوية')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'urgent' => 'danger', 'high' => 'warning',
                                'medium' => 'info', 'low' => 'success', default => 'gray',
                            })
                            ->formatStateUsing(fn($state) => match ($state) {
                                'urgent' => '🔴 عاجلة', 'high' => '🟠 عالية',
                                'medium' => '🟡 متوسطة', 'low' => '🟢 منخفضة', default => $state,
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'open' => 'danger', 'in_progress' => 'warning',
                                'resolved' => 'success', 'closed' => 'gray', default => 'gray',
                            })
                            ->formatStateUsing(fn($state) => match ($state) {
                                'open' => '🔓 مفتوحة', 'in_progress' => '⚙️ قيد المعالجة',
                                'resolved' => '✅ محلولة', 'closed' => '🔒 مغلقة', default => $state,
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('👥 الأطراف')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('المشتكي'),
                        Infolists\Components\TextEntry::make('driver.name')
                            ->label('السائق المعني')
                            ->default('—'),
                        Infolists\Components\TextEntry::make('trip_id')
                            ->label('رقم الرحلة')
                            ->default('—'),
                        Infolists\Components\TextEntry::make('assignedTo.name')
                            ->label('مسند إلى')
                            ->default('غير مسند')
                            ->badge()
                            ->color(fn($state) => $state === 'غير مسند' ? 'warning' : 'info'),
                    ])
                    ->columns(4),

                \Filament\Schemas\Components\Section::make('📝 التفاصيل والحل')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull()
                            ->prose(),
                        Infolists\Components\TextEntry::make('resolution')
                            ->label('الحل')
                            ->columnSpanFull()
                            ->prose()
                            ->default('لم يتم الحل بعد')
                            ->color(fn($state) => $state === 'لم يتم الحل بعد' ? 'warning' : 'success'),
                    ]),
            ]);
    }
}
