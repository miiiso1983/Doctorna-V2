# تطبيق طبيبك - Flutter Mobile App

تطبيق موبايل لمنصة طبيبك لحجز المواعيد الطبية، مبني بـ Flutter ويدعم Android و iOS.

## 📱 المميزات

- ✅ تسجيل الدخول والتسجيل للمرضى والأطباء
- ✅ عرض قائمة الأطباء مع البحث والفلترة
- ✅ حجز المواعيد الطبية
- ✅ إدارة الملف الشخصي
- ✅ المنشورات الصحية
- ✅ الإشعارات
- ✅ دعم اللغة العربية (RTL)
- ✅ تصميم Material Design 3
- ✅ نظام مصادقة آمن باستخدام JWT

## 🛠️ التقنيات المستخدمة

- **Flutter** - إطار العمل الأساسي
- **Provider** - إدارة الحالة
- **HTTP & Dio** - التواصل مع API
- **Shared Preferences** - التخزين المحلي
- **Cached Network Image** - تحميل وتخزين الصور
- **Flutter Localizations** - دعم اللغة العربية

## 📁 بنية المشروع

```
lib/
├── config/           # إعدادات التطبيق
│   ├── api_config.dart
│   └── app_colors.dart
├── models/           # نماذج البيانات
│   ├── user.dart
│   ├── doctor.dart
│   ├── appointment.dart
│   └── ...
├── services/         # خدمات API
│   ├── api_service.dart
│   ├── auth_service.dart
│   └── ...
├── providers/        # إدارة الحالة
│   ├── auth_provider.dart
│   └── ...
├── screens/          # شاشات التطبيق
│   ├── auth/
│   ├── home/
│   ├── doctors/
│   ├── appointments/
│   └── ...
├── widgets/          # مكونات قابلة لإعادة الاستخدام
└── utils/            # أدوات مساعدة
```

## 🚀 البدء

### المتطلبات

- Flutter SDK (3.9.2 أو أحدث)
- Dart SDK
- Android Studio / Xcode
- محرر نصوص (VS Code, Android Studio, etc.)

### التثبيت

1. **استنساخ المشروع:**
```bash
git clone https://github.com/miiiso1983/Doctorna-V2.git
cd Doctorna-V2/tabibak_app
```

2. **تثبيت المكتبات:**
```bash
flutter pub get
```

3. **تحديث رابط API:**
افتح `lib/config/api_config.dart` وغيّر `baseUrl` إلى رابط السيرفر الخاص بك:
```dart
static const String baseUrl = 'https://your-domain.com';
```

4. **تشغيل التطبيق:**
```bash
# Android
flutter run

# iOS
flutter run -d ios

# Web (للتطوير فقط)
flutter run -d chrome
```

## 📱 بناء التطبيق

### Android (APK)
```bash
flutter build apk --release
```
الملف سيكون في: `build/app/outputs/flutter-apk/app-release.apk`

### Android (App Bundle)
```bash
flutter build appbundle --release
```
الملف سيكون في: `build/app/outputs/bundle/release/app-release.aab`

### iOS
```bash
flutter build ios --release
```

## 🔧 الإعدادات

### تغيير اسم التطبيق

**Android:**
- افتح `android/app/src/main/AndroidManifest.xml`
- غيّر `android:label`

**iOS:**
- افتح `ios/Runner/Info.plist`
- غيّر `CFBundleName`

### تغيير أيقونة التطبيق

استخدم حزمة `flutter_launcher_icons`:
```bash
flutter pub add dev:flutter_launcher_icons
```

أنشئ ملف `flutter_launcher_icons.yaml` وأضف:
```yaml
flutter_icons:
  android: true
  ios: true
  image_path: "assets/icon/app_icon.png"
```

ثم نفذ:
```bash
flutter pub run flutter_launcher_icons
```

## 🌐 API Endpoints

التطبيق يتواصل مع REST API الموجود في `/api`:

- `POST /api/auth/login` - تسجيل الدخول
- `POST /api/auth/register` - التسجيل
- `GET /api/doctors/list` - قائمة الأطباء
- `POST /api/appointments/create` - حجز موعد
- وغيرها...

راجع `api/README.md` للتفاصيل الكاملة.

## 🧪 الاختبار

```bash
# تشغيل الاختبارات
flutter test

# تحليل الكود
flutter analyze

# فحص الأداء
flutter run --profile
```

## 📝 الحالة الحالية

### ✅ تم إنجازه:
- [x] إنشاء المشروع الأساسي
- [x] نظام المصادقة (Login/Register)
- [x] API Service Layer
- [x] Auth Provider
- [x] شاشة Splash
- [x] شاشة تسجيل الدخول
- [x] دعم اللغة العربية

### 🚧 قيد التطوير:
- [ ] شاشة التسجيل
- [ ] الشاشة الرئيسية
- [ ] قائمة الأطباء
- [ ] تفاصيل الطبيب
- [ ] حجز المواعيد
- [ ] الملف الشخصي
- [ ] المنشورات الصحية
- [ ] الإشعارات

## 🤝 المساهمة

نرحب بالمساهمات! يرجى:
1. Fork المشروع
2. إنشاء branch جديد (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push إلى Branch (`git push origin feature/amazing-feature`)
5. فتح Pull Request

## 📄 الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE).

## 📞 الدعم

للمساعدة أو الإبلاغ عن مشاكل:
- افتح Issue على GitHub
- راسلنا على: info@tabibak.com

## 🙏 شكر خاص

- فريق Flutter
- مجتمع Dart
- جميع المساهمين في المكتبات المستخدمة

---

**صُنع بـ ❤️ في العراق**
