<?php

namespace App\Filament\Traits;

/**
 * Trait للتحقق من صلاحيات الأدوار في Filament Resources.
 * يتحقق من Spatie Roles أولاً، ثم من عمود role في جدول users كـ fallback.
 */
trait HasRoleAccess
{
    /**
     * التحقق من أن المستخدم لديه أحد الأدوار المسموح بها.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // super_admin يرى كل شيء دائماً (Spatie أو عمود role)
        if ($user->role === 'super_admin') {
            return true;
        }

        // التحقق من Spatie roles إذا كانت محملة
        try {
            if ($user->getRoleNames()->contains('super_admin')) {
                return true;
            }
        } catch (\Exception $e) {
            // Spatie ليست متاحة أو المستخدم لا يملك أدواراً Spatie
        }

        $allowedRoles = static::$allowedRoles ?? [];

        if (empty($allowedRoles)) {
            return false;
        }

        // التحقق من عمود role في جدول users (الطريقة الرئيسية)
        if (in_array($user->role, $allowedRoles)) {
            return true;
        }

        // التحقق من Spatie roles كـ fallback
        try {
            return $user->hasAnyRole($allowedRoles);
        } catch (\Exception $e) {
            return false;
        }
    }
}
