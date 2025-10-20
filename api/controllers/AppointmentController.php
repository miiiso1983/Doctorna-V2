<?php
namespace API;

/**
 * Appointment Controller
 */
class AppointmentController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * List appointments
     */
    public function list() {
        $userId = \AuthMiddleware::userId();
        $role = \AuthMiddleware::userRole();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : API_PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;
        
        // Build query based on role
        if ($role === 'patient') {
            $stmt = $this->db->prepare("SELECT id FROM patients WHERE user_id = ?");
            $stmt->execute([$userId]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);
            $patientId = $patient['id'];
            
            $where = "a.patient_id = ?";
            $params = [$patientId];
        } elseif ($role === 'doctor') {
            $stmt = $this->db->prepare("SELECT id FROM doctors WHERE user_id = ?");
            $stmt->execute([$userId]);
            $doctor = $stmt->fetch(\PDO::FETCH_ASSOC);
            $doctorId = $doctor['id'];
            
            $where = "a.doctor_id = ?";
            $params = [$doctorId];
        } else {
            $where = "1=1";
            $params = [];
        }
        
        // Status filter
        if (isset($_GET['status'])) {
            $where .= " AND a.status = ?";
            $params[] = $_GET['status'];
        }
        
        // Get total
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM appointments a WHERE $where");
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        // Get appointments
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                p.user_id as patient_user_id,
                pu.name as patient_name, pu.phone as patient_phone, pu.avatar as patient_avatar,
                d.user_id as doctor_user_id,
                du.name as doctor_name, du.avatar as doctor_avatar,
                d.specialization_id, s.name as specialization_name
            FROM appointments a
            INNER JOIN patients p ON a.patient_id = p.id
            INNER JOIN users pu ON p.user_id = pu.id
            INNER JOIN doctors d ON a.doctor_id = d.id
            INNER JOIN users du ON d.user_id = du.id
            LEFT JOIN specializations s ON d.specialization_id = s.id
            WHERE $where
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        \Response::paginated($appointments, $total, $page, $limit);
    }
    
    /**
     * Create appointment
     */
    public function create() {
        $userId = \AuthMiddleware::userId();
        $role = \AuthMiddleware::userRole();
        
        if ($role !== 'patient') {
            \Response::forbidden('فقط المرضى يمكنهم حجز المواعيد');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $validator = new \Validator($data);
        $validator->required(['doctor_id', 'appointment_date', 'appointment_time'])
                  ->date('appointment_date')
                  ->numeric('doctor_id');
        
        if ($validator->fails()) {
            \Response::validationError($validator->errors());
        }
        
        // Get patient ID
        $stmt = $this->db->prepare("SELECT id FROM patients WHERE user_id = ?");
        $stmt->execute([$userId]);
        $patient = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$patient) {
            \Response::error('ملف المريض غير موجود', 404);
        }
        
        // Check if slot is available
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM appointments 
            WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?
            AND status NOT IN ('cancelled', 'no_show')
        ");
        $stmt->execute([$data['doctor_id'], $data['appointment_date'], $data['appointment_time']]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            \Response::error('هذا الموعد محجوز بالفعل', 409);
        }
        
        // Get doctor consultation fee
        $stmt = $this->db->prepare("SELECT consultation_fee FROM doctors WHERE id = ?");
        $stmt->execute([$data['doctor_id']]);
        $doctor = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        try {
            // Create appointment
            $stmt = $this->db->prepare("
                INSERT INTO appointments (
                    patient_id, doctor_id, appointment_date, appointment_time,
                    duration, status, type, symptoms, notes, fee, payment_status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $patient['id'],
                $data['doctor_id'],
                $data['appointment_date'],
                $data['appointment_time'],
                $data['duration'] ?? 30,
                $data['type'] ?? 'consultation',
                $data['symptoms'] ?? null,
                $data['notes'] ?? null,
                $doctor['consultation_fee']
            ]);
            
            $appointmentId = $this->db->lastInsertId();
            
            // Get created appointment
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       du.name as doctor_name, 
                       s.name as specialization_name
                FROM appointments a
                INNER JOIN doctors d ON a.doctor_id = d.id
                INNER JOIN users du ON d.user_id = du.id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE a.id = ?
            ");
            $stmt->execute([$appointmentId]);
            $appointment = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            \Response::success($appointment, SUCCESS_MESSAGES['appointment_created'], 201);
            
        } catch (\Exception $e) {
            \Response::error('فشل حجز الموعد: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get appointment details
     */
    public function details($id) {
        if (!$id) {
            \Response::error('معرف الموعد مطلوب', 400);
        }
        
        $userId = \AuthMiddleware::userId();
        $role = \AuthMiddleware::userRole();
        
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                p.user_id as patient_user_id,
                pu.name as patient_name, pu.phone as patient_phone, pu.email as patient_email,
                pu.avatar as patient_avatar, pu.address as patient_address,
                pat.date_of_birth, pat.gender, pat.blood_type, pat.medical_history,
                pat.allergies, pat.current_medications,
                d.user_id as doctor_user_id,
                du.name as doctor_name, du.phone as doctor_phone, du.email as doctor_email,
                du.avatar as doctor_avatar,
                d.specialization_id, d.license_number, d.experience_years,
                d.clinic_name, d.clinic_address, d.clinic_phone,
                s.name as specialization_name, s.name_en as specialization_name_en
            FROM appointments a
            INNER JOIN patients p ON a.patient_id = p.id
            INNER JOIN users pu ON p.user_id = pu.id
            LEFT JOIN patients pat ON p.id = pat.id
            INNER JOIN doctors d ON a.doctor_id = d.id
            INNER JOIN users du ON d.user_id = du.id
            LEFT JOIN specializations s ON d.specialization_id = s.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $appointment = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            \Response::notFound('الموعد غير موجود');
        }
        
        // Check authorization
        if ($role === 'patient' && $appointment['patient_user_id'] != $userId) {
            \Response::forbidden();
        }
        if ($role === 'doctor' && $appointment['doctor_user_id'] != $userId) {
            \Response::forbidden();
        }
        
        \Response::success($appointment);
    }
    
    /**
     * Cancel appointment
     */
    public function cancel($id) {
        if (!$id) {
            \Response::error('معرف الموعد مطلوب', 400);
        }
        
        $userId = \AuthMiddleware::userId();
        $role = \AuthMiddleware::userRole();
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Get appointment
        $stmt = $this->db->prepare("
            SELECT a.*, p.user_id as patient_user_id, d.user_id as doctor_user_id
            FROM appointments a
            INNER JOIN patients p ON a.patient_id = p.id
            INNER JOIN doctors d ON a.doctor_id = d.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $appointment = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            \Response::notFound('الموعد غير موجود');
        }
        
        // Check authorization
        if ($role === 'patient' && $appointment['patient_user_id'] != $userId) {
            \Response::forbidden();
        }
        if ($role === 'doctor' && $appointment['doctor_user_id'] != $userId) {
            \Response::forbidden();
        }
        
        // Update appointment
        $stmt = $this->db->prepare("
            UPDATE appointments 
            SET status = 'cancelled', 
                cancellation_reason = ?,
                cancelled_by = ?,
                cancelled_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $data['reason'] ?? null,
            $role,
            $id
        ]);
        
        \Response::success([], SUCCESS_MESSAGES['appointment_cancelled']);
    }
    
    /**
     * Confirm appointment (doctor only)
     */
    public function confirm($id) {
        \AuthMiddleware::requireRole('doctor');
        
        if (!$id) {
            \Response::error('معرف الموعد مطلوب', 400);
        }
        
        $stmt = $this->db->prepare("
            UPDATE appointments 
            SET status = 'confirmed', confirmed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        \Response::success([], SUCCESS_MESSAGES['appointment_confirmed']);
    }
    
    /**
     * Complete appointment (doctor only)
     */
    public function complete($id) {
        \AuthMiddleware::requireRole('doctor');
        
        if (!$id) {
            \Response::error('معرف الموعد مطلوب', 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("
            UPDATE appointments 
            SET status = 'completed', 
                completed_at = NOW(),
                doctor_notes = ?,
                prescription = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['doctor_notes'] ?? null,
            $data['prescription'] ?? null,
            $id
        ]);
        
        \Response::success([], 'تم إكمال الموعد بنجاح');
    }
}

