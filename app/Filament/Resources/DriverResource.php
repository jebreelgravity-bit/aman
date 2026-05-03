<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Filament\Traits\HasRoleAccess;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DriverResource extends Resource
{
    use HasRoleAccess;

    /** الأدوار المسموح لها بالوصول لهذا Resource */
    protected static array $allowedRoles = ['admin', 'super_admin'];

    protected static ?string $model = User::class;

    protected static ?string $slug = 'drivers';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'السائقين';

    protected static ?string $modelLabel = 'سائق';

    protected static ?string $pluralModelLabel = 'السائقين';

    protected static \UnitEnum|string|null $navigationGroup = 'المستخدمين';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'driver');
    }


    public static function form(Schema $form): Schema
    {
        return $form->schema([

            /* ── البيانات الأساسية ── */
            \Filament\Schemas\Components\Section::make('البيانات الأساسية')
                ->description('معلومات الحساب والتواصل')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\FileUpload::make('avatar_url')
                        ->label('الصورة الشخصية (اختياري)')
                        ->image()
                        ->directory('avatars/drivers')
                        ->imageEditor()
                        ->circleCropper()
                        ->maxSize(2048)
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('name')
                        ->label('الاسم الكامل')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('رقم الهاتف')
                        ->tel()
                        ->required()
                        ->maxLength(20),

                    Forms\Components\Hidden::make('role')->default('driver'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('الحساب نشط')
                        ->default(true),
                ])
                ->columns(2),

            /* ── البيانات الشخصية ── */
            \Filament\Schemas\Components\Section::make('البيانات الشخصية')
                ->description('المعلومات الشخصية للسائق')
                ->icon('heroicon-o-identification')
                ->schema([
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->label('تاريخ الميلاد')
                        ->maxDate(now()->subYears(18))
                        ->displayFormat('Y-m-d'),

                    Forms\Components\Select::make('gender')
                        ->label('الجنس')
                        ->options(['male' => 'ذكر', 'female' => 'أنثى']),

                    Forms\Components\TextInput::make('city')
                        ->label('المدينة')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('address')
                        ->label('العنوان')
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            /* ── الهوية الوطنية ── */
            \Filament\Schemas\Components\Section::make('الهوية الوطنية')
                ->description('رقم وصور الهوية الوطنية')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Forms\Components\TextInput::make('national_id')
                        ->label('رقم الهوية الوطنية')
                        ->maxLength(20)
                        ->unique(table: 'users', column: 'national_id', ignoreRecord: true)
                        ->placeholder('1xxxxxxxxx'),

                    Forms\Components\Placeholder::make('id_hint')
                        ->label('')
                        ->content('صور الهوية اختيارية — يمكن رفعها لاحقاً'),

                    Forms\Components\FileUpload::make('national_id_front')
                        ->label('صورة الهوية — الوجه الأمامي (اختياري)')
                        ->image()
                        ->directory('drivers/national-ids')
                        ->maxSize(5120)
                        ->nullable()
                        ->previewable()
                        ->downloadable(),

                    Forms\Components\FileUpload::make('national_id_back')
                        ->label('صورة الهوية — الوجه الخلفي (اختياري)')
                        ->image()
                        ->directory('drivers/national-ids')
                        ->maxSize(5120)
                        ->nullable()
                        ->previewable()
                        ->downloadable(),
                ])
                ->columns(2),

            /* ── بيانات المركبة ── */
            \Filament\Schemas\Components\Section::make('بيانات المركبة')
                ->description('معلومات السيارة أو الباص')
                ->icon('heroicon-o-truck')
                ->schema([
                    Forms\Components\TextInput::make('vehicle_plate')
                        ->label('رقم اللوحة')
                        ->placeholder('مثال: أ ب ج 1234')
                        ->maxLength(20)
                        ->required(),

                    Forms\Components\Select::make('vehicle_type')
                        ->label('نوع المركبة')
                        ->options([
                            'taxi'    => 'تاكسي',
                            'minibus' => 'ميني باص',
                            'bus'     => 'باص كبير',
                            'suv'     => 'SUV / دفع رباعي',
                            'van'     => 'فان',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('vehicle_grade')
                        ->label('درجة المركبة')
                        ->options([
                            'economy'  => 'اقتصادي — Economy',
                            'standard' => 'قياسي — Standard',
                            'comfort'  => 'مريح — Comfort',
                            'premium'  => 'مميز — Premium',
                            'business' => 'رجال أعمال — Business',
                            'vip'      => 'VIP الدرجة الأولى',
                        ])
                        ->required()
                        ->native(false)
                        ->helperText('درجة الخدمة المقدمة من المركبة'),

                    Forms\Components\TextInput::make('vehicle_model')
                        ->label('موديل المركبة')
                        ->placeholder('مثال: تويوتا كامري 2023')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('vehicle_color')
                        ->label('لون المركبة')
                        ->placeholder('مثال: أبيض')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('vehicle_year')
                        ->label('سنة الصنع')
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(now()->year + 1)
                        ->placeholder((string) now()->year),

                    Forms\Components\FileUpload::make('vehicle_photo')
                        ->label('صورة المركبة (اختياري)')
                        ->image()
                        ->directory('drivers/vehicles')
                        ->maxSize(5120)
                        ->nullable()
                        ->previewable()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            /* ── كلمة المرور ── */
            \Filament\Schemas\Components\Section::make('كلمة المرور')
                ->icon('heroicon-o-lock-closed')
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label('كلمة المرور')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $context): bool => $context === 'create')
                        ->minLength(8),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('تأكيد كلمة المرور')
                        ->password()
                        ->revealable()
                        ->same('password')
                        ->dehydrated(false)
                        ->required(fn(string $context): bool => $context === 'create'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=3b82f6&color=fff'),

                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle_plate')
                    ->label('رقم اللوحة')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('نوع المركبة')
                    ->formatStateUsing(fn($state) => match($state) {
                        'taxi'    => 'تاكسي',
                        'minibus' => 'ميني باص',
                        'bus'     => 'باص كبير',
                        'suv'     => 'SUV',
                        'van'     => 'فان',
                        default   => $state ?? '—',
                    })
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle_grade')
                    ->label('الدرجة')
                    ->formatStateUsing(fn($state) => match($state) {
                        'economy'  => 'اقتصادي',
                        'standard' => 'قياسي',
                        'comfort'  => 'مريح',
                        'premium'  => 'مميز',
                        'business' => 'بيزنس',
                        'vip'      => 'VIP',
                        default    => $state ?? '—',
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'economy'  => 'gray',
                        'standard' => 'info',
                        'comfort'  => 'primary',
                        'premium'  => 'warning',
                        'business' => 'danger',
                        'vip'      => 'success',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('driverTrips_count')
                    ->label('الرحلات')
                    ->counts('driverTrips')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([


                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),

                \Filament\Actions\Action::make('suspend')
                    ->label('تعليق الحساب')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تعليق حساب السائق')
                    ->modalDescription('هل أنت متأكد من تعليق هذا الحساب؟ لن يتمكن السائق من استقبال رحلات جديدة.')
                    ->action(function (User $record) {
                        $record->update(['is_active' => false]);
                        Notification::make()->title('تم تعليق حساب السائق')->icon('heroicon-o-no-symbol')->danger()->send();
                    })
                    ->visible(fn(User $record) => $record->is_active),

                \Filament\Actions\Action::make('activate')
                    ->label('تفعيل الحساب')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()->title('تم تفعيل حساب السائق')->icon('heroicon-o-check-circle')->success()->send();
                    })
                    ->visible(fn(User $record) => !$record->is_active),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('bulk_activate')
                        ->label('تفعيل المحددين')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each(fn($r) => $r->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion(),

                    \Filament\Actions\BulkAction::make('bulk_suspend')
                        ->label('تعليق المحددين')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each(fn($r) => $r->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DriverTripsRelationManager::class,
            RelationManagers\DriverRatingsRelationManager::class,
            RelationManagers\DriverTransactionsRelationManager::class,
            RelationManagers\DriverRewardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'view' => Pages\ViewDriver::route('/{record}'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }

    // ─── بحث شامل ───
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'الهاتف' => $record->phone ?? '—',
            'الحالة' => $record->is_active ? '🟢 نشط' : '🔴 معلّق',
        ];
    }

    // ─── عداد التنقل ───
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('is_active', true)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
