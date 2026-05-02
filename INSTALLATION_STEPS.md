# خطوات التثبيت النهائية - نظام أمان
# Final Installation Steps - Amaan System

---

## 🚀 الخطوات المطلوبة للتشغيل الفوري

### 1️⃣ تشغيل جميع Migrations

```bash
php artisan migrate
```

هذا سيُنشئ جميع الجداول:
- ✅ users (مع التعديلات)
- ✅ pricing_settings
- ✅ trips
- ✅ transactions
- ✅ driver_rewards
- ✅ activity_logs
- ✅ ratings
- ✅ complaints
- ✅ coupons & coupon_usage
- ✅ popups & popup_interactions
- ✅ subscription_plans & user_subscriptions
- ✅ dynamic_pricing_rules
- ✅ permissions & roles (Spatie)

---

### 2️⃣ تشغيل Seeders

```bash
# بيانات تجريبية للمستخدمين
php artisan db:seed --class=AmaanSystemSeeder

# الأدوار والصلاحيات
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

### 3️⃣ إنشاء مستخدم Super Admin

```bash
php artisan tinker
```

```php
$admin = App\Models\User::where('email', 'admin@amaan.com')->first();
$admin->assignRole('super_admin');
exit
```

---

### 4️⃣ نشر ملفات Filament

```bash
php artisan filament:assets
```

---

### 5️⃣ إنشاء ملف Theme للخطوط العربية

قم بإنشاء المجلد والملف:

```bash
mkdir -p resources/css/filament/admin
```

أنشئ ملف `resources/css/filament/admin/theme.css`:

```css
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');

@tailwind base;
@tailwind components;
@tailwind utilities;

:root {
    --font-family: 'Cairo', sans-serif;
}

body {
    font-family: var(--font-family);
}
```

---

### 6️⃣ تحديث Vite Config

تأكد من أن `vite.config.js` يحتوي على:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
        }),
    ],
});
```

---

### 7️⃣ بناء الأصول

```bash
npm install
npm run build
```

---

### 8️⃣ مسح Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### 9️⃣ تشغيل الخادم

```bash
php artisan serve
```

---

### 🔟 الوصول للوحة التحكم

افتح المتصفح وانتقل إلى:

```
http://localhost:8000/admin
```

**بيانات الدخول:**
- البريد: `admin@amaan.com`
- كلمة المرور: `password`

---

## ✅ التحقق من التثبيت

### اختبار 1: الصفحة الرئيسية

يجب أن تشاهد:
- ✅ 6 بطاقات إحصائية
- ✅ 3 رسوم بيانية
- ✅ جدول آخر الرحلات

### اختبار 2: القائمة الجانبية

يجب أن تحتوي على:
- ✅ لوحة التحكم
- ✅ المستخدمون
- ✅ الرحلات
- ✅ إعدادات الأسعار
- ✅ التسعير الديناميكي
- ✅ الكوبونات
- ✅ الشكاوى والتذاكر

### اختبار 3: إنشاء مستخدم جديد

1. انتقل إلى "المستخدمون"
2. اضغط "إنشاء"
3. املأ البيانات
4. احفظ

### اختبار 4: تعديل الأسعار

1. انتقل إلى "إعدادات الأسعار"
2. اختر فئة (توفير، VIP، باص)
3. عدّل فتحة العداد أو سعر الكيلومتر
4. احفظ
5. تحقق من تسجيل التغيير في `activity_logs`

---

## 🎨 تخصيص إضافي (اختياري)

### إضافة شعار مخصص

1. ضع ملف الشعار في `public/images/logo.png`
2. سيظهر تلقائياً في لوحة التحكم

### تغيير الألوان

عدّل في `app/Providers/Filament/AdminPanelProvider.php`:

```php
->colors([
    'primary' => Color::Indigo,
    'success' => Color::Green,
    // ...
])
```

---

## 🔧 استكشاف الأخطاء

### خطأ: Class not found

```bash
composer dump-autoload
```

### خطأ: Permission denied

```bash
chmod -R 775 storage bootstrap/cache
```

### خطأ: Vite manifest not found

```bash
npm run build
```

### خطأ: Database connection

تحقق من ملف `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amaan
DB_USERNAME=root
DB_PASSWORD=
```

---

## 📊 البيانات التجريبية

بعد تشغيل `AmaanSystemSeeder`:

**المستخدمون:**
- 1 مدير: `admin@amaan.com`
- 5 سائقين: `driver1@amaan.com` - `driver5@amaan.com`
- 10 عملاء: `customer1@amaan.com` - `customer10@amaan.com`

**كلمة المرور للجميع:** `password`

**الأسعار الافتراضية:**
- توفير: 300 + (150 × كم)
- VIP: 500 + (220 × كم)
- باص: 400 + (180 × كم)

---

## 🎯 الخطوات التالية

بعد التثبيت الناجح:

1. ✅ إنشاء بيانات حقيقية للاختبار
2. ✅ اختبار جميع الـ Resources
3. ✅ اختبار نظام الصلاحيات
4. ✅ اختبار التجاوب على الموبايل
5. ✅ إعداد Backup تلقائي
6. ✅ تفعيل SSL للإنتاج

---

## 📞 الدعم

في حالة وجود مشاكل:
1. تحقق من ملف `storage/logs/laravel.log`
2. راجع وثائق Filament: https://filamentphp.com/docs
3. تواصل مع فريق التطوير

---

**جاهز للاستخدام! 🎉**
