<?php
/**
 * طبيبك - Doctor Appointment Booking System
 * Main Entry Point
 *
 * @author طبيبك
 * @version 1.0.0
 */
// Configure session cookie params after constants (needs SESSION_LIFETIME), but start session ASAP



// Start output buffering early to catch any accidental BOM/whitespace output
ob_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Let PHP built-in server serve static files under /public
if (php_sapi_name() === 'cli-server') {
    $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $filePath = __DIR__ . $urlPath;
    if (is_file($filePath)) {
        return false; // serve the requested resource as-is
    }
}

// Define constants
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');
define('CONTROLLERS_PATH', APP_PATH . '/controllers');
define('MODELS_PATH', APP_PATH . '/models');

// Include autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Include configuration
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

// Include core classes
require_once APP_PATH . '/core/Router.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/View.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/CSRF.php';

// Initialize router
$router = new Router();

// Define routes
$router->get('/', 'HomeController@index');

// Debug routes (secured with DEBUG_KEY)
$router->get('/debug/session', 'DebugController@session');

// Public pages
$router->get('/services', 'HomeController@services');
// Enforce canonical host/scheme to avoid losing session cookies across variants
$reqScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$reqHost = $_SERVER['HTTP_HOST'] ?? '';
$appParts = parse_url(APP_URL);
$appScheme = $appParts['scheme'] ?? 'http';
$appHost = $appParts['host'] ?? '';
$appPort = isset($appParts['port']) ? (':' . $appParts['port']) : '';
$reqPort = isset($_SERVER['SERVER_PORT']) && !in_array((string)$_SERVER['SERVER_PORT'], ['80','443']) ? (':' . $_SERVER['SERVER_PORT']) : '';
if ($reqHost && $appHost && (strtolower($reqHost) !== strtolower($appHost) || strtolower($reqScheme) !== strtolower($appScheme))) {
    $target = $appScheme . '://' . $appHost . $appPort . ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $target, true, 302);
    exit;
}

// Register global error handler (logs to storage/logs/error.log and shows friendly page)
require_once APP_PATH . '/core/ErrorHandler.php';
ErrorHandler::init();

// Ensure a writable, app-local session save path
$sessionPath = ROOT_PATH . '/storage/sessions';
if (!is_dir($sessionPath)) { @mkdir($sessionPath, 0775, true); }
@ini_set('session.save_path', $sessionPath);
// Harden and extend session lifetime on hosts with aggressive GC
@ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);
@ini_set('session.gc_probability', '1');
@ini_set('session.gc_divisor', '1000'); // 0.1% chance per request
@ini_set('session.use_strict_mode', '1');
@ini_set('session.use_only_cookies', '1');
@ini_set('session.cookie_httponly', '1');

// Start session now that constants and config are available
// Use a custom session name to avoid conflicts with any pre-existing PHPSESSID cookies
@session_name('DOCTORNASESSID');
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
$httpsOn = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($forwardedProto === 'https');
$cookieDomain = parse_url(APP_URL, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? '');
// Use SameSite=None for cross-site flows (requires Secure)
$cookieParams = [
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'domain' => $cookieDomain,
    'secure' => $httpsOn,
    'httponly' => true,
    'samesite' => $httpsOn ? 'None' : 'Lax',
];
session_set_cookie_params($cookieParams);
session_start();

$router->get('/search-doctors', 'HomeController@searchDoctors');
$router->get('/map-search', 'HomeController@mapSearch');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@sendContact');
$router->get('/privacy', 'HomeController@privacy');
$router->get('/terms', 'HomeController@terms');
$router->get('/faq', 'HomeController@faq');

// Auth
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@authenticate');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@store');
$router->get('/logout', 'AuthController@logout');

// Admin routes
$router->get('/admin', 'AdminController@index');
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/doctors', 'AdminController@doctors');
$router->get('/admin/patients', 'AdminController@patients');
$router->get('/admin/specializations', 'AdminController@specializations');
$router->get('/admin/appointments', 'AdminController@appointments');
$router->get('/admin/analytics', 'AdminController@analytics');
// Health posts management for admin
$router->get('/admin/health-posts', 'AdminController@healthPosts');
$router->post('/admin/health-posts/{id}/approve', 'AdminController@approveHealthPost');
$router->post('/admin/health-posts/{id}/reject', 'AdminController@rejectHealthPost');
$router->post('/admin/health-posts/{id}/delete', 'AdminController@deleteHealthPost');

// Doctor routes
$router->get('/doctor', 'DoctorController@index');
$router->get('/doctor/appointments', 'DoctorController@appointments');
$router->get('/doctor/profile', 'DoctorController@profile');
$router->post('/doctor/profile', 'DoctorController@updateProfile');
$router->get('/doctor/schedule', 'DoctorController@schedule');
$router->post('/doctor/schedule', 'DoctorController@updateSchedule');
$router->get('/doctor/patients', 'DoctorController@patients');
$router->get('/doctor/patients/{id}', 'DoctorController@patientDetails');
$router->get('/doctor/reviews', 'DoctorController@reviews');
$router->get('/doctor/analytics', 'DoctorController@analytics');
$router->get('/doctor/settings', 'DoctorController@settings');
// Health posts for doctors
$router->get('/doctor/health-posts', 'DoctorController@healthPosts');
$router->get('/doctor/health-posts/create', 'DoctorController@createHealthPost');
$router->post('/doctor/health-posts/create', 'DoctorController@storeHealthPost');
$router->get('/doctor/health-posts/{id}/edit', 'DoctorController@editHealthPost');
$router->post('/doctor/health-posts/{id}/edit', 'DoctorController@updateHealthPost');
$router->post('/doctor/health-posts/{id}/delete', 'DoctorController@deleteHealthPost');
$router->get('/doctor/health-info', 'DoctorController@healthInfo');

// Patient routes
$router->get('/patient', 'PatientController@index');
$router->get('/patient/search-doctors', 'PatientController@searchDoctors');
$router->get('/patient/doctor/{id}', 'PatientController@viewDoctor');
// Symptoms entry page for patients
$router->get('/patient/symptoms', 'PatientController@symptoms');
// Map search page for patients
$router->get('/patient/map-search', 'PatientController@mapSearch');

$router->post('/patient/appointments/book', 'PatientController@bookAppointment');

$router->get('/patient/profile', 'PatientController@profile');
$router->post('/patient/profile', 'PatientController@updateProfile');
$router->get('/patient/appointments', 'PatientController@appointments');
$router->get('/patient/medical-history', 'PatientController@medicalHistory');

// Settings
$router->get('/patient/settings', 'PatientController@settings');
$router->post('/patient/settings', 'PatientController@updateSettings');
// Health info for patients
$router->get('/patient/health-info', 'PatientController@healthInfo');

$router->get('/patient/prescriptions', 'PatientController@prescriptions');
$router->get('/patient/prescriptions/{id}/print', 'PatientController@printPrescription');
$router->post('/patient/prescriptions/{id}/refill', 'PatientController@requestRefill');
$router->get('/patient/emergency', 'PatientController@emergency');
$router->post('/patient/emergency', 'PatientController@updateEmergency');

// Map AJAX routes
$router->post('/map/find-nearby-doctors', 'MapController@findNearbyDoctors');
$router->post('/map/reverse-geocode', 'MapController@reverseGeocode');
$router->post('/map/geocode', 'MapController@geocodeAddress');
$router->post('/map/directions', 'MapController@getDirections');


// API routes
$router->post('/api/auth/login', 'ApiController@login');
$router->post('/api/auth/register', 'ApiController@register');
$router->get('/api/profile', 'ApiController@profile');
$router->put('/api/profile', 'ApiController@updateProfile');
$router->get('/api/doctors', 'ApiController@doctors');
$router->get('/api/doctors/{id}', 'ApiController@doctorDetails');
$router->get('/api/doctors/{doctor_id}/slots', 'ApiController@availableSlots');
$router->get('/api/specializations', 'ApiController@specializations');
$router->post('/api/appointments', 'ApiController@bookAppointment');
$router->get('/api/appointments', 'ApiController@appointments');
$router->get('/api/appointments/{id}', 'ApiController@getAppointment');
$router->put('/api/appointments/{id}', 'ApiController@updateAppointment');

// Reviews API
$router->post('/api/reviews', 'ApiController@createReview');
$router->get('/api/doctors/{id}/reviews', 'ApiController@doctorReviews');
$router->get('/api/reviews/{id}', 'ApiController@getReviewById');
$router->put('/api/reviews/{id}', 'ApiController@updateReview');
$router->delete('/api/reviews/{id}', 'ApiController@deleteReview');

// Health Posts API
$router->get('/api/health-posts', 'ApiController@healthPosts');
$router->get('/api/health-posts/{id}', 'ApiController@healthPostDetails');
$router->post('/api/health-posts', 'ApiController@createHealthPost');
$router->put('/api/health-posts/{id}', 'ApiController@updateHealthPost');
$router->delete('/api/health-posts/{id}', 'ApiController@deleteHealthPost');
$router->get('/api/doctors/{id}/health-posts', 'ApiController@doctorHealthPosts');

// Payments API (Qi Card)
$router->post('/api/payments/initiate', 'ApiController@initiatePayment');
$router->post('/api/payments/webhook', 'ApiController@paymentWebhook');
$router->get('/api/payments/{id}', 'ApiController@getPayment');


// Chat API
$router->get('/api/chats/threads', 'ApiController@chatThreads');
$router->get('/api/chats/{appointment_id}', 'ApiController@chatMessages');
$router->post('/api/chats/send', 'ApiController@sendChatMessage');
$router->post('/api/chats/{appointment_id}/read', 'ApiController@readChat');

// Video Call API
$router->post('/api/video/rooms', 'ApiController@createVideoRoom');
$router->get('/api/video/rooms/{appointment_id}', 'ApiController@getVideoRoom');

// Chat AJAX
$router->get('/ajax/chats/{appointment_id}', 'AjaxController@chatMessages');
$router->post('/ajax/chats/send', 'AjaxController@sendChatMessage');
$router->post('/ajax/chats/{appointment_id}/read', 'AjaxController@readChat');

// Video AJAX
$router->post('/ajax/video/rooms', 'AjaxController@createVideoRoom');
$router->get('/ajax/video/rooms/{appointment_id}', 'AjaxController@getVideoRoom');
$router->post('/ajax/video/rooms/{appointment_id}/status', 'AjaxController@updateVideoRoomStatus');

// Video room page (WebRTC UI)
$router->get('/video/room/{appointment_id}', 'VideoController@room');


$router->post('/api/video/rooms/{appointment_id}/status', 'ApiController@updateVideoRoomStatus');




// AJAX routes
$router->post('/ajax/appointment/accept', 'AjaxController@acceptAppointment');
$router->post('/ajax/appointment/reject', 'AjaxController@rejectAppointment');
$router->post('/ajax/doctor/availability', 'AjaxController@checkAvailability');
$router->get('/ajax/symptoms/recommend', 'AjaxController@recommendSpecialization');

// Notifications (AJAX)
$router->get('/notifications/check', 'AjaxController@notificationsCheck');
$router->post('/notifications/{id}/read', 'AjaxController@readNotification');


// Dispatch the request
$router->dispatch();

// Flush output buffer after dispatch and ensure Standards Mode
$output = ob_get_clean();
$bom = "\xEF\xBB\xBF";
if (strpos($output, $bom) === 0) {
    $output = substr($output, 3);
}
// Remove any leading whitespace before DOCTYPE
echo ltrim($output);
