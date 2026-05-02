<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_description',
        'logo',
        'favicon',
        'contact_email',
        'contact_phone',
        'whatsapp_number',
        'social_facebook',
        'social_twitter',
        'social_instagram',
        'social_linkedin',
        'app_version_ios',
        'app_version_android',
        'force_update_app',
        'maintenance_mode',
        'primary_color',
        'terms_of_service',
        'privacy_policy',
    ];

    protected $casts = [
        'force_update_app' => 'boolean',
        'maintenance_mode' => 'boolean',
    ];

    /**
     * الحصول على الإعدادات (singleton pattern)
     */
    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'site_name' => 'Amaan - أمان',
            'primary_color' => '#0ea5e9',
        ]);
    }

    /**
     * هل وضع الصيانة مفعّل؟
     */
    public function isMaintenanceMode(): bool
    {
        return $this->maintenance_mode;
    }

    /**
     * هل التحديث الإجباري مفعّل؟
     */
    public function isForceUpdateEnabled(): bool
    {
        return $this->force_update_app;
    }
}
