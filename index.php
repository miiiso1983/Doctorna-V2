<?php
/**
 * Doctorna - Doctor Appointment Booking System
 * Main Entry Point
 *
 * @author Doctorna Team
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

// Public pages
$router->get('/services', 'HomeController@services');
// Register global error handler (logs to storage/logs/error.log and shows friendly page)
require_once APP_PATH . '/core/ErrorHandler.php';
ErrorHandler::init();

// Start session now that constants and config are available
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
$httpsOn = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($forwardedProto === 'https');
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'secure' => $httpsOn,
    'httponly' => true,
    'samesite' => 'Lax',
]);
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

// Patient routes
$router->get('/patient', 'PatientController@index');
$router->get('/patient/search-doctors', 'PatientController@searchDoctors');
$router->get('/patient/doctor/{id}', 'PatientController@viewDoctor');
// Map search page for patients
$router->get('/patient/map-search', 'PatientController@mapSearch');

$router->get('/patient/profile', 'PatientController@profile');
$router->post('/patient/profile', 'PatientController@updateProfile');
$router->get('/patient/appointments', 'PatientController@appointments');
$router->get('/patient/medical-history', 'PatientController@medicalHistory');

// Settings
$router->get('/patient/settings', 'PatientController@settings');
$router->post('/patient/settings', 'PatientController@updateSettings');

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

// AJAX routes
$router->post('/ajax/appointment/accept', 'AjaxController@acceptAppointment');
$router->post('/ajax/appointment/reject', 'AjaxController@rejectAppointment');
$router->post('/ajax/doctor/availability', 'AjaxController@checkAvailability');
$router->get('/ajax/symptoms/recommend', 'AjaxController@recommendSpecialization');

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
