<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use App\Models\SystemSetting;
use BackedEnum;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات النظام';
    protected static ?string $title = 'إعدادات النظام الشاملة';
    protected static \UnitEnum|string|null $navigationGroup = 'السجلات والنظام';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SystemSetting::firstOrCreate(['id' => 1]);
        $this->form->fill($settings->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('الأساسية')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('site_name')
                                    ->label('اسم المنصة')
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('site_description')
                                    ->label('وصف المنصة')
                                    ->columnSpanFull()
                                    ->rows(3),

                                FileUpload::make('logo')
                                    ->label('الشعار (Logo)')
                                    ->image()
                                    ->directory('settings')
                                    ->columnSpan(1),

                                FileUpload::make('favicon')
                                    ->label('الأيقونة (Favicon)')
                                    ->image()
                                    ->directory('settings')
                                    ->columnSpan(1),

                                ColorPicker::make('primary_color')
                                    ->label('اللون الأساسي للتطبيق')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tabs\Tab::make('التواصل والدعم')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                TextInput::make('contact_email')
                                    ->label('البريد الإلكتروني للدعم')
                                    ->email(),

                                TextInput::make('contact_phone')
                                    ->label('رقم الهاتف الأساسي')
                                    ->tel(),

                                TextInput::make('whatsapp_number')
                                    ->label('رقم الواتساب')
                                    ->tel(),
                            ])->columns(2),

                        Tabs\Tab::make('التواصل الاجتماعي')
                            ->icon('heroicon-o-share')
                            ->schema([
                                TextInput::make('social_facebook')
                                    ->label('رابط فيسبوك')
                                    ->url(),

                                TextInput::make('social_twitter')
                                    ->label('رابط تويتر / X')
                                    ->url(),

                                TextInput::make('social_instagram')
                                    ->label('رابط انستغرام')
                                    ->url(),

                                TextInput::make('social_linkedin')
                                    ->label('رابط لينكد إن')
                                    ->url(),
                            ])->columns(2),

                        Tabs\Tab::make('تطبيقات الجوال')
                            ->icon('heroicon-o-device-phone-mobile')
                            ->schema([
                                TextInput::make('app_version_ios')
                                    ->label('إصدار تطبيق iOS الحالي'),

                                TextInput::make('app_version_android')
                                    ->label('إصدار تطبيق Android الحالي'),

                                Toggle::make('force_update_app')
                                    ->label('إجبار المستخدمين على تحديث التطبيق')
                                    ->inline(false),
                            ])->columns(2),

                        Tabs\Tab::make('حالات النظام والسياسات')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Toggle::make('maintenance_mode')
                                    ->label('تفعيل وضع الصيانة (إيقاف عمل التطبيق مؤقتاً)')
                                    ->columnSpanFull()
                                    ->inline(false)
                                    ->onColor('danger'),

                                RichEditor::make('terms_of_service')
                                    ->label('شروط الاستخدام')
                                    ->columnSpanFull(),

                                RichEditor::make('privacy_policy')
                                    ->label('سياسة الخصوصية')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // منع القيم الفارغة للحقول المطلوبة
        $data['primary_color'] = $data['primary_color'] ?? '#FFD700';
        $data['site_name']     = $data['site_name']     ?? 'نظام أمان';

        $settings = SystemSetting::firstOrCreate(['id' => 1]);
        $settings->update($data);

        Notification::make()
            ->title('نجاح')
            ->body('تم حفظ الإعدادات العامة بنجاح!')
            ->success()
            ->send();
    }
}
