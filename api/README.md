# طبيبك REST API

REST API للتطبيق الموبايل (Flutter) لمنصة طبيبك.

## 📋 المتطلبات

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx مع mod_rewrite

## 🚀 التثبيت

1. تأكد من أن مجلد `api` موجود في نفس مستوى المشروع الرئيسي
2. تأكد من أن ملف `.htaccess` موجود في مجلد `api`
3. قم بتحديث `JWT_SECRET_KEY` في `api/config/config.php`

## 🔐 المصادقة

يستخدم API نظام JWT (JSON Web Tokens) للمصادقة.

### تسجيل الدخول
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {...},
    "profile": {...},
    "expires_in": 604800
  }
}
```

### استخدام Token
أضف الـ token في header لكل طلب يتطلب مصادقة:
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## 📚 API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/login` | تسجيل الدخول | ❌ |
| POST | `/api/auth/register` | تسجيل حساب جديد | ❌ |
| POST | `/api/auth/logout` | تسجيل الخروج | ✅ |
| POST | `/api/auth/refresh` | تحديث Token | ❌ |
| GET | `/api/auth/me` | الحصول على بيانات المستخدم الحالي | ✅ |

### Users

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/users/profile` | الحصول على الملف الشخصي | ✅ |
| PUT | `/api/users/update` | تحديث الملف الشخصي | ✅ |
| POST | `/api/users/change-password` | تغيير كلمة المرور | ✅ |
| POST | `/api/users/upload-avatar` | رفع صورة الملف الشخصي | ✅ |

### Doctors

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/doctors/list` | قائمة الأطباء | ❌ |
| GET | `/api/doctors/search?q=...` | البحث عن أطباء | ❌ |
| GET | `/api/doctors/{id}` | تفاصيل طبيب | ❌ |
| GET | `/api/doctors/availability/{id}?date=YYYY-MM-DD` | مواعيد الطبيب المتاحة | ❌ |
| GET | `/api/doctors/specializations` | قائمة التخصصات | ❌ |

### Appointments

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/appointments/list` | قائمة المواعيد | ✅ |
| POST | `/api/appointments/create` | حجز موعد جديد | ✅ |
| GET | `/api/appointments/details/{id}` | تفاصيل موعد | ✅ |
| POST | `/api/appointments/cancel/{id}` | إلغاء موعد | ✅ |
| POST | `/api/appointments/confirm/{id}` | تأكيد موعد (طبيب فقط) | ✅ |
| POST | `/api/appointments/complete/{id}` | إكمال موعد (طبيب فقط) | ✅ |

### Health Posts

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/health-posts/list` | قائمة المنشورات الصحية | ❌ |
| GET | `/api/health-posts/{id}` | تفاصيل منشور | ❌ |
| POST | `/api/health-posts/create` | إنشاء منشور (طبيب فقط) | ✅ |
| PUT | `/api/health-posts/update/{id}` | تحديث منشور (طبيب فقط) | ✅ |
| DELETE | `/api/health-posts/delete/{id}` | حذف منشور (طبيب فقط) | ✅ |

### Notifications

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/notifications/list` | قائمة الإشعارات | ✅ |
| GET | `/api/notifications/unread-count` | عدد الإشعارات غير المقروءة | ✅ |
| POST | `/api/notifications/{id}/mark-read` | تحديد إشعار كمقروء | ✅ |
| POST | `/api/notifications/mark-all-read` | تحديد جميع الإشعارات كمقروءة | ✅ |

## 📝 أمثلة الاستخدام

### تسجيل حساب جديد
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "password": "password123",
  "phone": "07701234567",
  "role": "patient"
}
```

### حجز موعد
```http
POST /api/appointments/create
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "doctor_id": 5,
  "appointment_date": "2024-12-25",
  "appointment_time": "10:00:00",
  "type": "consultation",
  "symptoms": "صداع مستمر",
  "notes": "ملاحظات إضافية"
}
```

### البحث عن أطباء
```http
GET /api/doctors/list?specialization_id=3&city=Baghdad&page=1&limit=20
```

### تحديث الملف الشخصي
```http
PUT /api/users/update
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json

{
  "name": "أحمد محمد علي",
  "phone": "07701234567",
  "address": "بغداد - الكرادة",
  "city": "Baghdad"
}
```

## 🔒 الأمان

- جميع كلمات المرور مشفرة باستخدام `password_hash()`
- JWT tokens تنتهي صلاحيتها بعد 7 أيام
- Refresh tokens تنتهي صلاحيتها بعد 30 يوم
- CORS مفعّل للسماح بالوصول من تطبيق Flutter
- التحقق من صحة جميع المدخلات

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "message": "رسالة النجاح",
  "data": {...},
  "timestamp": 1703520000
}
```

### Error Response
```json
{
  "success": false,
  "message": "رسالة الخطأ",
  "errors": {...},
  "timestamp": 1703520000
}
```

### Paginated Response
```json
{
  "success": true,
  "message": null,
  "data": [...],
  "pagination": {
    "total": 100,
    "page": 1,
    "limit": 20,
    "pages": 5
  },
  "timestamp": 1703520000
}
```

## 🧪 الاختبار

يمكنك اختبار API باستخدام:
- Postman
- cURL
- أي HTTP client

### مثال باستخدام cURL:
```bash
# تسجيل الدخول
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# الحصول على قائمة الأطباء
curl -X GET https://your-domain.com/api/doctors/list

# حجز موعد (يتطلب token)
curl -X POST https://your-domain.com/api/appointments/create \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"doctor_id":5,"appointment_date":"2024-12-25","appointment_time":"10:00:00"}'
```

## 🐛 معالجة الأخطاء

| HTTP Code | Description |
|-----------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict |
| 422 | Validation Error |
| 500 | Internal Server Error |

## 📞 الدعم

للمساعدة أو الإبلاغ عن مشاكل، يرجى التواصل مع فريق التطوير.

