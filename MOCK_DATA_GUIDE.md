# دليل البيانات الوهمية (Mock Data Guide)

## 📋 نظرة عامة

تم إضافة بيانات وهمية (Mock Data) لجميع خدمات التطبيق لتمكين التطوير والاختبار المحلي بدون الحاجة للاتصال بالـ API الحقيقي.

---

## ✅ الخدمات التي تحتوي على Mock Data

### 1️⃣ **AuthService** (`lib/services/auth_service.dart`)
- ✅ تسجيل الدخول (Login)
- ✅ إنشاء مستخدم تجريبي
- ✅ حفظ Tokens وهمية

**البيانات الوهمية:**
- Email: أي email
- Password: أي password
- User: مستخدم تجريبي بـ ID = 1

---

### 2️⃣ **DoctorService** (`lib/services/doctor_service.dart`)
- ✅ قائمة الأطباء (3 أطباء)
- ✅ تفاصيل الطبيب
- ✅ المواعيد المتاحة (10 مواعيد)
- ✅ التخصصات (5 تخصصات)
- ✅ البحث عن الأطباء

**البيانات الوهمية:**
- د. أحمد محمود - طب القلب
- د. فاطمة علي - طب الأطفال
- د. محمد حسن - طب الأسنان

---

### 3️⃣ **AppointmentService** (`lib/services/appointment_service.dart`)
- ✅ قائمة المواعيد (2 مواعيد)
- ✅ تفاصيل الموعد
- ✅ إنشاء موعد جديد
- ✅ إلغاء الموعد
- ✅ تأكيد الموعد
- ✅ إكمال الموعد

**البيانات الوهمية:**
- موعد مع د. أحمد محمود (pending)
- موعد مع د. فاطمة علي (confirmed)

---

### 4️⃣ **HealthPostService** (`lib/services/health_post_service.dart`)
- ✅ قائمة المنشورات الصحية (3 منشورات)

**البيانات الوهمية:**
- نصائح للحفاظ على صحة القلب
- التغذية السليمة للأطفال
- العناية بالأسنان اليومية

---

### 5️⃣ **NotificationService** (`lib/services/notification_service.dart`)
- ✅ قائمة الإشعارات (3 إشعارات)
- ✅ عدد الإشعارات غير المقروءة (2)
- ✅ تحديد كمقروء
- ✅ تحديد الكل كمقروء

**البيانات الوهمية:**
- تأكيد موعد (غير مقروء)
- منشور جديد (غير مقروء)
- تذكير بموعد (مقروء)

---

### 6️⃣ **ReviewService** (`lib/services/review_service.dart`)
- ✅ قائمة التقييمات (3 تقييمات)

**البيانات الوهمية:**
- تقييم 5 نجوم من أحمد علي
- تقييم 4 نجوم من فاطمة حسن
- تقييم 5 نجوم من محمد سعيد

---

## 🔄 كيفية التبديل بين Mock Data والـ API الحقيقي

### الطريقة السريعة (البحث والاستبدال):

1. **للعودة للـ API الحقيقي:**
   - ابحث عن: `// TEMPORARY: Mock data`
   - احذف كود Mock Data
   - أزل التعليق عن كود `/* REAL API CODE */`

2. **مثال في `auth_service.dart`:**

```dart
// الكود الحالي (Mock):
Future<Map<String, dynamic>> login({
  required String email,
  required String password,
}) async {
  // TEMPORARY: Mock login for development
  print('🔐 Mock Login: $email');
  
  await Future.delayed(const Duration(seconds: 1));
  
  final mockUser = User(...);
  
  return {
    'user': mockUser,
    'profile': null,
  };
  
  /* REAL API CODE - Uncomment when API is working
  final response = await _apiService.post(
    ApiConfig.login,
    body: {
      'email': email,
      'password': password,
    },
  );
  ...
  */
}
```

**بعد التعديل (API حقيقي):**

```dart
Future<Map<String, dynamic>> login({
  required String email,
  required String password,
}) async {
  final response = await _apiService.post(
    ApiConfig.login,
    body: {
      'email': email,
      'password': password,
    },
  );
  
  final user = User.fromJson(response['data']['user']);
  
  await _apiService.saveTokens(
    response['data']['token'],
    response['data']['refresh_token'],
  );
  
  return {
    'user': user,
    'profile': response['data']['profile'],
  };
}
```

---

## 📝 ملاحظات مهمة

### ✅ المزايا:
1. **التطوير المحلي:** يمكنك تطوير واختبار التطبيق بدون اتصال بالإنترنت
2. **سرعة التطوير:** لا حاجة لانتظار استجابة السيرفر
3. **اختبار الواجهة:** يمكنك اختبار جميع الشاشات بسهولة
4. **بيانات واقعية:** البيانات الوهمية تشبه البيانات الحقيقية

### ⚠️ القيود:
1. **لا يوجد تخزين دائم:** البيانات تُفقد عند إعادة تشغيل التطبيق
2. **لا يوجد تزامن:** التغييرات لا تُحفظ في قاعدة البيانات
3. **الدردشة والمكالمات:** تحتاج API حقيقي للعمل
4. **رفع الملفات:** لا يعمل مع Mock Data

---

## 🚀 الخطوات التالية

### عند جاهزية الـ API:

1. **اختبار الـ API:**
   ```bash
   curl -X POST "https://phpstack-1510634-5888136.cloudwaysapps.com/api/auth/login" \
     -H "Content-Type: application/json" \
     -d '{"email":"test@test.com","password":"123456"}'
   ```

2. **إزالة Mock Data:**
   - افتح كل ملف خدمة
   - احذف كود Mock Data
   - أزل التعليق عن كود API الحقيقي

3. **اختبار التطبيق:**
   - سجل دخول بحساب حقيقي
   - تأكد من عمل جميع الميزات
   - اختبر الدردشة والمكالمات

---

## 📊 ملخص الملفات المعدلة

```
tabibak_app/lib/services/
├── auth_service.dart          ✅ Mock Login
├── doctor_service.dart        ✅ Mock Doctors
├── appointment_service.dart   ✅ Mock Appointments
├── health_post_service.dart   ✅ Mock Health Posts
├── notification_service.dart  ✅ Mock Notifications
└── review_service.dart        ✅ Mock Reviews
```

---

## 🎯 الحالة الحالية

- ✅ التطبيق يعمل بشكل كامل على Chrome (Web)
- ✅ جميع الشاشات تعرض بيانات واقعية
- ✅ لا توجد رسائل "فشل الاتصال بالخادم"
- ⚠️ iOS يحتاج حل مشكلة CocoaPods
- ⚠️ الدردشة والمكالمات تحتاج API حقيقي

---

**آخر تحديث:** 2024-12-23
**الإصدار:** 1.0.0 (Mock Data)

