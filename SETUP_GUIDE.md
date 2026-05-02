# دليل الإعداد السريع - نظام أمان
# Quick Setup Guide - Amaan System

---

## 📦 المتطلبات / Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Laravel 11.x

---

## 🚀 خطوات التثبيت / Installation Steps

### 1️⃣ إعداد قاعدة البيانات / Database Setup

قم بإنشاء قاعدة بيانات جديدة:
```sql
CREATE DATABASE amaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2️⃣ تحديث ملف البيئة / Update Environment File

قم بتحديث ملف `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amaan
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3️⃣ تثبيت الاعتماديات / Install Dependencies

```bash
composer install
```

### 4️⃣ توليد مفتاح التطبيق / Generate Application Key

```bash
php artisan key:generate
```

### 5️⃣ تشغيل الـ Migrations / Run Migrations

```bash
php artisan migrate
```

سيتم إنشاء الجداول التالية:
- ✅ users (مع إضافة role, phone, is_active)
- ✅ pricing_settings (مع القيم الافتراضية)
- ✅ trips
- ✅ transactions
- ✅ driver_rewards
- ✅ activity_logs

### 6️⃣ تشغيل الـ Seeder (اختياري) / Run Seeder (Optional)

```bash
php artisan db:seed --class=AmaanSystemSeeder
```

سيتم إنشاء:
- 1 مدير: `admin@amaan.com`
- 5 سائقين: `driver1@amaan.com` - `driver5@amaan.com`
- 10 عملاء: `customer1@amaan.com` - `customer10@amaan.com`
- كلمة المرور لجميع الحسابات: `password`

---

## 🎯 التحقق من التثبيت / Verify Installation

### اختبار حساب الأسعار / Test Price Calculation

```bash
php artisan tinker
```

```php
use App\Services\TripPriceCalculator;

$calculator = new TripPriceCalculator();
$pricing = $calculator->calculate('economy', 10);
print_r($pricing);

// النتيجة المتوقعة:
// [
//     'category' => 'economy',
//     'distance_km' => 10,
//     'base_fare' => 300.00,
//     'price_per_km' => 150.00,
//     'distance_cost' => 1500.00,
//     'total_price' => 1800.00,
//     'currency' => 'IQD'
// ]
```

### اختبار إنشاء رحلة / Test Trip Creation

```php
use App\Models\Trip;
use App\Models\User;

$customer = User::where('role', 'customer')->first();
$driver = User::where('role', 'driver')->first();

$trip = Trip::create([
    'customer_id' => $customer->id,
    'driver_id' => $driver->id,
    'category' => 'economy',
    'pickup_address' => 'بغداد - الكرادة',
    'pickup_latitude' => 33.3152,
    'pickup_longitude' => 44.3661,
    'dropoff_address' => 'بغداد - المنصور',
    'dropoff_latitude' => 33.3128,
    'dropoff_longitude' => 44.3615,
    'distance_km' => 10.5,
    'estimated_price' => 1875.00,
    'status' => 'pending',
]);

echo "تم إنشاء الرحلة #{$trip->id} بنجاح!\n";
```

### اختبار نظام المكافآت / Test Reward System

```php
use App\Services\DriverRewardService;

$rewardService = new DriverRewardService();
$distribution = $rewardService->distributeWeeklyRewards();

print_r($distribution);
```

---

## 📋 الأوامر المتاحة / Available Commands

### توزيع المكافآت الأسبوعية / Distribute Weekly Rewards

```bash
# توزيع مكافآت الأسبوع الحالي
php artisan rewards:distribute

# توزيع مكافآت أسبوع محدد
php artisan rewards:distribute 15 2024
```

---

## 🔧 التكوين / Configuration

### تسجيل الـ Middleware / Register Middleware

أضف إلى `bootstrap/app.php`:

```php
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckActiveUser;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => CheckRole::class,
        'active' => CheckActiveUser::class,
    ]);
})
```

### استخدام الـ Middleware في الـ Routes / Use Middleware in Routes

```php
// في routes/api.php

use App\Http\Controllers\TripController;
use App\Http\Controllers\AdminController;

// مسارات المدير فقط
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::put('/pricing/{id}', [AdminController::class, 'updatePricing']);
    Route::post('/rewards/distribute', [AdminController::class, 'distributeRewards']);
});

// مسارات السائق
Route::middleware(['auth:sanctum', 'role:driver', 'active'])->group(function () {
    Route::get('/driver/trips', [TripController::class, 'driverTrips']);
    Route::post('/trips/{id}/accept', [TripController::class, 'acceptTrip']);
});

// مسارات العميل
Route::middleware(['auth:sanctum', 'role:customer', 'active'])->group(function () {
    Route::post('/trips', [TripController::class, 'createTrip']);
    Route::get('/trips/my-trips', [TripController::class, 'myTrips']);
});
```

---

## 📊 هيكل المشروع / Project Structure

```
app/
├── Console/
│   └── Commands/
│       └── DistributeWeeklyRewards.php
├── Http/
│   └── Middleware/
│       ├── CheckRole.php
│       └── CheckActiveUser.php
├── Models/
│   ├── User.php
│   ├── Trip.php
│   ├── Transaction.php
│   ├── PricingSetting.php
│   ├── DriverReward.php
│   └── ActivityLog.php
└── Services/
    ├── TripPriceCalculator.php
    ├── DriverRewardService.php
    └── ActivityLogger.php

database/
├── migrations/
│   ├── 2024_01_01_000001_add_role_to_users_table.php
│   ├── 2024_01_01_000002_create_pricing_settings_table.php
│   ├── 2024_01_01_000003_create_trips_table.php
│   ├── 2024_01_01_000004_create_transactions_table.php
│   ├── 2024_01_01_000005_create_driver_rewards_table.php
│   └── 2024_01_01_000006_create_activity_logs_table.php
└── seeders/
    └── AmaanSystemSeeder.php
```

---

## 🔍 استكشاف الأخطاء / Troubleshooting

### خطأ في الاتصال بقاعدة البيانات / Database Connection Error

```bash
# تحقق من إعدادات قاعدة البيانات
php artisan config:clear
php artisan cache:clear
```

### خطأ في الـ Migrations / Migration Error

```bash
# إعادة تشغيل الـ Migrations
php artisan migrate:fresh --seed
```

### مسح الـ Cache / Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📚 الوثائق الكاملة / Full Documentation

للحصول على الوثائق الكاملة، راجع ملف:
```
AMAAN_SYSTEM_DOCUMENTATION.md
```

---

## ✅ قائمة التحقق / Checklist

- [ ] تثبيت الاعتماديات (`composer install`)
- [ ] إعداد ملف `.env`
- [ ] توليد مفتاح التطبيق (`php artisan key:generate`)
- [ ] تشغيل الـ Migrations (`php artisan migrate`)
- [ ] تشغيل الـ Seeder (اختياري)
- [ ] اختبار حساب الأسعار
- [ ] اختبار إنشاء رحلة
- [ ] تسجيل الـ Middleware
- [ ] إنشاء الـ Controllers (الخطوة التالية)

---

## 🎉 جاهز للاستخدام! / Ready to Use!

النظام الآن جاهز للاستخدام. يمكنك البدء في:
1. إنشاء الـ Controllers
2. تطوير الـ API Endpoints
3. بناء واجهة المستخدم
4. إضافة نظام الإشعارات

---

**تم التطوير بواسطة / Developed by:** فريق أمان التقني  
**الدعم / Support:** support@amaan.com
