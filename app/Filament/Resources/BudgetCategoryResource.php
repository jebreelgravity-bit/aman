<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetCategoryResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\BudgetCategory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetCategoryResource extends Resource
{
    use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'super_admin', 'accountant'];
    protected static ?string $model = BudgetCategory::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'فئات الميزانية';
    protected static ?string $modelLabel = 'فئة';
    protected static ?string $pluralModelLabel = 'فئات الميزانية';
    protected static \UnitEnum|string|null $navigationGroup = 'المحاسبة';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('اسم الفئة')
                ->required()
                ->maxLength(100),

            Forms\Components\Select::make('type')
                ->label('النوع')
                ->options(['expense' => 'مصروف', 'income' => 'إيراد'])
                ->default('expense')
                ->required(),

            Forms\Components\TextInput::make('description')
                ->label('الوصف')
                ->maxLength(300)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('icon')
                ->label('الأيقونة (Heroicon)')
                ->default('heroicon-o-folder')
                ->placeholder('heroicon-o-folder'),

            Forms\Components\ColorPicker::make('color')
                ->label('اللون')
                ->default('#6366f1'),

            Forms\Components\Toggle::make('is_active')
                ->label('نشطة')
                ->default(true),

            Forms\Components\TextInput::make('sort_order')
                ->label('ترتيب العرض')
                ->numeric()
                ->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')->label('اللون'),
                Tables\Columns\TextColumn::make('name')->label('الفئة')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn($state) => $state === 'expense' ? 'مصروف' : 'إيراد')
                    ->colors(['danger' => 'expense', 'success' => 'income']),
                Tables\Columns\TextColumn::make('description')->label('الوصف')->limit(50)->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('نشطة')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBudgetCategories::route('/'),
            'create' => Pages\CreateBudgetCategory::route('/create'),
            'edit'   => Pages\EditBudgetCategory::route('/{record}/edit'),
        ];
    }
}
