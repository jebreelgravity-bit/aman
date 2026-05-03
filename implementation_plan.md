# 🔍 تقرير الفحص الشامل وخطة التطوير — نظام أمان (Amaan)
> بناءً على الفحص الكامل للمشروع ومتابعة التقدم الأخير

## 🎯 ملخص الفحص الشامل للحالة الحالية

لقد قمت بفحص كامل أجزاء المشروع، بما في ذلك قاعدة البيانات، واجهات Filament الإدارية، المتحكمات (Controllers)، المسارات (Routes)، والنماذج (Models).
**التقدم ممتاز جداً منذ التقييم الأخير!** لقد تم إنجاز معظم المهام التي كانت مفقودة، وإليك التفاصيل:

### ما تم إنجازه بنجاح (المكتملات ✅):
1. **قاعدة البيانات والنماذج (Models):** جميع الجداول مبنية بشكل سليم، بما في ذلك التحديثات الأخيرة للجداول المالية (`wallets` و `wallet_transactions`).
2. **لوحة تحكم Filament:** تم إكمال كافة الموارد (Resources) المطلوبة التي كانت ناقصة في السابق، مثل:
   - `TransactionResource`
   - `RatingResource`
   - `WalletResource` & `WalletTransactionsResource`
   - `SubscriptionPlanResource` & `UserSubscriptionResource`
   - `ActivityLogResource` وغيرها.
3. **الواجهة البرمجية الأساسية (Core API):** تم بناء متحكمات (Controllers) فعالة للمصادقة (Auth)، الملف الشخصي، الرحلات، التقييم، الكوبونات، والاشتراكات في `routes/api.php`.
4. **نظام الصلاحيات (Spatie):** مثبت والموارد الإدارية محمية به.
5. **التقارير والإحصائيات:** `AnalyticsService` متكامل وتعمل لوحات القياس (Widgets) بشكل مثالي.

---

## ⚠️ الأجزاء المتبقية (قيد التطوير أو مفقودة)

بالرغم من الإنجاز الكبير، هناك أجزاء جوهرية في النظام تحتاج إلى إكمال للوصول إلى الإطلاق:

### 1. المحفظة والنظام المالي (Wallet & Financial Flow)
- `WalletController` فارغ حالياً ولا يحتوي على وظائف (إيداع، سحب، تحويل، سجل حركات).
- لا يوجد مسارات (Routes) للمحفظة في `api.php`.
- ربط المحفظة بنظام الرحلات: خصم تكلفة الرحلة من رصيد المحفظة تلقائياً أو تقسيمها بين المحفظة والنقد.
- سحب أرباح السائق (Payout).

### 2. البث المباشر والتتبع (Real-time & WebSockets)
- تم بناء الأحداث (`TripRequested` و `TripStatusUpdated`) وحزمة `laravel/reverb` مثبتة.
- **لكن:** يحتاج نظام Reverb إلى تهيئة كاملة، ويجب بناء نظام لتحديث موقع السائق المباشر بشكل مستمر (Driver Location Tracking API & Broadcasting).
- لا يوجد نظام إشعارات (Push Notifications) حقيقي للهواتف المحمولة (مثل Firebase Cloud Messaging).

### 3. التحقق من السائقين والأمان (Driver Verification)
- حالياً يمكن للسائق التسجيل والعمل. لا يوجد جدول مخصص لرفع صور رخصة القيادة، الهوية، أو صور المركبة، ولوحة Filament للتحقق من المستندات واعتماد السائقين.

### 4. التوثيق والاختبار (Documentation & Testing)
- لا يوجد توثيق للـ API (مثل Swagger/Scramble) ليستخدمه مطورو تطبيقات الهاتف (Flutter/React Native).
- نقص في اختبارات الوحدة (Unit & Feature Tests) للمحرك المالي لضمان عدم وجود ثغرات في الحسابات.

---

> [!IMPORTANT]
> ## User Review Required
> يُرجى مراجعة الخطة أدناه وتأكيد أولويات التطوير. هل نبدأ ببرمجة النظام المالي (المحفظة والدفع)، أم إعداد البث المباشر (WebSockets/Reverb) لتتبع الرحلات؟

> [!WARNING]
> ## Open Questions
> 1. هل سيتم استخدام بوابة دفع إلكترونية معينة لشحن المحفظة (مثل زين كاش، آسيا حوالة)، أم سيكون الشحن يدوياً أو عبر بطاقات تعبئة في المرحلة الأولى؟
> 2. هل نعتمد حزمة `dedoc/scramble` لتوليد توثيق API تلقائي لمطوري الموبايل؟

---

## 🚀 خطة التطوير (Implementation Plan)

تم تقسيم العمل المتبقي إلى 4 مراحل واضحة:

### المرحلة 1: تفعيل النظام المالي والمحفظة (Financial Flow)
تهدف هذه المرحلة إلى إكمال المحرك المالي لتمكين المستخدمين من الدفع.

#### [MODIFY] `app/Http/Controllers/Api/WalletController.php`
- برمجة دوال: `balance` (عرض الرصيد)، `deposit` (إيداع - مبدئياً وهمي لتجربة النظام)، `transactions` (سجل الحركات).
#### [MODIFY] `routes/api.php`
- إضافة مجموعة مسارات `/wallet`.
#### [MODIFY] `app/Http/Controllers/Api/TripController.php`
- تعديل دالة إكمال الرحلة لخصم المبلغ من محفظة العميل وإيداع أرباح السائق في محفظته بناءً على نسبة العمولة.
#### [NEW] `app/Services/PaymentGatewayService.php` (اختياري / حسب الإجابة)
- إعداد هيكل للربط مع بوابات الدفع لاحقاً.

---

### المرحلة 2: التتبع المباشر والتواصل (Real-time & WebSockets)
تفعيل التتبع المباشر للرحلات.

#### [MODIFY] `config/reverb.php` و `.env`
- إعداد قنوات Reverb وتفعيلها.
#### [NEW] `app/Http/Controllers/Api/DriverLocationController.php`
- مسار API يستقبل إحداثيات السائق الحالية كل بضع ثوان.
#### [NEW] `app/Events/DriverLocationUpdated.php`
- حدث يبث موقع السائق المباشر للعميل عبر `PrivateChannel('trip.{id}')`.
#### [MODIFY] `routes/channels.php`
- حماية القنوات (ألا يتنصت العميل إلا على رحلته الخاصة).

---

### المرحلة 3: نظام اعتماد السائقين (Driver Verification)
لضمان الأمان في المنصة.

#### [NEW] `database/migrations/xxxx_create_driver_documents_table.php`
- جدول لتخزين (رخصة القيادة، الهوية، صور المركبة، وتاريخ الانتهاء).
#### [NEW] `app/Models/DriverDocument.php`
#### [NEW] `app/Filament/Resources/DriverDocumentResource.php`
- واجهة للإدارة لمراجعة المستندات (قبول/رفض) وإرسال إشعار للسائق.
#### [MODIFY] `app/Http/Controllers/Api/ProfileController.php`
- إضافة واجهة API لرفع المستندات من تطبيق السائق.

---

### المرحلة 4: الجودة والتوثيق (QA & Docs)
لضمان تسليم نظام احترافي خالٍ من الأخطاء.

#### [NEW] `tests/Feature/WalletTest.php`
- اختبار لعمليات الخصم والإيداع.
#### [NEW] `tests/Feature/TripPriceCalculatorTest.php`
- اختبار للتسعير الديناميكي وعمولة التطبيق.
#### [MODIFY] `composer.json`
- تثبيت حزمة `dedoc/scramble` لتوليد واجهة Swagger تلقائية.
- ضبط توثيق الـ API في المسار `/docs/api`.

---

## 🛠 Verification Plan (خطة التحقق)

### Automated Tests
- سيتم كتابة `Feature Tests` للـ Wallet و Trip Payment Process، وتشغيلها باستخدام `php artisan test`.
- التحقق من عدم القدرة على طلب رحلة برصيد غير كافٍ (إذا كان الدفع حصرياً بالمحفظة).

### Manual Verification
- **المحفظة:** إنشاء عميل وسائق، شحن محفظة العميل بـ 10,000 د.ع، إنشاء رحلة وإنهائها بقيمة 5,000 د.ع، ثم التأكد من أن:
  - رصيد العميل أصبح 5,000 د.ع.
  - رصيد السائق ازداد بـ 4,000 د.ع (بفرض أن عمولة التطبيق 20%).
  - المحفظة الإدارية (أو عمولة التطبيق) ازدادت بـ 1,000 د.ع.
- **WebSockets:** استخدام أداة (مثل Laravel Echo Testing) لفتح اتصال ومحاكاة تغيير موقع السائق والتأكد من وصول البث.
