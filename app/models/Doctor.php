<?php
/**
 * Doctor Model
 */

require_once APP_PATH . '/core/Model.php';

class Doctor extends Model {
    protected $table = 'doctors';
    protected $fillable = [
        'user_id', 'specialization_id', 'license_number', 'experience_years',
        'biography', 'education', 'certifications', 'languages',
        'consultation_fee', 'status', 'clinic_name', 'clinic_address',
        'clinic_phone', 'working_hours', 'available_days'
    ];
    
    /**
     * Get doctor by user ID
     */
    public function getByUserId($userId) {
        $sql = "SELECT d.*, s.name as specialization_name, s.name_en as specialization_name_en,
                       u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                       u.latitude, u.longitude
                FROM {$this->table} d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.user_id = :user_id";
        
        return $this->db->fetch($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get all approved doctors with their details
     */
    public function getApprovedDoctors($page = 1, $perPage = null) {
        $perPage = $perPage ?? DOCTORS_PER_PAGE;
        
        $sql = "SELECT d.*, s.name as specialization_name, s.name_en as specialization_name_en,
                       u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                       u.latitude, u.longitude
                FROM {$this->table} d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.status = 'approved' AND u.status = 'active'
                ORDER BY d.rating DESC, d.total_reviews DESC";
        
        return $this->paginate($page, $perPage, "d.status = 'approved' AND u.status = 'active'", [], 'd.rating DESC, d.total_reviews DESC');
    }
    
    /**
     * Search doctors
     */
    public function searchDoctors($query, $specializationId = null, $city = null, $page = 1) {
        $conditions = ["d.status = 'approved'", "u.status = 'active'"];
        $params = [];
        
        // Search in name, biography, clinic_name
        if ($query) {
            $conditions[] = "(u.name LIKE :query OR d.biography LIKE :query OR d.clinic_name LIKE :query)";
            $params['query'] = "%{$query}%";
        }
        
        // Filter by specialization
        if ($specializationId) {
            $conditions[] = "d.specialization_id = :specialization_id";
            $params['specialization_id'] = $specializationId;
        }
        
        // Filter by city
        if ($city) {
            $conditions[] = "u.city = :city";
            $params['city'] = $city;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT d.*, s.name as specialization_name, s.name_en as specialization_name_en,
                       u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                       u.latitude, u.longitude
                FROM {$this->table} d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE {$whereClause}
                ORDER BY d.rating DESC, d.total_reviews DESC";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count
                     FROM {$this->table} d
                     LEFT JOIN users u ON d.user_id = u.id
                     WHERE {$whereClause}";
        
        $total = $this->db->fetch($countSql, $params)['count'];
        
        // Get paginated results
        $perPage = DOCTORS_PER_PAGE;
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
     * Find nearby doctors
     */
    public function findNearbyDoctors($latitude, $longitude, $radius = 50, $specializationId = null, $page = 1) {
        $conditions = ["d.status = 'approved'", "u.status = 'active'", "u.latitude IS NOT NULL", "u.longitude IS NOT NULL"];
        $params = [
            'lat' => $latitude,
            'lng' => $longitude,
            'radius' => $radius
        ];
        
        if ($specializationId) {
            $conditions[] = "d.specialization_id = :specialization_id";
            $params['specialization_id'] = $specializationId;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        // Calculate distance using Haversine formula
        $sql = "SELECT d.*, s.name as specialization_name, s.name_en as specialization_name_en,
                       u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                       u.latitude, u.longitude,
                       (6371 * acos(cos(radians(:lat)) * cos(radians(u.latitude)) * 
                       cos(radians(u.longitude) - radians(:lng)) + sin(radians(:lat)) * 
                       sin(radians(u.latitude)))) AS distance
                FROM {$this->table} d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE {$whereClause}
                HAVING distance < :radius
                ORDER BY distance ASC, d.rating DESC";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM (
                        SELECT d.id,
                               (6371 * acos(cos(radians(:lat)) * cos(radians(u.latitude)) * 
                               cos(radians(u.longitude) - radians(:lng)) + sin(radians(:lat)) * 
                               sin(radians(u.latitude)))) AS distance
                        FROM {$this->table} d
                        LEFT JOIN users u ON d.user_id = u.id
                        WHERE {$whereClause}
                        HAVING distance < :radius
                     ) as nearby_doctors";
        
        $total = $this->db->fetch($countSql, $params)['count'];
        
        // Get paginated results
        $perPage = DOCTORS_PER_PAGE;
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
     * Get doctor statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stats['total_doctors'] = $this->count();
        $stats['approved_doctors'] = $this->count('status = :status', ['status' => 'approved']);
        $stats['pending_doctors'] = $this->count('status = :status', ['status' => 'pending']);
        $stats['suspended_doctors'] = $this->count('status = :status', ['status' => 'suspended']);
        
        // Average rating
        $avgRating = $this->db->fetch("SELECT AVG(rating) as avg_rating FROM {$this->table} WHERE status = 'approved'");
        $stats['average_rating'] = round($avgRating['avg_rating'], 2);
        
        return $stats;
    }
    
    /**
     * Update doctor status
     */
    public function updateStatus($doctorId, $status) {
        return $this->update($doctorId, ['status' => $status]);
    }
    
    /**
     * Update doctor rating
     */
    public function updateRating($doctorId) {
        $sql = "UPDATE {$this->table} SET 
                rating = (SELECT AVG(rating) FROM reviews WHERE doctor_id = :doctor_id AND is_approved = 1),
                total_reviews = (SELECT COUNT(*) FROM reviews WHERE doctor_id = :doctor_id AND is_approved = 1)
                WHERE id = :doctor_id";
        
        return $this->db->query($sql, ['doctor_id' => $doctorId]);
    }
    
    /**
     * Get doctor's schedule
     */
    public function getSchedule($doctorId) {
        $sql = "SELECT * FROM doctor_schedules WHERE doctor_id = :doctor_id ORDER BY 
                FIELD(day_of_week, 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'),
                start_time";
        
        return $this->db->fetchAll($sql, ['doctor_id' => $doctorId]);
    }
    
    /**
     * Check doctor availability
     */
    public function isAvailable($doctorId, $date, $time) {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $sql = "SELECT COUNT(*) as count FROM doctor_schedules 
                WHERE doctor_id = :doctor_id 
                AND day_of_week = :day_of_week 
                AND start_time <= :time 
                AND end_time > :time 
                AND is_available = 1";
        
        $result = $this->db->fetch($sql, [
            'doctor_id' => $doctorId,
            'day_of_week' => $dayOfWeek,
            'time' => $time
        ]);
        
        if ($result['count'] == 0) {
            return false;
        }
        
        // Check if there's already an appointment at this time
        $appointmentSql = "SELECT COUNT(*) as count FROM appointments 
                          WHERE doctor_id = :doctor_id 
                          AND appointment_date = :date 
                          AND appointment_time = :time 
                          AND status IN ('pending', 'confirmed')";
        
        $appointmentResult = $this->db->fetch($appointmentSql, [
            'doctor_id' => $doctorId,
            'date' => $date,
            'time' => $time
        ]);
        
        return $appointmentResult['count'] == 0;
    }
    
    /**
     * Get available time slots for a doctor on a specific date
     */
    public function getAvailableSlots($doctorId, $date) {
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        // Get doctor's schedule for the day
        $sql = "SELECT start_time, end_time FROM doctor_schedules
                WHERE doctor_id = :doctor_id
                AND day_of_week = :day_of_week
                AND is_available = 1";

        $schedules = $this->db->fetchAll($sql, [
            'doctor_id' => $doctorId,
            'day_of_week' => $dayOfWeek
        ]);

        if (empty($schedules)) {
            return [];
        }

        // Get booked appointments for the day
        $bookedSql = "SELECT appointment_time FROM appointments
                      WHERE doctor_id = :doctor_id
                      AND appointment_date = :date
                      AND status IN ('pending', 'confirmed')";

        $bookedSlots = $this->db->fetchAll($bookedSql, [
            'doctor_id' => $doctorId,
            'date' => $date
        ]);

        $bookedTimes = array_column($bookedSlots, 'appointment_time');

        // Generate available slots
        $availableSlots = [];

        foreach ($schedules as $schedule) {
            $startTime = strtotime($schedule['start_time']);
            $endTime = strtotime($schedule['end_time']);

            for ($time = $startTime; $time < $endTime; $time += 1800) { // 30-minute slots
                $timeSlot = date('H:i:s', $time);

                if (!in_array($timeSlot, $bookedTimes)) {
                    $availableSlots[] = $timeSlot;
                }
            }
        }

        return $availableSlots;
    }

    /**
     * Get doctor with location data
     */
    public function getDoctorWithLocation($id) {
        $sql = "SELECT d.*, s.name as specialization_name, s.icon as specialization_icon,
                       u.name, u.email, u.phone, u.city, u.avatar, u.address, u.created_at,
                       u.latitude, u.longitude,
                       COUNT(DISTINCT a.id) as total_appointments,
                       COUNT(DISTINCT r.id) as total_reviews,
                       COALESCE(AVG(r.rating), 0) as rating
                FROM {$this->table} d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN appointments a ON d.id = a.doctor_id AND a.status = 'completed'
                LEFT JOIN reviews r ON d.id = r.doctor_id
                WHERE d.id = :id
                GROUP BY d.id";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Get doctor with full details including location
     */
    public function getDoctorWithDetails($id) {
        $sql = "SELECT d.*, s.name as specialization_name, s.icon as specialization_icon,
                       u.name, u.email, u.phone, u.city, u.avatar, u.address, u.created_at,
                       u.latitude, u.longitude,
                       COUNT(DISTINCT a.id) as total_appointments,
                       COUNT(DISTINCT r.id) as total_reviews,
                       COALESCE(AVG(r.rating), 0) as rating
                FROM {$this->table} d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN appointments a ON d.id = a.doctor_id AND a.status = 'completed'
                LEFT JOIN reviews r ON d.id = r.doctor_id
                WHERE d.id = :id
                GROUP BY d.id";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Find doctors within radius with enhanced filters
     */
    public function findDoctorsWithinRadius($latitude, $longitude, $radius = 10, $filters = []) {
        $conditions = ["d.status = 'approved'", "u.status = 'active'", "u.latitude IS NOT NULL", "u.longitude IS NOT NULL"];
        $params = [
            'lat' => $latitude,
            'lng' => $longitude,
            'radius' => $radius
        ];

        // Add filters
        if (!empty($filters['specialization'])) {
            $conditions[] = 'd.specialization_id = :specialization';
            $params['specialization'] = $filters['specialization'];
        }

        if (!empty($filters['rating'])) {
            $conditions[] = 'COALESCE(AVG(r.rating), 0) >= :min_rating';
            $params['min_rating'] = $filters['rating'];
        }

        if (!empty($filters['max_fee'])) {
            $conditions[] = 'd.consultation_fee <= :max_fee';
            $params['max_fee'] = $filters['max_fee'];
        }

        if (!empty($filters['gender'])) {
            $conditions[] = 'u.gender = :gender';
            $params['gender'] = $filters['gender'];
        }

        $whereClause = implode(' AND ', $conditions);

        // Use Haversine formula to calculate distance
        $sql = "SELECT d.*, s.name as specialization_name, s.icon as specialization_icon,
                       u.name, u.email, u.phone, u.city, u.avatar, u.address,
                       u.latitude, u.longitude,
                       d.clinic_address, d.clinic_phone,
                       COUNT(DISTINCT a.id) as total_appointments,
                       COUNT(DISTINCT r.id) as total_reviews,
                       COALESCE(AVG(r.rating), 0) as rating,
                       (6371 * acos(cos(radians(:lat)) * cos(radians(u.latitude)) *
                        cos(radians(u.longitude) - radians(:lng)) +
                        sin(radians(:lat)) * sin(radians(u.latitude)))) AS distance
                FROM {$this->table} d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN appointments a ON d.id = a.doctor_id AND a.status = 'completed'
                LEFT JOIN reviews r ON d.id = r.doctor_id
                WHERE {$whereClause}
                GROUP BY d.id
                HAVING distance <= :radius
                ORDER BY distance ASC, rating DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Update doctor location
     */
    public function updateLocation($doctorId, $latitude, $longitude, $address = '') {
        // Update doctor's clinic address if provided
        if ($address) {
            $this->update($doctorId, ['clinic_address' => $address]);
        }

        // Update user's location
        $doctor = $this->find($doctorId);
        if ($doctor) {
            $userSql = "UPDATE users SET latitude = :latitude, longitude = :longitude WHERE id = :user_id";
            return $this->db->query($userSql, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'user_id' => $doctor['user_id']
            ]);
        }

        return false;
    }
}
