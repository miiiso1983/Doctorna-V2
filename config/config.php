<?php
/**
 * Application Configuration
 */

// Load environment variables
if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
}

// Application settings
define('APP_NAME', $_ENV['APP_NAME'] ?? 'طبيبك - نظام حجز المواعيد الطبية');
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($forwardedProto === 'https');
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__default_app_url = $scheme . '://' . $host;
define('APP_URL', rtrim($_ENV['APP_URL'] ?? $__default_app_url, '/'));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'Asia/Baghdad');
define('APP_KEY', $_ENV['APP_KEY'] ?? 'your-secret-key-here');

// Security settings
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));

// File upload settings
define('MAX_FILE_SIZE', (int)($_ENV['MAX_FILE_SIZE'] ?? 5242880)); // 5MB
define('ALLOWED_FILE_TYPES', explode(',', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,pdf,doc,docx'));
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Pagination settings
define('ITEMS_PER_PAGE', (int)($_ENV['ITEMS_PER_PAGE'] ?? 10));
define('DOCTORS_PER_PAGE', (int)($_ENV['DOCTORS_PER_PAGE'] ?? 12));

// Maps configuration
define('GOOGLE_MAPS_API_KEY', $_ENV['GOOGLE_MAPS_API_KEY'] ?? '');
define('MAPS_DEFAULT_LAT', (float)($_ENV['MAPS_DEFAULT_LAT'] ?? 33.3152));
define('MAPS_DEFAULT_LNG', (float)($_ENV['MAPS_DEFAULT_LNG'] ?? 44.3661));

// Email configuration
define('MAIL_DRIVER', $_ENV['MAIL_DRIVER'] ?? 'smtp');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@doctorna.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'طبيبك');

// Cache settings
define('CACHE_DRIVER', $_ENV['CACHE_DRIVER'] ?? 'file');
define('CACHE_LIFETIME', (int)($_ENV['CACHE_LIFETIME'] ?? 3600));

// Logging settings
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'debug');
define('LOG_FILE', $_ENV['LOG_FILE'] ?? 'logs/app.log');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// User roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_PATIENT', 'patient');

// Appointment statuses
define('APPOINTMENT_PENDING', 'pending');
define('APPOINTMENT_CONFIRMED', 'confirmed');
define('APPOINTMENT_CANCELLED', 'cancelled');
define('APPOINTMENT_COMPLETED', 'completed');

// Doctor statuses
define('DOCTOR_PENDING', 'pending');
define('DOCTOR_APPROVED', 'approved');
define('DOCTOR_SUSPENDED', 'suspended');

// Working days
define('WORKING_DAYS', [
    'sunday' => 'الأحد',
    'monday' => 'الإثنين',
    'tuesday' => 'الثلاثاء',
    'wednesday' => 'الأربعاء',
    'thursday' => 'الخميس',
    'friday' => 'الجمعة',
    'saturday' => 'السبت'
]);

// Time slots
define('TIME_SLOTS', [
    '08:00' => '08:00 ص',
    '08:30' => '08:30 ص',
    '09:00' => '09:00 ص',
    '09:30' => '09:30 ص',
    '10:00' => '10:00 ص',
    '10:30' => '10:30 ص',
    '11:00' => '11:00 ص',
    '11:30' => '11:30 ص',
    '12:00' => '12:00 م',
    '12:30' => '12:30 م',
    '13:00' => '01:00 م',
    '13:30' => '01:30 م',
    '14:00' => '02:00 م',
    '14:30' => '02:30 م',
    '15:00' => '03:00 م',
    '15:30' => '03:30 م',
    '16:00' => '04:00 م',
    '16:30' => '04:30 م',
    '17:00' => '05:00 م',
    '17:30' => '05:30 م',
    '18:00' => '06:00 م',
    '18:30' => '06:30 م',
    '19:00' => '07:00 م',
    '19:30' => '07:30 م',
    '20:00' => '08:00 م',
    '20:30' => '08:30 م',
    '21:00' => '09:00 م',
    '21:30' => '09:30 م',
    '22:00' => '10:00 م'
]);

// Error handling
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $error = [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log error
    error_log(json_encode($error), 3, ROOT_PATH . '/' . LOG_FILE);
    
    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<strong>Error:</strong> {$message} in <strong>{$file}</strong> on line <strong>{$line}</strong>";
        echo "</div>";
    }
    
    return true;
});

// Create necessary directories
$directories = [
    ROOT_PATH . '/logs',
    PUBLIC_PATH . '/uploads',
    PUBLIC_PATH . '/uploads/profiles',
    PUBLIC_PATH . '/uploads/documents',
    ROOT_PATH . '/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
