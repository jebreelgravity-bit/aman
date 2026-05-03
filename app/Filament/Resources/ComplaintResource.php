<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ComplaintResource extends Resource
{
    use HasRoleAccess;

    /** الأدوار المسموح لها بالوصول */
    protected static array $allowedRoles = ['admin', 'super_admin', 'support_manager'];

    protected static ?string $model = Complaint::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'الشكاوى والتذاكر';

    protected static ?string $modelLabel = 'شكوى';

    protected static ?string $pluralModelLabel = 'الشكاوى';

    protected static \UnitEnum|string|null $navigationGroup = 'دعم العملاء';
    protected static ?int $navigationSort = 1;

    // ─── بحث شامل ───
    protected static ?string $recordTitleAttribute = 'ticket_number';

    public static function getGloballySearchableAttributes(): array
    {
        return ['ticket_number', 'subject', 'user.name', 'driver.name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'المستخدم' => $record->user?->name ?? '—',
            'الأولوية' => match ($record->priority) {
                'urgent' => '🔴 عاجلة', 'high' => '🟠 عالية',
                'medium' => '🟡 متوسطة', 'low' => '🟢 منخفضة', default => $record->priority,
            },
        ];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات التذكرة')
                    ->schema([
                        Forms\Components\TextInput::make('ticket_number')
                            ->label('رقم التذكرة')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('trip_id')
                            ->label('الرحلة')
                            ->relationship('trip', 'id')
                            ->searchable()
                            ->native(false),

                        Forms\Components\Select::make('driver_id')
                            ->label('السائق')
                            ->relationship('driver', 'name', fn($query) => $query->where('role', 'driver'))
                            ->searchable()
                            ->native(false),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('تفاصيل الشكوى')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label('التصنيف')
                            ->options([
                                'delay' => 'تأخير',
                                'behavior' => 'سلوك',
                                'pricing' => 'تسعير',
                                'safety' => 'أمان',
                                'cleanliness' => 'نظافة',
                                'other' => 'أخرى',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('priority')
                            ->label('الأولوية')
                            ->options([
                                'low' => 'منخفضة',
                                'medium' => 'متوسطة',
                                'high' => 'عالية',
                                'urgent' => 'عاجلة',
                            ])
                            ->default('medium')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('subject')
                            ->label('الموضوع')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('المعالجة')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'open' => 'مفتوحة',
                                'in_progress' => 'قيد المعالجة',
                                'resolved' => 'محلولة',
                                'closed' => 'مغلقة',
                            ])
                            ->default('open')
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'in_progress' && !request()->input('first_response_at')) {
                                    $set('first_response_at', now());
                                }
                            }),

                        Forms\Components\Select::make('assigned_to')
                            ->label('مسند إلى')
                            ->relationship('assignedTo', 'name', fn($query) => $query->where('role', 'admin'))
                            ->searchable()
                            ->native(false),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('resolution')
                            ->label('الحل')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn($get) => in_array($get('status'), ['resolved', 'closed'])),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('رقم التذكرة')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('التصنيف')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'delay' => 'تأخير',
                        'behavior' => 'سلوك',
                        'pricing' => 'تسعير',
                        'safety' => 'أمان',
                        'cleanliness' => 'نظافة',
                        'other' => 'أخرى',
                        default => $state,
                    })
                    ->colors([
                        'warning' => ['delay', 'cleanliness'],
                        'danger' => ['behavior', 'safety'],
                        'info' => 'pricing',
                        'gray' => 'other',
                    ]),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('الأولوية')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'low' => 'منخفضة',
                        'medium' => 'متوسطة',
                        'high' => 'عالية',
                        'urgent' => 'عاجلة',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ]),

                Tables\Columns\TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'open' => 'مفتوحة',
                        'in_progress' => 'قيد المعالجة',
                        'resolved' => 'محلولة',
                        'closed' => 'مغلقة',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ]),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('مسند إلى')
                    ->default('غير مسند')
                    ->sortable(),

                Tables\Columns\TextColumn::make('response_time_minutes')
                    ->label('وقت الاستجابة')
                    ->formatStateUsing(fn($state) => $state ? round($state / 60, 1) . ' ساعة' : 'لم يتم الرد')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'open' => 'مفتوحة',
                        'in_progress' => 'قيد المعالجة',
                        'resolved' => 'محلولة',
                        'closed' => 'مغلقة',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options([
                        'delay' => 'تأخير',
                        'behavior' => 'سلوك',
                        'pricing' => 'تسعير',
                        'safety' => 'أمان',
                        'cleanliness' => 'نظافة',
                        'other' => 'أخرى',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'low' => 'منخفضة',
                        'medium' => 'متوسطة',
                        'high' => 'عالية',
                        'urgent' => 'عاجلة',
                    ])
                    ->native(false),
            ])
            ->actions([
                \Filament\Actions\Action::make('assign')
                    ->label('إسناد')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('إسناد إلى')
                            ->options(fn() => \App\Models\User::where('role', 'admin')->pluck('name', 'id'))
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (Complaint $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => 'in_progress',
                            'first_response_at' => $record->first_response_at ?? now(),
                        ]);

                        Notification::make()
                            ->title('تم إسناد التذكرة بنجاح')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Complaint $record) => $record->status === 'open'),

                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'open')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
