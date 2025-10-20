<?php
/**
 * طبيبك REST API
 * Main Entry Point for Mobile App API
 * 
 * @version 1.0.0
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Define constants
define('API_ROOT', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// Load configuration
require_once ROOT_PATH . '/config/database.php';
require_once API_ROOT . '/config/config.php';

// Load helpers
require_once API_ROOT . '/helpers/Response.php';
require_once API_ROOT . '/helpers/JWT.php';
require_once API_ROOT . '/helpers/Validator.php';

// Load middleware
require_once API_ROOT . '/middleware/AuthMiddleware.php';

// Load controllers
require_once API_ROOT . '/controllers/AuthController.php';
require_once API_ROOT . '/controllers/UserController.php';
require_once API_ROOT . '/controllers/DoctorController.php';
require_once API_ROOT . '/controllers/AppointmentController.php';
require_once API_ROOT . '/controllers/HealthPostController.php';
require_once API_ROOT . '/controllers/NotificationController.php';
require_once API_ROOT . '/controllers/SpecializationController.php';
require_once API_ROOT . '/controllers/ReviewController.php';
require_once API_ROOT . '/controllers/ChatController.php';

// Load models (reuse existing models)
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Doctor.php';
require_once ROOT_PATH . '/app/models/Patient.php';
require_once ROOT_PATH . '/app/models/Appointment.php';
require_once ROOT_PATH . '/app/models/HealthPost.php';
require_once ROOT_PATH . '/app/models/Notification.php';
require_once ROOT_PATH . '/app/models/Specialization.php';

// Simple router
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string and API prefix
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace('/api', '', $path);
$path = trim($path, '/');

// Split path into segments
$segments = $path ? explode('/', $path) : [];

// Get database connection
$db = Database::getInstance()->getConnection();

try {
    // Route the request
    if (empty($segments)) {
        Response::success(['message' => 'طبيبك API v1.0', 'status' => 'active']);
    }
    
    $resource = $segments[0] ?? '';
    
    switch ($resource) {
        case 'auth':
            $controller = new API\AuthController($db);
            $action = $segments[1] ?? 'index';
            
            switch ($action) {
                case 'login':
                    $controller->login();
                    break;
                case 'register':
                    $controller->register();
                    break;
                case 'logout':
                    $controller->logout();
                    break;
                case 'refresh':
                    $controller->refresh();
                    break;
                case 'me':
                    $controller->me();
                    break;
                default:
                    Response::error('Invalid auth endpoint', 404);
            }
            break;
            
        case 'users':
            AuthMiddleware::authenticate();
            $controller = new API\UserController($db);
            $action = $segments[1] ?? 'index';
            
            switch ($action) {
                case 'profile':
                    $controller->getProfile();
                    break;
                case 'update':
                    $controller->updateProfile();
                    break;
                case 'change-password':
                    $controller->changePassword();
                    break;
                case 'upload-avatar':
                    $controller->uploadAvatar();
                    break;
                default:
                    Response::error('Invalid user endpoint', 404);
            }
            break;
            
        case 'doctors':
            $controller = new API\DoctorController($db);
            $action = $segments[1] ?? 'index';
            
            switch ($action) {
                case 'list':
                case '':
                    $controller->list();
                    break;
                case 'search':
                    $controller->search();
                    break;
                case 'details':
                    $id = $segments[2] ?? null;
                    $controller->details($id);
                    break;
                case 'availability':
                    $id = $segments[2] ?? null;
                    $controller->availability($id);
                    break;
                case 'specializations':
                    $controller->specializations();
                    break;
                default:
                    // Check if it's a numeric ID
                    if (is_numeric($action)) {
                        $controller->details($action);
                    } else {
                        Response::error('Invalid doctor endpoint', 404);
                    }
            }
            break;
            
        case 'appointments':
            AuthMiddleware::authenticate();
            $controller = new API\AppointmentController($db);
            $action = $segments[1] ?? 'index';
            
            switch ($action) {
                case 'list':
                case '':
                    $controller->list();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'details':
                    $id = $segments[2] ?? null;
                    $controller->details($id);
                    break;
                case 'cancel':
                    $id = $segments[2] ?? null;
                    $controller->cancel($id);
                    break;
                case 'confirm':
                    $id = $segments[2] ?? null;
                    $controller->confirm($id);
                    break;
                case 'complete':
                    $id = $segments[2] ?? null;
                    $controller->complete($id);
                    break;
                default:
                    Response::error('Invalid appointment endpoint', 404);
            }
            break;
            
        case 'health-posts':
            $controller = new API\HealthPostController($db);
            $action = $segments[1] ?? 'index';
            
            switch ($action) {
                case 'list':
                case '':
                    $controller->list();
                    break;
                case 'details':
                    $id = $segments[2] ?? null;
                    $controller->details($id);
                    break;
                case 'create':
                    AuthMiddleware::authenticate();
                    $controller->create();
                    break;
                case 'update':
                    AuthMiddleware::authenticate();
                    $id = $segments[2] ?? null;
                    $controller->update($id);
                    break;
                case 'delete':
                    AuthMiddleware::authenticate();
                    $id = $segments[2] ?? null;
                    $controller->delete($id);
                    break;
                default:
                    if (is_numeric($action)) {
                        $controller->details($action);
                    } else {
                        Response::error('Invalid health post endpoint', 404);
                    }
            }
            break;
            
        case 'notifications':
            AuthMiddleware::authenticate();
            $controller = new API\NotificationController($db);
            $action = $segments[1] ?? 'index';
            
            switch ($action) {
                case 'list':
                case '':
                    $controller->list();
                    break;
                case 'read':
                    $id = $segments[2] ?? null;
                    $controller->markAsRead($id);
                    break;
                case 'read-all':
                    $controller->markAllAsRead();
                    break;
                case 'unread-count':
                    $controller->unreadCount();
                    break;
                default:
                    Response::error('Invalid notification endpoint', 404);
            }
            break;
            
        case 'specializations':
            $controller = new API\SpecializationController($db);
            $controller->list();
            break;

        case 'reviews':
            $controller = new API\ReviewController($db);
            $action = $segments[1] ?? 'index';

            switch ($action) {
                case 'doctor':
                    $doctorId = $segments[2] ?? null;
                    if ($segments[3] ?? '' === 'summary') {
                        $controller->getDoctorRatingSummary($doctorId);
                    } else {
                        $controller->getDoctorReviews($doctorId);
                    }
                    break;
                case 'add':
                    AuthMiddleware::authenticate();
                    $controller->addReview();
                    break;
                case 'delete':
                    AuthMiddleware::authenticate();
                    $id = $segments[2] ?? null;
                    $controller->deleteReview($id);
                    break;
                case 'my-review':
                    AuthMiddleware::authenticate();
                    $doctorId = $segments[2] ?? null;
                    $controller->getMyReview($doctorId);
                    break;
                default:
                    Response::error('Invalid review endpoint', 404);
            }
            break;

        case 'chat':
            AuthMiddleware::authenticate();
            $controller = new API\ChatController($db);
            $action = $segments[1] ?? 'index';

            switch ($action) {
                case 'conversations':
                case '':
                    $controller->getConversations();
                    break;
                case 'messages':
                    $conversationId = $segments[2] ?? null;
                    $controller->getMessages($conversationId);
                    break;
                case 'send':
                    $controller->sendMessage();
                    break;
                case 'mark-read':
                    $conversationId = $segments[2] ?? null;
                    $controller->markAsRead($conversationId);
                    break;
                case 'unread-count':
                    $controller->getUnreadCount();
                    break;
                case 'upload':
                    $controller->uploadAttachment();
                    break;
                default:
                    Response::error('Invalid chat endpoint', 404);
            }
            break;

        default:
            Response::error('Resource not found', 404);
    }
    
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}

