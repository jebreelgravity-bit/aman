<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\Expense;
use App\Models\BudgetCategory;
use App\Exports\ExpensesExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseResource extends Resource
{
    use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'super_admin', 'accountant'];
    protected static ?string $model = Expense::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'المصروفات';
    protected static ?string $modelLabel = 'مصروف';
    protected static ?string $pluralModelLabel = 'المصروفات';
    protected static \UnitEnum|string|null $navigationGroup = 'المحاسبة';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            \Filament\Schemas\Components\Section::make('تفاصيل المصروف')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('الفئة')
                        ->options(BudgetCategory::where('type', 'expense')->where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\TextInput::make('amount')
                        ->label('المبلغ')
                        ->numeric()
                        ->required()
                        ->prefix('ر.س')
                        ->minValue(0.01),

                    Forms\Components\DatePicker::make('expense_date')
                        ->label('تاريخ الصرف')
                        ->required()
                        ->default(now()),

                    Forms\Components\TextInput::make('description')
                        ->label('وصف المصروف')
                        ->required()
                        ->maxLength(500)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('vendor_name')
                        ->label('اسم المورّد / الجهة')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('invoice_number')
                        ->label('رقم الفاتورة')
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('invoice_date')
                        ->label('تاريخ الفاتورة'),

                    Forms\Components\Select::make('payment_method')
                        ->label('طريقة الدفع')
                        ->options([
                            'cash'          => 'نقداً',
                            'bank_transfer' => 'تحويل بنكي',
                            'card'          => 'بطاقة',
                            'check'         => 'شيك',
                        ])
                        ->default('cash')
                        ->required(),
                ])
                ->columns(2),

            \Filament\Schemas\Components\Section::make('المستندات والملاحظات')
                ->schema([
                    Forms\Components\FileUpload::make('receipt_path')
                        ->label('صورة الإيصال / الفاتورة')
                        ->image()
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->directory('expenses/receipts')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات إضافية')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->badge()
                    ->color(fn($record) => match($record->status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid'     => 'info',
                        default    => 'warning',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('vendor_name')
                    ->label('المورّد')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expense_date')
                    ->label('تاريخ الصرف')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn($state) => match($state) {
                        'cash'          => 'نقداً',
                        'bank_transfer' => 'تحويل بنكي',
                        'card'          => 'بطاقة',
                        'check'         => 'شيك',
                        default         => $state,
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending'  => 'معلّق',
                        'approved' => 'موافق',
                        'rejected' => 'مرفوض',
                        'paid'     => 'مدفوع',
                        default    => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid'     => 'info',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('paidBy.name')
                    ->label('سجّله')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'  => 'معلّق',
                        'approved' => 'موافق',
                        'rejected' => 'مرفوض',
                        'paid'     => 'مدفوع',
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash'          => 'نقداً',
                        'bank_transfer' => 'تحويل بنكي',
                        'card'          => 'بطاقة',
                        'check'         => 'شيك',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->isPending()),

                // زر الموافقة — للمدراء فقط
                Tables\Actions\Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('الموافقة على المصروف')
                    ->modalDescription(fn($record) => "هل تريد الموافقة على مصروف بمبلغ {$record->amount} ر.س ؟")
                    ->action(function (Expense $record) {
                        $record->approve(auth()->id());
                        Notification::make()->title('تمت الموافقة')->success()->send();
                    })
                    ->visible(fn(Expense $record) => $record->isPending() && auth()->user()?->hasAnyRole(['admin', 'super_admin'])),

                // زر الرفض
                Tables\Actions\Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Expense $record, array $data) {
                        $record->reject(auth()->id(), $data['rejection_reason']);
                        Notification::make()->title('تم رفض المصروف')->danger()->send();
                    })
                    ->visible(fn(Expense $record) => $record->isPending() && auth()->user()?->hasAnyRole(['admin', 'super_admin'])),

                // تحديد كمدفوع
                Tables\Actions\Action::make('mark_paid')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Expense $record) {
                        $record->update(['status' => 'paid']);
                        Notification::make()->title('تم تسجيل المصروف كمدفوع')->success()->send();
                    })
                    ->visible(fn(Expense $record) => $record->isApproved()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Expense::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view'   => Pages\ViewExpense::route('/{record}'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
