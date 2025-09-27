# Doctorna - نظام حجز المواعيد الطبية

نظام شامل لحجز المواعيد الطبية مبني بـ PHP 8+ و MySQL مع تصميم عصري وجميل باستخدام Bootstrap 5.

## المميزات الرئيسية

### 🏥 لوحات التحكم المتعددة
- **لوحة المدير العام**: إدارة شاملة للمستخدمين والأطباء والتخصصات
- **لوحة الطبيب**: إدارة المواعيد والملف الشخصي والجدول الزمني
- **لوحة المريض**: البحث عن الأطباء وحجز المواعيد وإدارة الملف الطبي

### 🔐 نظام المصادقة والأمان
- تسجيل دخول آمن مع تشفير كلمات المرور
- حماية CSRF للنماذج
- نظام الأدوار والصلاحيات
- إعادة تعيين كلمة المرور عبر البريد الإلكتروني

### 📱 التصميم المتجاوب
- تصميم متجاوب بالكامل مع Bootstrap 5 RTL
- دعم اللغة العربية مع الخطوط المناسبة
- ألوان عصرية وتصميم جذاب
- متوافق مع جميع الأجهزة والمتصفحات

### 🗺️ الخرائط والموقع الجغرافي
- تكامل مع Google Maps API
- البحث عن الأطباء حسب الموقع
- حساب المسافة بين المريض والطبيب

### 📊 التقارير والإحصائيات
- إحصائيات شاملة للمديرين
- تقارير المواعيد والإيرادات
- رسوم بيانية تفاعلية

## متطلبات النظام

- PHP 8.0 أو أحدث
- MySQL 5.7 أو أحدث
- Apache/Nginx مع mod_rewrite
- Composer لإدارة التبعيات
- مساحة تخزين 500MB على الأقل

## التثبيت

### 1. تحميل المشروع

```bash
git clone https://github.com/yourusername/doctorna.git
cd doctorna
```

### 2. تثبيت التبعيات

```bash
composer install
```

### 3. إعداد قاعدة البيانات

1. إنشاء قاعدة بيانات جديدة:
```sql
CREATE DATABASE doctorna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. استيراد الهيكل والبيانات الأولية:
```bash
mysql -u username -p doctorna < database/schema.sql
```

### 4. إعداد ملف البيئة

1. نسخ ملف البيئة النموذجي:
```bash
cp .env.example .env
```

2. تحديث إعدادات قاعدة البيانات في `.env`:
```env
DB_HOST=localhost
DB_NAME=doctorna
DB_USER=your_username
DB_PASS=your_password
```

3. إعداد إعدادات البريد الإلكتروني:
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_EMAIL=your_email@gmail.com
MAIL_FROM_NAME="Doctorna"
```

4. إعداد Google Maps API:
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

### 5. إعداد الصلاحيات

```bash
chmod -R 755 public/uploads
chmod -R 755 logs
chmod -R 755 cache
```

### 6. إعداد خادم الويب

#### Apache
تأكد من تفعيل mod_rewrite وأن ملف `.htaccess` موجود في المجلد الجذر.

#### Nginx
أضف التكوين التالي:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/doctorna/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## الاستخدام

### حسابات افتراضية

بعد التثبيت، يمكنك استخدام الحسابات التالية:

**مدير النظام:**
- البريد الإلكتروني: `admin@doctorna.com`
- كلمة المرور: `admin123`

**طبيب تجريبي:**
- البريد الإلكتروني: `doctor@doctorna.com`
- كلمة المرور: `doctor123`

**مريض تجريبي:**
- البريد الإلكتروني: `patient@doctorna.com`
- كلمة المرور: `patient123`

### الوصول للوحات التحكم

- **لوحة المدير**: `/admin`
- **لوحة الطبيب**: `/doctor`
- **لوحة المريض**: `/patient`

## هيكل المشروع

```
doctorna/
├── app/
│   ├── controllers/     # وحدات التحكم
│   ├── models/         # نماذج البيانات
│   ├── views/          # ملفات العرض
│   └── core/           # الفئات الأساسية
├── config/             # ملفات الإعداد
├── database/           # هيكل قاعدة البيانات
├── public/             # الملفات العامة
│   ├── css/           # ملفات التنسيق
│   ├── js/            # ملفات JavaScript
│   └── uploads/       # الملفات المرفوعة
├── vendor/             # تبعيات Composer
├── logs/               # ملفات السجلات
└── cache/              # ملفات التخزين المؤقت
```

## API Documentation

يوفر النظام واجهة برمجة تطبيقات RESTful للتطوير المستقبلي للتطبيقات المحمولة:

### نقاط النهاية الرئيسية

- `GET /api/doctors` - قائمة الأطباء
- `GET /api/specializations` - التخصصات الطبية
- `POST /api/appointments` - حجز موعد جديد
- `GET /api/appointments/{id}` - تفاصيل الموعد

### المصادقة

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     https://your-domain.com/api/doctors
```

## الأمان

### أفضل الممارسات المطبقة

- تشفير كلمات المرور باستخدام bcrypt
- حماية CSRF لجميع النماذج
- تنظيف وتصفية جميع المدخلات
- استخدام Prepared Statements لقاعدة البيانات
- رؤوس الأمان HTTP
- حماية من رفع الملفات الضارة

### إعدادات الأمان الإضافية

1. تفعيل HTTPS في الإنتاج
2. تحديث كلمات المرور الافتراضية
3. إعداد نسخ احتياطية منتظمة
4. مراقبة ملفات السجلات

## الدعم والمساهمة

### الإبلاغ عن المشاكل

يرجى استخدام [GitHub Issues](https://github.com/yourusername/doctorna/issues) للإبلاغ عن المشاكل.

### المساهمة

1. Fork المشروع
2. إنشاء فرع للميزة الجديدة
3. Commit التغييرات
4. Push للفرع
5. إنشاء Pull Request

## الترخيص

هذا المشروع مرخص تحت رخصة MIT. راجع ملف [LICENSE](LICENSE) للتفاصيل.

## الشكر والتقدير

- [Bootstrap](https://getbootstrap.com/) للتصميم المتجاوب
- [Font Awesome](https://fontawesome.com/) للأيقونات
- [Chart.js](https://www.chartjs.org/) للرسوم البيانية
- [Google Fonts](https://fonts.google.com/) للخطوط العربية

---

تم تطوير هذا النظام بعناية لتوفير تجربة مستخدم ممتازة وأداء عالي. نرحب بملاحظاتكم واقتراحاتكم لتحسين النظام.
