<?php
/**
 * Authentication Class
 * Handles user authentication and authorization
 */

class Auth {
    private $db;
    private $sessionKey = 'user_id';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Attempt to authenticate user
     */
    public function attempt($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            $this->login($user);
            return true;
        }
        
        return false;
    }
    
    /**
     * Login user
     */
    public function login($user) {
        $_SESSION[$this->sessionKey] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        
        // Update last login
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $user['id']]);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        unset($_SESSION[$this->sessionKey]);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_name']);
        
        // Destroy session
        session_destroy();
        session_start();
    }
    
    /**
     * Check if user is authenticated
     */
    public function check() {
        return isset($_SESSION[$this->sessionKey]) && !empty($_SESSION[$this->sessionKey]);
    }
    
    /**
     * Get current user
     */
    public function user() {
        if (!$this->check()) {
            return null;
        }
        
        $userId = $_SESSION[$this->sessionKey];
        return $this->getUserById($userId);
    }
    
    /**
     * Get current user ID
     */
    public function id() {
        return $_SESSION[$this->sessionKey] ?? null;
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        if (!$this->check()) {
            return false;
        }
        
        return $_SESSION['user_role'] === $role;
    }
    
    /**
     * Check if user is super admin
     */
    public function isSuperAdmin() {
        return $this->hasRole(ROLE_SUPER_ADMIN);
    }
    
    /**
     * Check if user is doctor
     */
    public function isDoctor() {
        return $this->hasRole(ROLE_DOCTOR);
    }
    
    /**
     * Check if user is patient
     */
    public function isPatient() {
        return $this->hasRole(ROLE_PATIENT);
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            return false;
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = ROLE_PATIENT;
        }
        
        // Set timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Insert user
        $userId = $this->db->insert('users', $data);
        
        if ($userId) {
            // Create role-specific profile
            $this->createUserProfile($userId, $data['role'], $data);
            return $this->getUserById($userId);
        }
        
        return false;
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->db->update('users', [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $userId]);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email) {
        return $this->db->exists('users', 'email = :email', ['email' => $email]);
    }
    
    /**
     * Get user by email
     */
    private function getUserByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1",
            ['email' => $email]
        );
    }
    
    /**
     * Get user by ID
     */
    private function getUserById($id) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = :id AND status = 'active' LIMIT 1",
            ['id' => $id]
        );
    }
    
    /**
     * Create user profile based on role
     */
    private function createUserProfile($userId, $role, $data) {
        $profileData = [
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        switch ($role) {
            case ROLE_DOCTOR:
                $profileData = array_merge($profileData, [
                    'specialization_id' => $data['specialization_id'] ?? null,
                    'license_number' => $data['license_number'] ?? '',
                    'experience_years' => $data['experience_years'] ?? 0,
                    'biography' => $data['biography'] ?? '',
                    'consultation_fee' => $data['consultation_fee'] ?? 0,
                    'status' => DOCTOR_PENDING
                ]);
                $this->db->insert('doctors', $profileData);
                break;
                
            case ROLE_PATIENT:
                $profileData = array_merge($profileData, [
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? '',
                    'blood_type' => $data['blood_type'] ?? '',
                    'emergency_contact' => $data['emergency_contact'] ?? '',
                    'medical_history' => $data['medical_history'] ?? ''
                ]);
                $this->db->insert('patients', $profileData);
                break;
        }
    }
    
    /**
     * Generate password reset token
     */
    public function generateResetToken($email) {
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $this->db->query(
            "INSERT INTO password_resets (email, token, expires_at, created_at) 
             VALUES (:email, :token, :expires, :created) 
             ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires, created_at = :created",
            [
                'email' => $email,
                'token' => $token,
                'expires' => $expires,
                'created' => date('Y-m-d H:i:s')
            ]
        );
        
        return $token;
    }
    
    /**
     * Verify password reset token
     */
    public function verifyResetToken($token) {
        return $this->db->fetch(
            "SELECT * FROM password_resets 
             WHERE token = :token AND expires_at > NOW() 
             LIMIT 1",
            ['token' => $token]
        );
    }
    
    /**
     * Reset password using token
     */
    public function resetPassword($token, $newPassword) {
        $reset = $this->verifyResetToken($token);
        
        if (!$reset) {
            return false;
        }
        
        $user = $this->getUserByEmail($reset['email']);
        
        if (!$user) {
            return false;
        }
        
        // Update password
        $this->updatePassword($user['id'], $newPassword);
        
        // Delete reset token
        $this->db->delete('password_resets', 'token = :token', ['token' => $token]);
        
        return true;
    }
    
    /**
     * Get user's full profile with role-specific data
     */
    public function getFullProfile($userId = null) {
        $userId = $userId ?? $this->id();
        
        if (!$userId) {
            return null;
        }
        
        $user = $this->getUserById($userId);
        
        if (!$user) {
            return null;
        }
        
        // Get role-specific profile
        switch ($user['role']) {
            case ROLE_DOCTOR:
                $profile = $this->db->fetch(
                    "SELECT d.*, s.name as specialization_name 
                     FROM doctors d 
                     LEFT JOIN specializations s ON d.specialization_id = s.id 
                     WHERE d.user_id = :user_id",
                    ['user_id' => $userId]
                );
                break;
                
            case ROLE_PATIENT:
                $profile = $this->db->fetch(
                    "SELECT * FROM patients WHERE user_id = :user_id",
                    ['user_id' => $userId]
                );
                break;
                
            default:
                $profile = [];
        }
        
        return array_merge($user, $profile ?: []);
    }
}
