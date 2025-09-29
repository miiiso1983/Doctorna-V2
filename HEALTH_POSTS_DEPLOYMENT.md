# نظام المنشورات الصحية - دليل النشر

## نظرة عامة
تم إضافة نظام كامل للمنشورات الصحية يسمح للأطباء بإنشاء مقالات ونصائح صحية، ويتطلب موافقة الأدمن قبل النشر، ويمكن للمرضى مشاهدة المنشورات المعتمدة فقط.

## المميزات الجديدة

### للأطباء:
- إنشاء منشورات صحية جديدة مع إمكانية رفع صورة
- إدارة المنشورات الخاصة (تعديل، حذف)
- عرض جميع المنشورات المعتمدة من جميع الأطباء
- تصنيف المنشورات حسب التخصص
- متابعة حالة المنشورات (في الانتظار، معتمد، مرفوض)

### للأدمن:
- مراجعة جميع المنشورات الصحية
- الموافقة على المنشورات أو رفضها
- حذف المنشورات
- إحصائيات شاملة عن المنشورات
- تصفية المنشورات حسب الحالة

### للمرضى:
- عرض المنشورات الصحية المعتمدة فقط
- تصفية المنشورات حسب التخصص
- قراءة المحتوى الكامل في نافذة منبثقة
- معلومات عن الطبيب الناشر

## خطوات النشر على Cloudways

### 1. تحديث الكود من GitHub
```bash
# الدخول إلى مجلد التطبيق
cd /home/master/applications/[APP_NAME]/public_html

# سحب آخر التحديثات
git pull origin main
```

### 2. إنشاء جدول قاعدة البيانات
قم بتنفيذ SQL التالي في phpMyAdmin أو MySQL CLI:

```sql
-- إنشاء جدول المنشورات الصحية
CREATE TABLE IF NOT EXISTS `health_posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `doctor_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_doctor_id` (`doctor_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. إنشاء مجلد رفع الصور
```bash
# إنشاء المجلد مع الصلاحيات المناسبة
mkdir -p public/uploads/health-posts
chmod 755 public/uploads/health-posts
chown www-data:www-data public/uploads/health-posts
```

### 4. إعادة تشغيل الخدمات
في لوحة تحكم Cloudways:
1. اذهب إلى Application Management
2. اضغط على "Deployment via Git"
3. اضغط على "Deploy Latest Commit"
4. بعد اكتمال النشر، اذهب إلى "Application Settings"
5. اضغط على "Restart PHP-FPM"
6. اضغط على "Purge Varnish Cache" (إن وجد)

### 5. التحقق من التثبيت
1. سجل الدخول كطبيب وتحقق من ظهور رابط "منشوراتي الصحية" في القائمة الجانبية
2. سجل الدخول كأدمن وتحقق من ظهور رابط "المنشورات الصحية" في القائمة الجانبية
3. سجل الدخول كمريض وتحقق من ظهور رابط "معلومات صحية" في القائمة الجانبية

## الملفات الجديدة

### Models
- `app/models/HealthPost.php` - نموذج البيانات للمنشورات الصحية

### Controllers
تم تحديث:
- `app/controllers/DoctorController.php` - إضافة دوال إدارة المنشورات
- `app/controllers/AdminController.php` - إضافة دوال مراجعة المنشورات
- `app/controllers/PatientController.php` - إضافة دالة عرض المنشورات

### Views - Doctor
- `app/views/doctor/health-posts.php` - قائمة منشورات الطبيب
- `app/views/doctor/create-health-post.php` - نموذج إنشاء منشور جديد
- `app/views/doctor/edit-health-post.php` - نموذج تعديل منشور
- `app/views/doctor/health-info.php` - عرض المنشورات المعتمدة

### Views - Admin
- `app/views/admin/health-posts.php` - إدارة جميع المنشورات

### Views - Patient
- `app/views/patient/health-info.php` - عرض المنشورات المعتمدة

### Database
- `database/migrations/add_health_posts_table.sql` - سكريبت إنشاء الجدول

### Routes
تم تحديث `index.php` بإضافة المسارات التالية:

**للطبيب:**
- GET `/doctor/health-posts` - قائمة المنشورات
- GET `/doctor/health-posts/create` - نموذج إنشاء منشور
- POST `/doctor/health-posts/create` - حفظ منشور جديد
- GET `/doctor/health-posts/{id}/edit` - نموذج تعديل منشور
- POST `/doctor/health-posts/{id}/edit` - حفظ التعديلات
- POST `/doctor/health-posts/{id}/delete` - حذف منشور
- GET `/doctor/health-info` - عرض المنشورات المعتمدة

**للأدمن:**
- GET `/admin/health-posts` - إدارة المنشورات
- POST `/admin/health-posts/{id}/approve` - الموافقة على منشور
- POST `/admin/health-posts/{id}/reject` - رفض منشور
- POST `/admin/health-posts/{id}/delete` - حذف منشور

**للمريض:**
- GET `/patient/health-info` - عرض المنشورات المعتمدة

## الأمان والتحقق

### رفع الصور
- يتم التحقق من نوع الملف (JPG, PNG, WEBP فقط)
- الحد الأقصى لحجم الصورة: 5MB
- يتم حفظ الصور في `public/uploads/health-posts/`

### الصلاحيات
- الأطباء يمكنهم فقط تعديل/حذف منشوراتهم الخاصة
- الأدمن فقط يمكنه الموافقة/رفض المنشورات
- المرضى يمكنهم فقط مشاهدة المنشورات المعتمدة
- جميع النماذج محمية بـ CSRF tokens

### حالات المنشورات
- `pending` - في انتظار المراجعة (الحالة الافتراضية)
- `approved` - معتمد ومرئي للجميع
- `rejected` - مرفوض ولا يظهر للمرضى

## استكشاف الأخطاء

### المشكلة: لا يظهر رابط المنشورات في القائمة الجانبية
**الحل:** تأكد من تحديث ملفات layouts:
- `app/views/layouts/doctor.php`
- `app/views/layouts/admin.php`
- `app/views/layouts/patient.php`

### المشكلة: خطأ عند رفع الصور
**الحل:** تأكد من:
1. وجود مجلد `public/uploads/health-posts/`
2. صلاحيات المجلد 755
3. المالك www-data أو المستخدم المناسب

### المشكلة: خطأ في قاعدة البيانات
**الحل:** تأكد من:
1. تنفيذ سكريبت SQL لإنشاء الجدول
2. وجود جدول `doctors` (foreign key dependency)
3. استخدام charset utf8mb4

## الدعم الفني
في حالة وجود أي مشاكل، يرجى التحقق من:
- `storage/logs/error.log` - سجل أخطاء التطبيق
- `storage/logs/auth.log` - سجل المصادقة
- سجلات PHP-FPM في Cloudways

## ملاحظات إضافية
- يمكن للطبيب تعديل منشور معتمد، لكن سيتطلب مراجعة جديدة من الأدمن
- المنشورات المرفوضة يمكن تعديلها وإعادة إرسالها
- يتم عرض المنشورات بترتيب الأحدث أولاً
- التصفية حسب التخصص تعتمد على حقل `category` في المنشور

