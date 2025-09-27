<?php
/**
 * Base Controller Class
 * All controllers extend from this class
 */

class Controller {
    protected $view;
    protected $auth;
    protected $csrf;
    
    public function __construct() {
        $this->view = new View();
        $this->auth = new Auth();
        $this->csrf = new CSRF();
    }
    
    /**
     * Render a view
     */
    protected function render($view, $data = []) {
        return $this->view->render($view, $data);
    }
    
    /**
     * Render a view with layout
     */
    protected function renderWithLayout($view, $data = [], $layout = 'main') {
        return $this->view->renderWithLayout($view, $data, $layout);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Return success JSON response
     */
    protected function success($message = 'تم بنجاح', $data = []) {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Return error JSON response
     */
    protected function error($message = 'حدث خطأ', $statusCode = 400, $errors = []) {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    /**
     * Redirect back
     */
    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Get request input
     */
    protected function input($key = null, $default = null) {
        if ($key === null) {
            return array_merge($_GET, $_POST);
        }
        
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Get POST data
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get uploaded file
     */
    protected function file($key) {
        return $_FILES[$key] ?? null;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF() {
        if (!$this->csrf->validate()) {
            $this->error('رمز الحماية غير صحيح', 403);
        }
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!$this->auth->check()) {
            if ($this->isAjax()) {
                $this->error('يجب تسجيل الدخول أولاً', 401);
            } else {
                $this->redirect('/login');
            }
        }
    }
    
    /**
     * Check if user has specific role
     */
    protected function requireRole($role) {
        $this->requireAuth();
        
        if (!$this->auth->hasRole($role)) {
            if ($this->isAjax()) {
                $this->error('ليس لديك صلاحية للوصول لهذه الصفحة', 403);
            } else {
                $this->redirect('/');
            }
        }
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if request method is POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request method is GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Validate input data
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $fieldRules = explode('|', $rule);
            
            foreach ($fieldRules as $fieldRule) {
                $ruleParts = explode(':', $fieldRule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "حقل {$field} مطلوب";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "حقل {$field} يجب أن يكون بريد إلكتروني صحيح";
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $ruleValue) {
                            $errors[$field][] = "حقل {$field} يجب أن يكون على الأقل {$ruleValue} أحرف";
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $ruleValue) {
                            $errors[$field][] = "حقل {$field} يجب أن يكون أقل من {$ruleValue} حرف";
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = "حقل {$field} يجب أن يكون رقم";
                        }
                        break;
                        
                    case 'phone':
                        if (!empty($value) && !preg_match('/^[0-9+\-\s()]+$/', $value)) {
                            $errors[$field][] = "حقل {$field} يجب أن يكون رقم هاتف صحيح";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Upload file
     */
    protected function uploadFile($file, $directory = 'uploads', $allowedTypes = null) {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $allowedTypes = $allowedTypes ?? ALLOWED_FILE_TYPES;
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            return false;
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }
        
        $uploadDir = PUBLIC_PATH . '/' . trim($directory, '/') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $directory . '/' . $fileName;
        }
        
        return false;
    }
    
    /**
     * Set flash message
     */
    protected function flash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash message
     */
    protected function getFlash($type) {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
}
