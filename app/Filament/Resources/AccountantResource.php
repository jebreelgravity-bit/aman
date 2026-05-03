<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountantResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountantResource extends Resource
{
    use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'super_admin'];
    protected static ?string $model = User::class;
    protected static ?string $slug = 'accountants';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'المحاسبين';
    protected static ?string $modelLabel = 'محاسب';
    protected static ?string $pluralModelLabel = 'المحاسبين';
    protected static \UnitEnum|string|null $navigationGroup = 'المستخدمين';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'accountant');
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
                        ->directory('avatars')
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
                        ->maxLength(20),

                    Forms\Components\Hidden::make('role')
                        ->default('accountant'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('الحساب نشط')
                        ->default(true),
                ])
                ->columns(2),

            /* ── البيانات الشخصية ── */
            \Filament\Schemas\Components\Section::make('البيانات الشخصية')
                ->description('المعلومات الشخصية للمحاسب')
                ->icon('heroicon-o-identification')
                ->schema([
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->label('تاريخ الميلاد')
                        ->maxDate(now()->subYears(18))
                        ->displayFormat('Y-m-d'),

                    Forms\Components\Select::make('gender')
                        ->label('الجنس')
                        ->options([
                            'male'   => 'ذكر',
                            'female' => 'أنثى',
                        ]),

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

                    Forms\Components\Placeholder::make('id_photos_hint')
                        ->label('')
                        ->content('يمكنك رفع الصور الآن أو لاحقاً — الصور اختيارية')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('national_id_front')
                        ->label('صورة الهوية — الوجه الأمامي (اختياري)')
                        ->image()
                        ->directory('accountants/national-ids')
                        ->maxSize(5120)
                        ->nullable()
                        ->previewable()
                        ->downloadable(),

                    Forms\Components\FileUpload::make('national_id_back')
                        ->label('صورة الهوية — الوجه الخلفي (اختياري)')
                        ->image()
                        ->directory('accountants/national-ids')
                        ->maxSize(5120)
                        ->nullable()
                        ->previewable()
                        ->downloadable(),
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
                    ->label('الصورة')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=6366f1&color=fff'),

                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('المدينة')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('تاريخ الميلاد')
                    ->date('Y-m-d')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('national_id_front')
                    ->label('هوية أمام')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('national_id_back')
                    ->label('هوية خلف')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
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

                Tables\Filters\Filter::make('has_national_id')
                    ->label('لديه هوية وطنية')
                    ->query(fn(Builder $query) => $query->whereNotNull('national_id')),

                Tables\Filters\Filter::make('has_id_photos')
                    ->label('لديه صور الهوية')
                    ->query(fn(Builder $query) => $query
                        ->whereNotNull('national_id_front')
                        ->whereNotNull('national_id_back')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAccountants::route('/'),
            'create' => Pages\CreateAccountant::route('/create'),
            'view'   => Pages\ViewAccountant::route('/{record}'),
            'edit'   => Pages\EditAccountant::route('/{record}/edit'),
        ];
    }
}
