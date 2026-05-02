# 🔍 تقرير الفحص الشامل — نظام أمان (Amaan)
> تاريخ الفحص: 29 أبريل 2026

---

## 1. نظرة عامة

| البند | القيمة |
|---|---|
| **الإطار** | Laravel 12 + Filament v3 |
| **قاعدة البيانات** | MySQL (تم التحويل من SQLite) |
| **العملة** | ريال يمني (YER) |
| **اللغة** | عربي |

---

## 2. قاعدة البيانات (20 جدول / 16 Model)

| الجدول | Model | الحالة |
|---|---|---|
| users | User | ✅ مع role, is_active, phone |
| trips | Trip | ✅ إحداثيات، أسعار، حالات، ActivityLogger |
| transactions | Transaction | ⚠️ ينقص customer_id في fillable |
| ratings | Rating | ✅ تقييم متعدد + تعليقات |
| complaints | Complaint | ✅ تذاكر + أولويات + إسناد |
| coupons / coupon_usages | Coupon / CouponUsage | ✅ / ⚠️ CouponUsage بدون Resource |
| popups / popup_interactions | Popup / PopupInteraction | ✅ / ⚠️ PopupInteraction بدون Resource |
| pricing_settings | PricingSetting | ✅ فتحات عداد + سعر/كم |
| dynamic_pricing_rules | DynamicPricingRule | ✅ مضاعف/نسبة/ثابت |
| subscription_plans | SubscriptionPlan | ✅ |
| user_subscriptions | UserSubscription | ✅ مع تحقق وثائق |
| driver_rewards | DriverReward | ✅ أسبوعي، توزيع نسبي |
| activity_logs | ActivityLog | ✅ تسجيل تلقائي |
| system_settings | SystemSetting | ✅ singleton |
| permissions/roles | (Spatie) | ✅ |
| notifications | (Laravel) | ✅ |
| personal_access_tokens | (Sanctum) | ✅ جاهز لكن غير مستخدم |

---

## 3. لوحة التحكم (Filament Resources)

| Resource | المجموعة | الصفحات | علاقات | الحالة |
|---|---|---|---|---|
| TripResource | إدارة الرحلات | List,Create,View,Edit | — | ✅ مع إجراءات حالة |
| DriverResource | إدارة السائقين | List,Create,View,Edit | رحلات+تقييمات+معاملات+مكافآت | ✅ مع تعليق/تفعيل |
| CustomerResource | إدارة المستخدمين | List,Create,View,Edit | رحلات+شكاوى | ✅ |
| AdminResource | الإدارة العليا | List,Create,Edit | — | ⚠️ لا View، لا علاقات |
| ComplaintResource | دعم العملاء | List,Create,View,Edit | — | ✅ مع إسناد |
| CouponResource | التسويق | List,Create,Edit | — | ⚠️ لا View |
| PricingSettingResource | الإعدادات المالية | List,Edit | — | ✅ |
| DynamicPricingRuleResource | الإعدادات المالية | List,Create,Edit | — | ⚠️ لا View |
| UserResource | — | List,Create,Edit | — | ❌ مكرر! لا يُستخدم |

**مجلدات بدون Resource رئيسي:** Ratings, Transactions, DriverRewards, Popups, SubscriptionPlans, UserSubscriptions, CouponUsages, PopupInteractions, ActivityLogs

---

## 4. Widgets (10)

| Widget | النوع | الحالة |
|---|---|---|
| StatsOverviewWidget | Stats (6 بطاقات) | ✅ |
| TripsChartWidget | Line (24 ساعة) | ✅ |
| DemandChartWidget | Bar (أسبوعي) | ✅ |
| FinancialSummaryWidget | Doughnut (طرق دفع) | ✅ |
| RevenueByCategoryWidget | Chart | ✅ |
| WeeklyRevenueComparisonWidget | Chart | ✅ |
| LatestTripsWidget | Table | ✅ |
| DriverRatingsWidget | Table | ✅ |
| CustomerSegmentationWidget | Chart | ✅ |
| WelcomeBannerWidget | Banner | ✅ |

---

## 5. الصفحات المستقلة

- **ReportsPage** ✅ — مركز التقارير الشامل
- **SystemSettings** ✅ — 5 تبويبات (أساسية، تواصل، سوشيال، جوال، سياسات)

---

## 6. الخدمات (4)

| Service | الحالة |
|---|---|
| TripPriceCalculator | ✅ حساب سعر + توزيع مالي + caching |
| DriverRewardService | ✅ توزيع أسبوعي مع حد 10% |
| AnalyticsService | ✅ (تم إصلاح SQLite→MySQL) |
| ActivityLogger | ✅ تسجيل تلقائي |

---

## 7. المشاكل الحالية (Bugs)

| # | المشكلة | الخطورة | تم إصلاحه؟ |
|---|---|---|---|
| 1 | تشفير مزدوج للباسورد في Seeder | 🔴 | ✅ |
| 2 | ملف .env مخرب | 🔴 | ✅ |
| 3 | SQLite strftime مع MySQL | 🔴 | ✅ |
| 4 | UserResource مكرر | 🟡 | ❌ |
| 5 | مجلدات Resources بدون ملف رئيسي | 🟡 | ❌ |
| 6 | أدوار Spatie غير مربوطة بـ Filament | 🟡 | ❌ |
| 7 | Middleware غير مسجل في Kernel | 🟡 | ❌ |
| 8 | DistributeWeeklyRewards غير مسجل في Scheduler | 🟡 | ❌ |
| 9 | DatabaseSeeder لا يشغل AmaanSystemSeeder | 🟢 | ❌ |
| 10 | لا يوجد API للجوال | 🔴 | ❌ |
| 11 | Transaction ينقص customer_id | 🟡 | ❌ |

---

## 8. ما هو مكتمل ✅

- [x] لوحة تحكم إدارية كاملة (Filament)
- [x] إدارة رحلات + سائقين + عملاء + شكاوى + كوبونات
- [x] إعدادات أسعار + تسعير ديناميكي
- [x] نظام مكافآت أسبوعي
- [x] نظام اشتراكات + تحقق وثائق
- [x] تسجيل أنشطة تلقائي
- [x] تقارير + تصدير (4 أنواع)
- [x] بحث شامل + عدادات تنقل
- [x] إشعارات Filament
- [x] نظام صلاحيات Spatie (5 أدوار)
- [x] 10 Widgets للـ Dashboard

---

## 9. ما هو ناقص ❌

### 🔴 حرج

1. **API للجوال بالكامل** — تسجيل/دخول، طلب رحلة، تتبع، إشعارات، محفظة، تقييم، كوبونات، اشتراكات
2. **ربط الصلاحيات بـ Filament** — كل مدير يرى كل شيء حالياً
3. **Real-time / WebSocket** — لا يوجد تتبع مباشر للرحلات

### 🟡 متوسط

4. تنظيف Resources المكررة + إنشاء Resources ناقصة
5. إصلاح Transaction (إضافة customer_id)
6. تسجيل Middleware في Kernel
7. تسجيل Scheduler في console.php
8. إصلاح DatabaseSeeder
9. إشعارات Push (Firebase)

### 🟢 منخفض

10. اختبارات (Unit + Feature)
11. توثيق API (Swagger)
12. لوحة تحكم السائق
13. لوحة تحكم العميل
14. نظام دفع إلكتروني
15. تحقق سائق (وثائق + هوية)
16. خريطة تفاعلية + وضع داكن + متعدد اللغات

---

## 10. خطة التطوير

### المرحلة 1: إصلاحات وتحضيرات (1-2 يوم) 🔧

| # | المهمة |
|---|---|
| 1.1 | إصلاح DatabaseSeeder — إضافة AmaanSystemSeeder + RolesAndPermissionsSeeder |
| 1.2 | حذف UserResource المكرر |
| 1.3 | إصلاح Transaction — إضافة customer_id |
| 1.4 | تسجيل Middleware في Kernel |
| 1.5 | تسجيل Scheduler في console.php |
| 1.6 | ربط أدوار Spatie بـ Filament Resources (canAccess) |

### المرحلة 2: إكمال Resources الناقصة (2-3 أيام) 📋

| # | المهمة |
|---|---|
| 2.1 | إنشاء TransactionResource (معاملات مالية) |
| 2.2 | إنشاء RatingResource (تقييمات) |
| 2.3 | إنشاء PopupResource (بوب أب تسويقي) |
| 2.4 | إنشاء SubscriptionPlanResource (باقات الاشتراك) |
| 2.5 | إنشاء UserSubscriptionResource (اشتراكات المستخدمين) |
| 2.6 | إنشاء DriverRewardResource (مكافآت السائقين) |
| 2.7 | إنشاء ActivityLogResource (سجل الأنشطة) |
| 2.8 | إنشاء CouponUsageResource + PopupInteractionResource |

### المرحلة 3: API للجوال — الأساس (5-7 أيام) 📱

| # | المهمة |
|---|---|
| 3.1 | إنشاء routes/api.php مع مجموعات مصادقة |
| 3.2 | AuthController — تسجيل دخول/خروج + refresh |
| 3.3 | RegisterController — تسجيل عميل + سائق |
| 3.4 | ProfileController — الملف الشخصي + تحديث |
| 3.5 | TripController — طلب رحلة + إلغاء + تاريخ |
| 3.6 | RatingController — تقييم رحلة |
| 3.7 | CouponController — تطبيق كوبون |
| 3.8 | SubscriptionController — اشتراك + تجديد |
| 3.9 | API Resources (JSON transformation) |

### المرحلة 4: Real-time + إشعارات (3-5 أيام) ⚡

| # | المهمة |
|---|---|
| 4.1 | تثبيت Laravel Reverb أو Pusher |
| 4.2 | تحديث موقع السائق مباشر (WebSocket) |
| 4.3 | إشعارات Push للسائق (رحلة جديدة) |
| 4.4 | إشعارات Push للعميل (حالة رحلة) |
| 4.5 | إشعارات للمدير (شكوى جديدة) |

### المرحلة 5: محفظة ودفع إلكتروني (3-5 أيام) 💳

| # | المهمة |
|---|---|
| 5.1 | إنشاء Wallet Model + Migration |
| 5.2 | WalletTransaction Model |
| 5.3 | WalletController (API) — رصيد + إيداع + سحب |
| 5.4 | تكامل بوابة دفع يمنية |
| 5.5 | سحب أرباح السائق |

### المرحلة 6: تحقق السائق + تحسينات (2-3 أيام) 🚗

| # | المهمة |
|---|---|
| 6.1 | DriverDocument Model + Migration |
| 6.2 | تحقق وثائق السائق (Filament Resource) |
| 6.3 | خريطة تفاعلية (Google Maps / OpenStreetMap) |
| 6.4 | لوحة تحكم السائق (Filament Panel منفصل) |
| 6.5 | اختبارات Unit + Feature |
| 6.6 | توثيق API (Swagger) |

---

## ملخص الإحصائيات

| البند | العدد |
|---|---|
| Models | 16 |
| Migrations | 20 |
| Filament Resources | 9 فعلي + 9 ناقص |
| Widgets | 10 |
| Pages | 2 |
| Services | 4 |
| Exports | 4 |
| Console Commands | 1 |
| Middleware | 2 |
| Bugs مفتوحة | 8 |
| مكتمل | ~70% |
| ناقص | ~30% |
