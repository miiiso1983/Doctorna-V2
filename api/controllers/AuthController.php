<?php
namespace API;

/**
 * Authentication Controller
 */
class AuthController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Login
     * POST /api/auth/login
     */
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $validator = new \Validator($data);
        $validator->required(['email', 'password'])
                  ->email('email');
        
        if ($validator->fails()) {
            \Response::validationError($validator->errors());
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        // Find user
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            \Response::error(ERROR_MESSAGES['invalid_credentials'], 401);
        }
        
        // Get additional profile data based on role
        $profile = null;
        if ($user['role'] === 'doctor') {
            $stmt = $this->db->prepare("
                SELECT d.*, s.name as specialization_name, s.name_en as specialization_name_en
                FROM doctors d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE d.user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
        } elseif ($user['role'] === 'patient') {
            $stmt = $this->db->prepare("SELECT * FROM patients WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        
        // Update last login
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Generate JWT token
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'name' => $user['name']
        ];
        
        $token = \JWT::encode($payload);
        $refreshToken = \JWT::encode($payload, JWT_REFRESH_EXPIRATION);
        
        // Remove sensitive data
        unset($user['password']);
        
        \Response::success([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $user,
            'profile' => $profile,
            'expires_in' => JWT_EXPIRATION
        ], SUCCESS_MESSAGES['login_success']);
    }
    
    /**
     * Register
     * POST /api/auth/register
     */
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $validator = new \Validator($data);
        $validator->required(['name', 'email', 'password', 'phone', 'role'])
                  ->email('email')
                  ->minLength('password', 6)
                  ->in('role', ['doctor', 'patient']);
        
        if ($validator->fails()) {
            \Response::validationError($validator->errors());
        }
        
        // Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            \Response::error(ERROR_MESSAGES['email_exists'], 409);
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $this->db->beginTransaction();
            
            // Create user
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, phone, password, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $hashedPassword,
                $data['role']
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Create role-specific profile
            if ($data['role'] === 'doctor') {
                $stmt = $this->db->prepare("
                    INSERT INTO doctors (user_id, status, created_at)
                    VALUES (?, 'pending', NOW())
                ");
                $stmt->execute([$userId]);
            } elseif ($data['role'] === 'patient') {
                $stmt = $this->db->prepare("
                    INSERT INTO patients (user_id, created_at)
                    VALUES (?, NOW())
                ");
                $stmt->execute([$userId]);
            }
            
            $this->db->commit();
            
            // Get created user
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            unset($user['password']);
            
            // Generate token
            $payload = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'name' => $user['name']
            ];
            
            $token = \JWT::encode($payload);
            $refreshToken = \JWT::encode($payload, JWT_REFRESH_EXPIRATION);
            
            \Response::success([
                'token' => $token,
                'refresh_token' => $refreshToken,
                'user' => $user,
                'expires_in' => JWT_EXPIRATION
            ], SUCCESS_MESSAGES['register_success'], 201);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            \Response::error('فشل التسجيل: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get current user
     * GET /api/auth/me
     */
    public function me() {
        \AuthMiddleware::authenticate();
        
        $userId = \AuthMiddleware::userId();
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            \Response::notFound(ERROR_MESSAGES['user_not_found']);
        }
        
        unset($user['password']);
        
        // Get profile
        $profile = null;
        if ($user['role'] === 'doctor') {
            $stmt = $this->db->prepare("
                SELECT d.*, s.name as specialization_name
                FROM doctors d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE d.user_id = ?
            ");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
        } elseif ($user['role'] === 'patient') {
            $stmt = $this->db->prepare("SELECT * FROM patients WHERE user_id = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        
        \Response::success([
            'user' => $user,
            'profile' => $profile
        ]);
    }
    
    /**
     * Refresh token
     * POST /api/auth/refresh
     */
    public function refresh() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['refresh_token'])) {
            \Response::error('Refresh token مطلوب', 400);
        }
        
        try {
            $payload = \JWT::decode($data['refresh_token']);
            
            // Generate new tokens
            $newPayload = [
                'user_id' => $payload['user_id'],
                'email' => $payload['email'],
                'role' => $payload['role'],
                'name' => $payload['name']
            ];
            
            $token = \JWT::encode($newPayload);
            $refreshToken = \JWT::encode($newPayload, JWT_REFRESH_EXPIRATION);
            
            \Response::success([
                'token' => $token,
                'refresh_token' => $refreshToken,
                'expires_in' => JWT_EXPIRATION
            ]);
            
        } catch (\Exception $e) {
            \Response::error($e->getMessage(), 401);
        }
    }
    
    /**
     * Logout
     * POST /api/auth/logout
     */
    public function logout() {
        \AuthMiddleware::authenticate();
        \Response::success([], SUCCESS_MESSAGES['logout_success']);
    }
}

