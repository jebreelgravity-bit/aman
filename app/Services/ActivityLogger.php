<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity
     * 
     * @param string $action Action performed
     * @param mixed $model Model instance (optional)
     * @param array|null $oldValues Old values before change
     * @param array|null $newValues New values after change
     * @param string|null $description Additional description
     * @return ActivityLog
     */
    public static function log(
        string $action,
        $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => $description,
        ]);
    }
    
    /**
     * Log pricing update
     * 
     * @param mixed $pricingSetting
     * @param array $oldValues
     * @param array $newValues
     * @return ActivityLog
     */
    public static function logPricingUpdate($pricingSetting, array $oldValues, array $newValues): ActivityLog
    {
        return self::log(
            'pricing_updated',
            $pricingSetting,
            $oldValues,
            $newValues,
            "تم تحديث أسعار فئة {$pricingSetting->category}"
        );
    }
    
    /**
     * Log reward distribution
     * 
     * @param array $distributionData
     * @return ActivityLog
     */
    public static function logRewardDistribution(array $distributionData): ActivityLog
    {
        return self::log(
            'rewards_distributed',
            null,
            null,
            $distributionData,
            "توزيع مكافآت الأسبوع {$distributionData['week_number']} لعام {$distributionData['year']}"
        );
    }
    
    /**
     * Log trip creation
     * 
     * @param mixed $trip
     * @return ActivityLog
     */
    public static function logTripCreated($trip): ActivityLog
    {
        return self::log(
            'trip_created',
            $trip,
            null,
            $trip->toArray(),
            "رحلة جديدة من {$trip->pickup_address} إلى {$trip->dropoff_address}"
        );
    }
    
    /**
     * Log trip status change
     * 
     * @param mixed $trip
     * @param string $oldStatus
     * @param string $newStatus
     * @return ActivityLog
     */
    public static function logTripStatusChange($trip, string $oldStatus, string $newStatus): ActivityLog
    {
        return self::log(
            'trip_status_changed',
            $trip,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            "تغيير حالة الرحلة #{$trip->id} من {$oldStatus} إلى {$newStatus}"
        );
    }
    
    /**
     * Log transaction creation
     * 
     * @param mixed $transaction
     * @return ActivityLog
     */
    public static function logTransactionCreated($transaction): ActivityLog
    {
        return self::log(
            'transaction_created',
            $transaction,
            null,
            $transaction->toArray(),
            "معاملة مالية جديدة للرحلة #{$transaction->trip_id}"
        );
    }
    
    /**
     * Log user role change
     * 
     * @param mixed $user
     * @param string $oldRole
     * @param string $newRole
     * @return ActivityLog
     */
    public static function logUserRoleChange($user, string $oldRole, string $newRole): ActivityLog
    {
        return self::log(
            'user_role_changed',
            $user,
            ['role' => $oldRole],
            ['role' => $newRole],
            "تغيير دور المستخدم {$user->name} من {$oldRole} إلى {$newRole}"
        );
    }
}
