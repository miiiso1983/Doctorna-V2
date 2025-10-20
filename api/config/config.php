<?php
/**
 * API Configuration
 */

// JWT Configuration
define('JWT_SECRET_KEY', $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production-2024');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400 * 7); // 7 days
define('JWT_REFRESH_EXPIRATION', 86400 * 30); // 30 days

// API Configuration
define('API_VERSION', '1.0.0');
define('API_RATE_LIMIT', 100); // requests per minute
define('API_PAGINATION_LIMIT', 20);
define('API_MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Allowed file types for uploads
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Upload directories
define('UPLOAD_DIR', ROOT_PATH . '/public/uploads');
define('AVATAR_DIR', UPLOAD_DIR . '/avatars');
define('HEALTH_POST_DIR', UPLOAD_DIR . '/health-posts');
define('DOCUMENT_DIR', UPLOAD_DIR . '/documents');

// Ensure upload directories exist
if (!is_dir(AVATAR_DIR)) {
    mkdir(AVATAR_DIR, 0755, true);
}
if (!is_dir(HEALTH_POST_DIR)) {
    mkdir(HEALTH_POST_DIR, 0755, true);
}
if (!is_dir(DOCUMENT_DIR)) {
    mkdir(DOCUMENT_DIR, 0755, true);
}

// Error messages in Arabic
define('ERROR_MESSAGES', [
    'unauthorized' => 'غير مصرح لك بالوصول',
    'invalid_credentials' => 'بيانات الدخول غير صحيحة',
    'user_not_found' => 'المستخدم غير موجود',
    'email_exists' => 'البريد الإلكتروني مستخدم بالفعل',
    'invalid_token' => 'رمز المصادقة غير صالح',
    'token_expired' => 'انتهت صلاحية رمز المصادقة',
    'missing_fields' => 'يرجى ملء جميع الحقول المطلوبة',
    'invalid_email' => 'البريد الإلكتروني غير صالح',
    'weak_password' => 'كلمة المرور ضعيفة جداً',
    'not_found' => 'العنصر غير موجود',
    'forbidden' => 'ليس لديك صلاحية للقيام بهذا الإجراء',
    'server_error' => 'حدث خطأ في الخادم',
    'invalid_file_type' => 'نوع الملف غير مدعوم',
    'file_too_large' => 'حجم الملف كبير جداً',
    'upload_failed' => 'فشل رفع الملف',
]);

// Success messages in Arabic
define('SUCCESS_MESSAGES', [
    'login_success' => 'تم تسجيل الدخول بنجاح',
    'register_success' => 'تم التسجيل بنجاح',
    'logout_success' => 'تم تسجيل الخروج بنجاح',
    'profile_updated' => 'تم تحديث الملف الشخصي بنجاح',
    'password_changed' => 'تم تغيير كلمة المرور بنجاح',
    'appointment_created' => 'تم حجز الموعد بنجاح',
    'appointment_cancelled' => 'تم إلغاء الموعد بنجاح',
    'appointment_confirmed' => 'تم تأكيد الموعد بنجاح',
    'post_created' => 'تم إنشاء المنشور بنجاح',
    'post_updated' => 'تم تحديث المنشور بنجاح',
    'post_deleted' => 'تم حذف المنشور بنجاح',
    'notification_read' => 'تم تحديد الإشعار كمقروء',
]);

