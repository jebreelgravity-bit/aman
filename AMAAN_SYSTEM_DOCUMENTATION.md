# نظام أمان - دليل النظام الكامل
## Amaan System - Complete Documentation

---

## 📋 نظرة عامة / Overview

نظام **أمان** هو تطبيق Laravel متكامل لإدارة رحلات النقل مع نظام مالي متقدم يشمل حساب الأسعار، توزيع المكافآت، وتتبع النشاطات.

**Amaan** is a comprehensive Laravel application for managing transportation trips with an advanced financial system including price calculation, reward distribution, and activity tracking.

---

## 🗄️ هيكل قاعدة البيانات / Database Structure

### 1. جدول المستخدمين / Users Table
```sql
- id
- name
- email
- password
- role (admin, driver, customer)
- phone
- is_active
- timestamps
```

### 2. جدول الرحلات / Trips Table
```sql
- id
- customer_id (FK → users)
- driver_id (FK → users)
- category (economy, vip, bus)
- pickup_address, pickup_latitude, pickup_longitude
- dropoff_address, dropoff_latitude, dropoff_longitude
- distance_km
- estimated_price, final_price
- status (pending, accepted, started, completed, cancelled)
- accepted_at, started_at, completed_at, cancelled_at
- cancellation_reason
- timestamps
```

### 3. جدول إعدادات الأسعار / Pricing Settings Table
```sql
- id
- category (economy, vip, bus) [UNIQUE]
- base_fare (فتحة العداد)
- price_per_km (سعر الكيلومتر)
- is_active
- timestamps
```

**القيم الافتراضية / Default Values:**
- **توفير / Economy**: 300 + (150 × كم)
- **VIP**: 500 + (220 × كم)
- **باص / Bus**: 400 + (180 × كم)

### 4. جدول المعاملات المالية / Transactions Table
```sql
- id
- trip_id (FK → trips)
- driver_id (FK → users)
- trip_price
- app_commission_rate (20%)
- app_commission
- driver_earnings
- payment_method (cash, card, wallet)
- payment_status (pending, completed, failed)
- paid_at
- timestamps
```

### 5. جدول مكافآت السائقين / Driver Rewards Table
```sql
- id
- driver_id (FK → users)
- week_number, year
- week_start_date, week_end_date
- total_trips
- total_earnings
- calculated_reward
- actual_reward
- is_distributed
- distributed_at
- notes
- timestamps
- UNIQUE(driver_id, week_number, year)
```

### 6. جدول سجلات النشاط / Activity Logs Table
```sql
- id
- user_id (FK → users)
- action
- model_type, model_id
- old_values (JSON)
- new_values (JSON)
- ip_address, user_agent
- description
- timestamps
```

---

## 💰 المحرك المالي / Financial Engine

### 1. حساب أسعار الرحلات / Trip Price Calculator

**الموقع / Location:** `app/Services/TripPriceCalculator.php`

#### الاستخدام / Usage:
```php
use App\Services\TripPriceCalculator;

$calculator = new TripPriceCalculator();

// حساب سعر رحلة
$pricing = $calculator->calculate('economy', 10.5);
// النتيجة:
// [
//     'category' => 'economy',
//     'distance_km' => 10.5,
//     'base_fare' => 300.00,
//     'price_per_km' => 150.00,
//     'distance_cost' => 1575.00,
//     'total_price' => 1875.00,
//     'currency' => 'IQD'
// ]

// حساب التوزيع المالي
$breakdown = $calculator->calculateFinancialBreakdown(1875.00);
// النتيجة:
// [
//     'trip_price' => 1875.00,
//     'app_commission_rate' => 20.00,
//     'app_commission' => 375.00,
//     'driver_earnings' => 1500.00
// ]
```

#### الخوارزمية / Algorithm:
```
السعر الكلي = فتحة العداد + (سعر الكيلومتر × المسافة)
Total Price = Base Fare + (Price per KM × Distance)

عمولة التطبيق = السعر الكلي × 20%
App Commission = Total Price × 20%

ربح السائق = السعر الكلي - عمولة التطبيق
Driver Earnings = Total Price - App Commission
```

---

### 2. نظام توزيع المكافآت / Reward Distribution System

**الموقع / Location:** `app/Services/DriverRewardService.php`

#### القواعد / Rules:
1. **الحد الأقصى للمكافآت**: 10% من أرباح التطبيق الأسبوعية
2. **التوزيع النسبي**: إذا تجاوزت المكافآت المحسوبة الحد الأقصى، يتم التوزيع نسبياً
3. **معايير المكافآت**:
   - 50+ رحلة: 50,000 دينار + 5% من الأرباح
   - 30-49 رحلة: 30,000 دينار + 5% من الأرباح
   - 20-29 رحلة: 20,000 دينار
   - 10-19 رحلة: 10,000 دينار

#### الاستخدام / Usage:
```php
use App\Services\DriverRewardService;

$rewardService = new DriverRewardService();

// توزيع مكافآت الأسبوع الحالي
$distribution = $rewardService->distributeWeeklyRewards();

// توزيع مكافآت أسبوع محدد
$distribution = $rewardService->distributeWeeklyRewards(15, 2024);

// النتيجة تتضمن:
// - إجمالي أرباح التطبيق
// - الحد الأقصى للمكافآت (10%)
// - إجمالي المكافآت المحسوبة
// - نسبة التوزيع
// - تفاصيل مكافأة كل سائق
```

#### مثال على التوزيع / Distribution Example:
```
أرباح التطبيق الأسبوعية = 10,000,000 دينار
الحد الأقصى للمكافآت (10%) = 1,000,000 دينار

المكافآت المحسوبة:
- سائق 1: 80,000 دينار (60 رحلة)
- سائق 2: 70,000 دينار (45 رحلة)
- سائق 3: 50,000 دينار (35 رحلة)
الإجمالي = 200,000 دينار

نسبة التوزيع = 1.0 (لم يتجاوز الحد)
المكافآت الفعلية = المكافآت المحسوبة

---

إذا كانت المكافآت المحسوبة = 1,200,000 دينار
نسبة التوزيع = 1,000,000 / 1,200,000 = 0.8333
المكافآت الفعلية = المكافآت المحسوبة × 0.8333
```

---

## 🔒 نظام الأمان وتتبع النشاطات / Security & Activity Logging

**الموقع / Location:** `app/Services/ActivityLogger.php`

### الأحداث المسجلة / Logged Events:

1. **تحديث الأسعار / Pricing Updates**
   ```php
   ActivityLogger::logPricingUpdate($pricingSetting, $oldValues, $newValues);
   ```

2. **توزيع المكافآت / Reward Distribution**
   ```php
   ActivityLogger::logRewardDistribution($distributionData);
   ```

3. **إنشاء رحلة / Trip Creation**
   ```php
   ActivityLogger::logTripCreated($trip);
   ```

4. **تغيير حالة الرحلة / Trip Status Change**
   ```php
   ActivityLogger::logTripStatusChange($trip, $oldStatus, $newStatus);
   ```

5. **المعاملات المالية / Transactions**
   ```php
   ActivityLogger::logTransactionCreated($transaction);
   ```

### التسجيل التلقائي / Automatic Logging:
النظام يسجل تلقائياً:
- ✅ جميع تحديثات الأسعار من قبل الإدارة
- ✅ جميع عمليات توزيع المكافآت
- ✅ إنشاء وتحديث الرحلات
- ✅ إنشاء المعاملات المالية
- ✅ تغييرات أدوار المستخدمين

---

## 🚀 التثبيت والإعداد / Installation & Setup

### 1. تشغيل الـ Migrations
```bash
php artisan migrate
```

### 2. تشغيل الـ Seeder (بيانات تجريبية)
```bash
php artisan db:seed --class=AmaanSystemSeeder
```

سيتم إنشاء:
- 1 مدير (admin@amaan.com)
- 5 سائقين (driver1-5@amaan.com)
- 10 عملاء (customer1-10@amaan.com)
- كلمة المرور لجميع الحسابات: `admin123456`

### 3. إعدادات قاعدة البيانات
تأكد من تحديث ملف `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amaan
DB_USERNAME=root
DB_PASSWORD=
```

---

## 📊 أمثلة الاستخدام / Usage Examples

### إنشاء رحلة جديدة / Create New Trip
```php
use App\Models\Trip;
use App\Services\TripPriceCalculator;

$calculator = new TripPriceCalculator();

// حساب السعر المتوقع
$pricing = $calculator->calculate('economy', 15.3);

// إنشاء الرحلة
$trip = Trip::create([
    'customer_id' => 1,
    'category' => 'economy',
    'pickup_address' => 'بغداد - الكرادة',
    'pickup_latitude' => 33.3152,
    'pickup_longitude' => 44.3661,
    'dropoff_address' => 'بغداد - المنصور',
    'dropoff_latitude' => 33.3128,
    'dropoff_longitude' => 44.3615,
    'distance_km' => 15.3,
    'estimated_price' => $pricing['total_price'],
    'status' => 'pending',
]);
```

### قبول رحلة من قبل سائق / Driver Accepts Trip
```php
$trip->update([
    'driver_id' => 2,
    'status' => 'accepted',
    'accepted_at' => now(),
]);
```

### إكمال رحلة وإنشاء معاملة مالية / Complete Trip & Create Transaction
```php
use App\Models\Transaction;
use App\Services\TripPriceCalculator;

$calculator = new TripPriceCalculator();

// تحديث حالة الرحلة
$trip->update([
    'status' => 'completed',
    'completed_at' => now(),
    'final_price' => 2595.00,
]);

// حساب التوزيع المالي
$breakdown = $calculator->calculateFinancialBreakdown($trip->final_price);

// إنشاء المعاملة المالية
$transaction = Transaction::create([
    'trip_id' => $trip->id,
    'driver_id' => $trip->driver_id,
    'trip_price' => $breakdown['trip_price'],
    'app_commission_rate' => $breakdown['app_commission_rate'],
    'app_commission' => $breakdown['app_commission'],
    'driver_earnings' => $breakdown['driver_earnings'],
    'payment_method' => 'cash',
    'payment_status' => 'completed',
    'paid_at' => now(),
]);
```

### توزيع المكافآت الأسبوعية / Distribute Weekly Rewards
```php
use App\Services\DriverRewardService;

$rewardService = new DriverRewardService();
$distribution = $rewardService->distributeWeeklyRewards();

// عرض النتائج
echo "إجمالي أرباح التطبيق: {$distribution['total_app_profits']} دينار\n";
echo "الحد الأقصى للمكافآت: {$distribution['max_reward_pool']} دينار\n";
echo "عدد السائقين: {$distribution['drivers_count']}\n";

foreach ($distribution['rewards'] as $reward) {
    echo "سائق #{$reward['driver_id']}: {$reward['actual_reward']} دينار\n";
}
```

### عرض سجلات النشاط / View Activity Logs
```php
use App\Models\ActivityLog;

// آخر 50 نشاط
$recentActivities = ActivityLog::with('user')
    ->orderBy('created_at', 'desc')
    ->limit(50)
    ->get();

// نشاطات تحديث الأسعار فقط
$pricingUpdates = ActivityLog::action('pricing_updated')
    ->with('user')
    ->recent(30)
    ->get();

// نشاطات مستخدم معين
$userActivities = ActivityLog::byUser(1)
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## 🔧 الصيانة / Maintenance

### تحديث الأسعار / Update Pricing
```php
use App\Models\PricingSetting;

$pricing = PricingSetting::where('category', 'economy')->first();
$pricing->update([
    'base_fare' => 350.00,
    'price_per_km' => 160.00,
]);
// سيتم تسجيل التغيير تلقائياً في activity_logs
```

### مسح الـ Cache / Clear Cache
```php
use App\Services\TripPriceCalculator;

TripPriceCalculator::clearCache();
```

---

## 📈 التقارير / Reports

### تقرير أرباح السائق / Driver Earnings Report
```php
use App\Models\User;
use Carbon\Carbon;

$driver = User::find(2);
$startDate = Carbon::now()->startOfWeek();
$endDate = Carbon::now()->endOfWeek();

$earnings = $driver->transactions()
    ->whereBetween('created_at', [$startDate, $endDate])
    ->where('payment_status', 'completed')
    ->sum('driver_earnings');

$trips = $driver->driverTrips()
    ->whereBetween('completed_at', [$startDate, $endDate])
    ->where('status', 'completed')
    ->count();
```

### تقرير أرباح التطبيق / App Revenue Report
```php
use App\Models\Transaction;
use Carbon\Carbon;

$startDate = Carbon::now()->startOfMonth();
$endDate = Carbon::now()->endOfMonth();

$revenue = Transaction::whereBetween('created_at', [$startDate, $endDate])
    ->where('payment_status', 'completed')
    ->sum('app_commission');
```

---

## 🎯 الخطوات التالية / Next Steps

1. ✅ إنشاء Controllers للـ API
2. ✅ إضافة Middleware للتحقق من الأدوار
3. ✅ إنشاء واجهة إدارية (Dashboard)
4. ✅ إضافة نظام الإشعارات
5. ✅ تطوير تطبيق الموبايل

---

## 📞 الدعم / Support

للمزيد من المعلومات أو الدعم، يرجى التواصل مع فريق التطوير.

---

**تم التطوير بواسطة / Developed by:** فريق أمان التقني / Amaan Technical Team  
**الإصدار / Version:** 1.0.0  
**التاريخ / Date:** 2024
