<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\Budget;
use App\Models\BudgetCategory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetResource extends Resource
{
    use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'super_admin', 'accountant'];
    protected static ?string $model = Budget::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'الميزانيات';
    protected static ?string $modelLabel = 'ميزانية';
    protected static ?string $pluralModelLabel = 'الميزانيات';
    protected static \UnitEnum|string|null $navigationGroup = 'المحاسبة';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->label('الفئة')
                ->options(BudgetCategory::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('amount')
                ->label('المبلغ المخصص')
                ->numeric()
                ->required()
                ->prefix('ر.س')
                ->minValue(1),

            Forms\Components\Select::make('period_type')
                ->label('نوع الفترة')
                ->options([
                    'monthly'   => 'شهري',
                    'quarterly' => 'ربع سنوي',
                    'yearly'    => 'سنوي',
                ])
                ->default('monthly')
                ->required()
                ->live(),

            Forms\Components\TextInput::make('period_year')
                ->label('السنة')
                ->numeric()
                ->required()
                ->default(now()->year)
                ->minValue(2020)
                ->maxValue(2100),

            Forms\Components\Select::make('period_month')
                ->label('الشهر')
                ->options([
                    1  => 'يناير', 2  => 'فبراير', 3  => 'مارس',
                    4  => 'أبريل', 5  => 'مايو',   6  => 'يونيو',
                    7  => 'يوليو', 8  => 'أغسطس',  9  => 'سبتمبر',
                    10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
                ])
                ->visible(fn(Forms\Get $get) => $get('period_type') === 'monthly')
                ->default(now()->month),

            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')->label('الفئة')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('الميزانية')->money('SAR')->sortable(),
                Tables\Columns\TextColumn::make('period_type')
                    ->label('الفترة')
                    ->formatStateUsing(fn($state) => match($state) {
                        'monthly' => 'شهري', 'quarterly' => 'ربع سنوي', 'yearly' => 'سنوي', default => $state
                    }),
                Tables\Columns\TextColumn::make('period_year')->label('السنة'),
                Tables\Columns\TextColumn::make('period_month')->label('الشهر')
                    ->formatStateUsing(fn($state) => $state ? ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'][$state] : '—'),
                Tables\Columns\TextColumn::make('creator.name')->label('أنشأه')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->date('Y-m-d')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('period_type')
                    ->label('نوع الفترة')
                    ->options(['monthly' => 'شهري', 'quarterly' => 'ربع سنوي', 'yearly' => 'سنوي']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('period_year', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit'   => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
