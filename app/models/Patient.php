<?php
/**
 * Patient Model
 */

require_once APP_PATH . '/core/Model.php';

class Patient extends Model {
    protected $table = 'patients';
    protected $fillable = [
        'user_id', 'date_of_birth', 'gender', 'blood_type', 'height', 'weight',
        'emergency_contact', 'emergency_contact_name', 'medical_history',
        'allergies', 'current_medications', 'insurance_provider', 'insurance_number'
    ];
    
    /**
     * Get patient by user ID
     */
    public function getByUserId($userId) {
        $sql = "SELECT p.*, u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                       u.latitude, u.longitude
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.user_id = :user_id";
        
        return $this->db->fetch($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get all patients with their details
     */
    public function getAllPatients($page = 1) {
        $conditions = ["u.status = 'active'"];
        $params = [];
        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT p.*, u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                       u.created_at as registration_date
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE {$whereClause}
                ORDER BY u.created_at DESC";

        // Total count with join
        $countSql = "SELECT COUNT(*) as count
                     FROM {$this->table} p
                     LEFT JOIN users u ON p.user_id = u.id
                     WHERE {$whereClause}";
        $total = (int)$this->db->fetch($countSql, $params)['count'];

        // Pagination
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $results = $this->db->fetchAll($sql, $params);

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $total ? ($offset + 1) : 0,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Search patients
     */
    public function searchPatients($query, $page = 1) {
        $conditions = ["u.status = 'active'"];
        $params = [];
        
        if ($query) {
            $conditions[] = "(u.name LIKE :query OR u.email LIKE :query OR u.phone LIKE :query)";
            $params['query'] = "%{$query}%";
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT p.*, u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                       u.created_at as registration_date
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE {$whereClause}
                ORDER BY u.created_at DESC";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count
                     FROM {$this->table} p
                     LEFT JOIN users u ON p.user_id = u.id
                     WHERE {$whereClause}";
        
        $total = $this->db->fetch($countSql, $params)['count'];
        
        // Get paginated results
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $results = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Get patient statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stats['total_patients'] = $this->count();
        
        // Gender distribution
        $stats['male_patients'] = $this->count('gender = :gender', ['gender' => 'male']);
        $stats['female_patients'] = $this->count('gender = :gender', ['gender' => 'female']);
        
        // Age groups
        $currentDate = date('Y-m-d');
        $stats['children'] = $this->count('date_of_birth > :date', ['date' => date('Y-m-d', strtotime('-18 years'))]);
        $stats['adults'] = $this->count('date_of_birth BETWEEN :start AND :end', [
            'start' => date('Y-m-d', strtotime('-65 years')),
            'end' => date('Y-m-d', strtotime('-18 years'))
        ]);
        $stats['seniors'] = $this->count('date_of_birth < :date', ['date' => date('Y-m-d', strtotime('-65 years'))]);
        
        // Recent registrations (last 30 days)
        $stats['recent_registrations'] = $this->count(
            'created_at >= :date',
            ['date' => date('Y-m-d', strtotime('-30 days'))]
        );
        
        return $stats;
    }
    
    /**
     * Get patient's medical history
     */
    public function getMedicalHistory($patientId) {
        // Get patient symptoms
        $symptomsSql = "SELECT ps.*, s.name as symptom_name, s.category
                        FROM patient_symptoms ps
                        LEFT JOIN symptoms s ON ps.symptom_id = s.id
                        WHERE ps.patient_id = :patient_id
                        ORDER BY ps.reported_at DESC";
        
        $symptoms = $this->db->fetchAll($symptomsSql, ['patient_id' => $patientId]);
        
        // Get patient appointments with diagnoses
        $appointmentsSql = "SELECT a.*, d.name as doctor_name, s.name as specialization_name
                           FROM appointments a
                           LEFT JOIN doctors doc ON a.doctor_id = doc.id
                           LEFT JOIN users d ON doc.user_id = d.id
                           LEFT JOIN specializations s ON doc.specialization_id = s.id
                           WHERE a.patient_id = :patient_id
                           AND a.status = 'completed'
                           ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $appointments = $this->db->fetchAll($appointmentsSql, ['patient_id' => $patientId]);
        
        return [
            'symptoms' => $symptoms,
            'appointments' => $appointments
        ];
    }
    
    /**
     * Add patient symptoms
     */
    public function addSymptoms($patientId, $symptoms) {
        $this->beginTransaction();
        
        try {
            foreach ($symptoms as $symptom) {
                $this->db->insert('patient_symptoms', [
                    'patient_id' => $patientId,
                    'symptom_id' => $symptom['symptom_id'],
                    'severity' => $symptom['severity'] ?? 'mild',
                    'duration' => $symptom['duration'] ?? '',
                    'notes' => $symptom['notes'] ?? '',
                    'reported_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }
    
    /**
     * Get patient's current symptoms
     */
    public function getCurrentSymptoms($patientId, $days = 30) {
        $sql = "SELECT ps.*, s.name as symptom_name, s.category, s.severity_level
                FROM patient_symptoms ps
                LEFT JOIN symptoms s ON ps.symptom_id = s.id
                WHERE ps.patient_id = :patient_id
                AND ps.reported_at >= :date
                ORDER BY ps.reported_at DESC";
        
        return $this->db->fetchAll($sql, [
            'patient_id' => $patientId,
            'date' => date('Y-m-d', strtotime("-{$days} days"))
        ]);
    }
    
    /**
     * Get recommended specializations based on symptoms
     */
    public function getRecommendedSpecializations($patientId) {
        $sql = "SELECT s.id, s.name, s.name_en, s.description, s.icon, s.color,
                       AVG(ss.relevance_score) as relevance_score,
                       COUNT(ps.symptom_id) as symptom_count
                FROM patient_symptoms ps
                LEFT JOIN symptom_specializations ss ON ps.symptom_id = ss.symptom_id
                LEFT JOIN specializations s ON ss.specialization_id = s.id
                WHERE ps.patient_id = :patient_id
                AND ps.reported_at >= :date
                AND s.is_active = 1
                GROUP BY s.id
                HAVING symptom_count > 0
                ORDER BY relevance_score DESC, symptom_count DESC
                LIMIT 5";
        
        return $this->db->fetchAll($sql, [
            'patient_id' => $patientId,
            'date' => date('Y-m-d', strtotime('-30 days'))
        ]);
    }
    
    /**
     * Calculate patient age
     */
    public function calculateAge($dateOfBirth) {
        if (!$dateOfBirth) {
            return null;
        }
        
        $birthDate = new DateTime($dateOfBirth);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDate);
        
        return $age->y;
    }
    
    /**
     * Get patients by age group
     */
    public function getPatientsByAgeGroup($ageGroup) {
        $currentDate = date('Y-m-d');
        
        switch ($ageGroup) {
            case 'children':
                $condition = 'date_of_birth > :date';
                $params = ['date' => date('Y-m-d', strtotime('-18 years'))];
                break;
                
            case 'adults':
                $condition = 'date_of_birth BETWEEN :start AND :end';
                $params = [
                    'start' => date('Y-m-d', strtotime('-65 years')),
                    'end' => date('Y-m-d', strtotime('-18 years'))
                ];
                break;
                
            case 'seniors':
                $condition = 'date_of_birth < :date';
                $params = ['date' => date('Y-m-d', strtotime('-65 years'))];
                break;
                
            default:
                return [];
        }
        
        return $this->where($condition, $params, 'created_at DESC');
    }
    
    /**
     * Update patient profile
     */
    public function updateProfile($patientId, $data) {
        // Filter only allowed fields
        $allowedFields = [
            'date_of_birth', 'gender', 'blood_type', 'height', 'weight',
            'emergency_contact', 'emergency_contact_name', 'medical_history',
            'allergies', 'current_medications', 'insurance_provider', 'insurance_number'
        ];
        
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        return $this->update($patientId, $filteredData);
    }
}
