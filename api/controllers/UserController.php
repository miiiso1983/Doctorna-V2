<?php
namespace API;

/**
 * User Controller
 */
class UserController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get user profile
     */
    public function getProfile() {
        $userId = \AuthMiddleware::userId();
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            \Response::notFound();
        }
        
        unset($user['password']);
        
        // Get role-specific profile
        $profile = null;
        if ($user['role'] === 'doctor') {
            $stmt = $this->db->prepare("
                SELECT d.*, s.name as specialization_name, s.name_en as specialization_name_en
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
     * Update user profile
     */
    public function updateProfile() {
        $userId = \AuthMiddleware::userId();
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $this->db->beginTransaction();
            
            // Update user table
            $userFields = ['name', 'phone', 'address', 'city', 'country', 'latitude', 'longitude'];
            $updates = [];
            $params = [];
            
            foreach ($userFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (!empty($updates)) {
                $params[] = $userId;
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }
            
            // Update role-specific profile
            $role = \AuthMiddleware::userRole();
            
            if ($role === 'doctor') {
                $doctorFields = ['specialization_id', 'license_number', 'experience_years', 'biography', 
                                'education', 'certifications', 'languages', 'consultation_fee',
                                'clinic_name', 'clinic_address', 'clinic_phone'];
                $updates = [];
                $params = [];
                
                foreach ($doctorFields as $field) {
                    if (isset($data[$field])) {
                        $updates[] = "$field = ?";
                        $params[] = $data[$field];
                    }
                }
                
                if (!empty($updates)) {
                    $params[] = $userId;
                    $sql = "UPDATE doctors SET " . implode(', ', $updates) . " WHERE user_id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);
                }
            } elseif ($role === 'patient') {
                $patientFields = ['date_of_birth', 'gender', 'blood_type', 'height', 'weight',
                                 'emergency_contact', 'emergency_contact_name', 'medical_history',
                                 'allergies', 'current_medications', 'insurance_provider', 'insurance_number'];
                $updates = [];
                $params = [];
                
                foreach ($patientFields as $field) {
                    if (isset($data[$field])) {
                        $updates[] = "$field = ?";
                        $params[] = $data[$field];
                    }
                }
                
                if (!empty($updates)) {
                    $params[] = $userId;
                    $sql = "UPDATE patients SET " . implode(', ', $updates) . " WHERE user_id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);
                }
            }
            
            $this->db->commit();
            
            \Response::success([], SUCCESS_MESSAGES['profile_updated']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            \Response::error('فشل تحديث الملف الشخصي: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $userId = \AuthMiddleware::userId();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $validator = new \Validator($data);
        $validator->required(['current_password', 'new_password'])
                  ->minLength('new_password', 6);
        
        if ($validator->fails()) {
            \Response::validationError($validator->errors());
        }
        
        // Verify current password
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!password_verify($data['current_password'], $user['password'])) {
            \Response::error('كلمة المرور الحالية غير صحيحة', 400);
        }
        
        // Update password
        $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        \Response::success([], SUCCESS_MESSAGES['password_changed']);
    }
    
    /**
     * Upload avatar
     */
    public function uploadAvatar() {
        $userId = \AuthMiddleware::userId();
        
        if (!isset($_FILES['avatar'])) {
            \Response::error('لم يتم رفع أي ملف', 400);
        }
        
        $file = $_FILES['avatar'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            \Response::error(ERROR_MESSAGES['upload_failed'], 400);
        }
        
        if ($file['size'] > API_MAX_UPLOAD_SIZE) {
            \Response::error(ERROR_MESSAGES['file_too_large'], 400);
        }
        
        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
            \Response::error(ERROR_MESSAGES['invalid_file_type'], 400);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
        $filepath = AVATAR_DIR . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            \Response::error(ERROR_MESSAGES['upload_failed'], 500);
        }
        
        // Update user avatar
        $avatarUrl = '/uploads/avatars/' . $filename;
        $stmt = $this->db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$avatarUrl, $userId]);
        
        \Response::success([
            'avatar_url' => $avatarUrl
        ], 'تم رفع الصورة بنجاح');
    }
}

