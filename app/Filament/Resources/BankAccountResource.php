<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'super_admin', 'accountant'];
    protected static ?string $model = BankAccount::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'الحسابات البنكية';
    protected static ?string $modelLabel = 'حساب بنكي';
    protected static ?string $pluralModelLabel = 'الحسابات البنكية';
    protected static \UnitEnum|string|null $navigationGroup = 'المحاسبة';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            \Filament\Schemas\Components\Section::make('معلومات البنك')
                ->schema([
                    Forms\Components\TextInput::make('bank_name')
                        ->label('اسم البنك')
                        ->required()->maxLength(100),

                    Forms\Components\TextInput::make('account_name')
                        ->label('اسم صاحب الحساب')
                        ->required()->maxLength(200),

                    Forms\Components\TextInput::make('account_number')
                        ->label('رقم الحساب')
                        ->required()->maxLength(50),

                    Forms\Components\TextInput::make('iban')
                        ->label('IBAN')
                        ->maxLength(34),

                    Forms\Components\TextInput::make('swift_code')
                        ->label('SWIFT Code')
                        ->maxLength(11),

                    Forms\Components\Select::make('currency')
                        ->label('العملة')
                        ->options([
                            'SAR' => 'ريال سعودي (SAR)',
                            'IQD' => 'دينار عراقي (IQD)',
                            'BHD' => 'دينار بحريني (BHD)',
                            'AED' => 'درهم إماراتي (AED)',
                            'USD' => 'دولار أمريكي (USD)',
                        ])
                        ->default('SAR')
                        ->required(),
                ])
                ->columns(2),

            \Filament\Schemas\Components\Section::make('الرصيد والإعدادات')
                ->schema([
                    Forms\Components\TextInput::make('current_balance')
                        ->label('الرصيد الحالي')
                        ->numeric()
                        ->default(0)
                        ->prefix('ر.س'),

                    Forms\Components\DatePicker::make('last_synced_at')
                        ->label('آخر مزامنة'),

                    Forms\Components\Toggle::make('is_primary')
                        ->label('الحساب الرئيسي')
                        ->default(false),

                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true),

                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')->label('البنك')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('account_name')->label('اسم الحساب')->searchable(),
                Tables\Columns\TextColumn::make('account_number')->label('رقم الحساب')->copyable(),
                Tables\Columns\TextColumn::make('iban')->label('IBAN')->copyable()->toggleable(),
                Tables\Columns\TextColumn::make('currency')->label('العملة')->badge()->color('info'),
                Tables\Columns\TextColumn::make('current_balance')->label('الرصيد الحالي')->money('SAR')->sortable(),
                Tables\Columns\IconColumn::make('is_primary')->label('رئيسي')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
                Tables\Columns\TextColumn::make('last_synced_at')->label('آخر مزامنة')->date('Y-m-d')->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit'   => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
