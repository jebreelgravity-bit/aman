# دليل لوحة التحكم الإدارية - نظام أمان
# Amaan Admin Dashboard Guide

---

## 🎯 نظرة عامة / Overview

لوحة تحكم إدارية متكاملة مبنية على **Filament v5** مع واجهة مستخدم عصرية وتجربة مستخدم سلسة تدعم جميع أحجام الشاشات.

---

## 📦 المكونات المُنشأة / Created Components

### 1. **قاعدة البيانات (Database)**

#### Migrations الجديدة:
- ✅ `create_ratings_table` - تقييمات السائقين والخدمة
- ✅ `create_complaints_table` - نظام الشكاوى والتذاكر
- ✅ `create_coupons_table` - إدارة الكوبونات والخصومات
- ✅ `create_popups_table` - الإعلانات المنبثقة
- ✅ `create_subscriptions_table` - اشتراكات الطلاب والموظفين
- ✅ `create_dynamic_pricing_table` - التسعير الديناميكي (أوقات الذروة)
- ✅ `create_permission_tables` - نظام الصلاحيات

#### Models:
- ✅ Rating, Complaint, Coupon, CouponUsage
- ✅ Popup, PopupInteraction
- ✅ SubscriptionPlan, UserSubscription
- ✅ DynamicPricingRule

---

### 2. **الخدمات (Services)**

#### AnalyticsService
خدمة تحليلات متقدمة تحتوي على:

```php
// معدل الاحتفاظ بالعملاء
$analytics->getRetentionRate(30); // آخر 30 يوم

// معدل التحويل
$analytics->getConversionRate();

// إيرادات اليوم
$analytics->getTodayRevenue();

// توزيع الرحلات حسب الساعة
$analytics->getTripsDistributionByHour();

// الإيرادات حسب الفئة
$analytics->getRevenueByCategory();

// العملاء الجدد مقابل العائدين
$analytics->getNewVsReturningCustomers();

// أداء السائق
$analytics->getDriverPerformance($driverId);

// تحليل أوقات الذروة
$analytics->getPeakHoursAnalysis();

// المقارنة الشهرية
$analytics->getMonthlyComparison();
```

---

### 3. **Widgets (لوحة التحكم)**

#### StatsOverviewWidget
بطاقات إحصائية تعرض:
- 📊 الرحلات النشطة
- 💰 إيرادات اليوم
- 💵 أرباح التطبيق (20%)
- 📈 معدل التحويل
- 📊 نمو الإيرادات الشهري
- 👥 السائقون النشطون

#### TripsChartWidget
رسم بياني خطي يعرض توزيع الرحلات خلال 24 ساعة

#### RevenueByCategoryWidget
رسم دائري (Doughnut) لتوزيع الإيرادات حسب نوع الخدمة

#### CustomerSegmentationWidget
رسم بياني عمودي للمقارنة بين العملاء الجدد والعائدين

#### LatestTripsWidget
جدول تفاعلي يعرض آخر 10 رحلات

---

### 4. **Filament Resources**

#### UserResource
إدارة المستخدمين (مدراء، سائقين، عملاء):
- ✅ عرض وتصفية حسب الدور
- ✅ إضافة وتعديل المستخدمين
- ✅ تفعيل/تعطيل الحسابات
- ✅ عرض إحصائيات الرحلات لكل مستخدم

#### TripResource
إدارة الرحلات:
- ✅ عرض تفصيلي لكل رحلة
- ✅ تصفية حسب الحالة والفئة والتاريخ
- ✅ تتبع المسار والمسافة
- ✅ عرض التفاصيل المالية

#### ComplaintResource
نظام الشكاوى والتذاكر:
- ✅ تصنيف الشكاوى (تأخير، سلوك، تسعير، أمان، نظافة)
- ✅ تحديد الأولوية (منخفضة، متوسطة، عالية، عاجلة)
- ✅ إسناد التذاكر للمدراء
- ✅ تتبع وقت الاستجابة
- ✅ Badge يعرض عدد الشكاوى المفتوحة

#### CouponResource
إدارة الكوبونات:
- ✅ كوبونات نسبة مئوية أو مبلغ ثابت
- ✅ تحديد حدود الاستخدام
- ✅ استهداف فئات معينة
- ✅ جدولة الكوبونات (تاريخ بدء/انتهاء)
- ✅ تتبع عدد مرات الاستخدام

#### PricingSettingResource
إدارة الأسعار الأساسية:
- ✅ تعديل فتحة العداد لكل فئة
- ✅ تعديل سعر الكيلومتر
- ✅ معاينة مباشرة للحساب
- ✅ تفعيل/تعطيل الفئات

#### DynamicPricingRuleResource
التسعير الديناميكي (أوقات الذروة):
- ✅ قواعد مرنة حسب الوقت والمنطقة
- ✅ تحديد أيام الأسبوع
- ✅ ثلاثة أنواع من التعديل:
  - مضاعف (مثال: 1.5x)
  - نسبة مئوية (مثال: +20%)
  - زيادة ثابتة (مثال: +500 د.ع)
- ✅ نظام الأولويات

---

## 🔐 نظام الصلاحيات (Roles & Permissions)

### الأدوار المتاحة:

#### 1. Super Admin
- جميع الصلاحيات بدون قيود

#### 2. Admin
- إدارة المستخدمين والرحلات
- عرض التقارير المالية
- إدارة الكوبونات
- معالجة الشكاوى

#### 3. Financial Manager
- إدارة الأسعار
- توزيع المكافآت
- عرض وتصدير التقارير المالية

#### 4. Support Manager
- معالجة الشكاوى
- إسناد التذاكر
- عرض بيانات المستخدمين والرحلات

#### 5. Marketing Manager
- إدارة الكوبونات
- إدارة الإعلانات المنبثقة
- عرض تقارير التسويق

---

## 🚀 التثبيت والإعداد / Installation

### 1. تشغيل Migrations الجديدة

```bash
php artisan migrate
```

### 2. تشغيل Seeder للصلاحيات

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 3. إنشاء مستخدم Super Admin

```bash
php artisan tinker
```

```php
$user = App\Models\User::find(1); // المدير الأول
$user->assignRole('super_admin');
```

### 4. نشر ملفات Filament

```bash
php artisan filament:assets
```

### 5. تثبيت الخطوط العربية (Cairo Font)

أضف إلى `resources/css/filament/admin/theme.css`:

```css
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');

:root {
    --font-family: 'Cairo', sans-serif;
}
```

---

## 📊 استخدام لوحة التحكم / Dashboard Usage

### الوصول للوحة التحكم

```
http://your-domain.com/admin
```

### الصفحة الرئيسية

تعرض:
- 6 بطاقات إحصائية رئيسية
- رسم بياني للرحلات خلال 24 ساعة
- توزيع الإيرادات حسب الفئة
- مقارنة العملاء الجدد والعائدين
- جدول آخر الرحلات

### التنقل

القائمة الجانبية منظمة في مجموعات:
- 📊 لوحة التحكم
- 👥 إدارة المستخدمين
- 🚗 إدارة الرحلات
- 💰 الإعدادات المالية
- 📢 التسويق
- 🎧 دعم العملاء
- 📈 التقارير
- ⚙️ الإعدادات

---

## 🎨 التخصيص (Customization)

### تغيير الألوان

في `AdminPanelProvider.php`:

```php
->colors([
    'primary' => Color::Blue,
    'danger' => Color::Rose,
    'success' => Color::Emerald,
    // ...
])
```

### تغيير الشعار

```php
->brandLogo(asset('images/logo.png'))
->brandLogoHeight('2.5rem')
```

### تفعيل الوضع الليلي

```php
->darkMode(true)
```

---

## 📱 التجاوب (Responsiveness)

جميع المكونات متجاوبة بالكامل:
- ✅ Desktop (1920px+)
- ✅ Laptop (1366px - 1920px)
- ✅ Tablet (768px - 1366px)
- ✅ Mobile (320px - 768px)

---

## 🔔 الإشعارات (Notifications)

### إشعارات قاعدة البيانات

```php
use Filament\Notifications\Notification;

Notification::make()
    ->title('تم حفظ التغييرات')
    ->success()
    ->send();
```

### إشعارات فورية

```php
Notification::make()
    ->title('شكوى جديدة')
    ->body('تم استلام شكوى جديدة من العميل')
    ->warning()
    ->sendToDatabase($user);
```

---

## 📈 التقارير والتحليلات

### استخدام AnalyticsService

```php
use App\Services\AnalyticsService;

$analytics = new AnalyticsService();

// معدل الاحتفاظ
$retention = $analytics->getRetentionRate(30);
echo "معدل الاحتفاظ: {$retention['retention_rate']}%";

// معدل التحويل
$conversion = $analytics->getConversionRate();
echo "معدل التحويل: {$conversion['conversion_rate']}%";

// إيرادات اليوم
$revenue = $analytics->getTodayRevenue();
echo "إيرادات اليوم: {$revenue['total_revenue']} د.ع";
```

---

## 🔧 الصيانة (Maintenance)

### مسح Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### إعادة بناء الصلاحيات

```bash
php artisan permission:cache-reset
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 🎯 الميزات المتقدمة

### 1. التسعير الديناميكي

إنشاء قاعدة لأوقات الذروة:

```
الاسم: أوقات الذروة - المساء
الوقت: 17:00 - 20:00
الأيام: الأحد - الخميس
نوع التعديل: مضاعف
القيمة: 1.5 (زيادة 50%)
```

### 2. الكوبونات الذكية

إنشاء كوبون للعملاء الجدد:

```
الكود: WELCOME2024
النوع: نسبة مئوية
القيمة: 20%
الحد الأقصى: 5000 د.ع
نوع المستخدم: عملاء جدد
```

### 3. نظام الشكاوى

تتبع وقت الاستجابة تلقائياً:
- يتم حساب الوقت من إنشاء الشكوى
- يظهر في الجدول بالساعات
- تنبيهات للشكاوى المتأخرة

---

## 🔒 الأمان (Security)

### Activity Logging

جميع التغييرات مسجلة في `activity_logs`:
- تحديث الأسعار
- توزيع المكافآت
- تغيير الأدوار
- معالجة الشكاوى

### إدارة الصلاحيات

```php
// التحقق من الصلاحية
if (auth()->user()->can('edit_pricing_settings')) {
    // السماح بالتعديل
}

// التحقق من الدور
if (auth()->user()->hasRole('super_admin')) {
    // السماح بالوصول
}
```

---

## 📞 الدعم والمساعدة

للمزيد من المعلومات:
- [Filament Documentation](https://filamentphp.com/docs)
- [Laravel Documentation](https://laravel.com/docs)

---

## ✅ قائمة التحقق النهائية

- [ ] تشغيل جميع Migrations
- [ ] تشغيل Seeder للصلاحيات
- [ ] إنشاء مستخدم Super Admin
- [ ] تثبيت الخطوط العربية
- [ ] اختبار جميع الـ Resources
- [ ] اختبار الـ Widgets
- [ ] اختبار نظام الصلاحيات
- [ ] اختبار التجاوب على جميع الأجهزة

---

**تم التطوير بواسطة:** فريق أمان التقني  
**الإصدار:** 1.0.0  
**التاريخ:** 2024
