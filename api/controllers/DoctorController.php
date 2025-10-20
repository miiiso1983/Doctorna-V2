<?php
namespace API;

/**
 * Doctor Controller
 */
class DoctorController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * List doctors with filters
     */
    public function list() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : API_PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;
        
        $where = ["d.status = 'approved'", "u.status = 'active'"];
        $params = [];
        
        // Filters
        if (isset($_GET['specialization_id'])) {
            $where[] = "d.specialization_id = ?";
            $params[] = $_GET['specialization_id'];
        }
        
        if (isset($_GET['city'])) {
            $where[] = "u.city = ?";
            $params[] = $_GET['city'];
        }
        
        if (isset($_GET['min_rating'])) {
            $where[] = "d.rating >= ?";
            $params[] = $_GET['min_rating'];
        }
        
        // Search by name
        if (isset($_GET['search'])) {
            $where[] = "(u.name LIKE ? OR d.biography LIKE ?)";
            $searchTerm = '%' . $_GET['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM doctors d
            INNER JOIN users u ON d.user_id = u.id
            WHERE $whereClause
        ");
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        // Get doctors
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                u.latitude, u.longitude,
                s.name as specialization_name, s.name_en as specialization_name_en,
                s.icon as specialization_icon, s.color as specialization_color
            FROM doctors d
            INNER JOIN users u ON d.user_id = u.id
            LEFT JOIN specializations s ON d.specialization_id = s.id
            WHERE $whereClause
            ORDER BY d.rating DESC, d.total_reviews DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $doctors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        \Response::paginated($doctors, $total, $page, $limit);
    }
    
    /**
     * Search doctors
     */
    public function search() {
        $this->list(); // Reuse list method with search parameter
    }
    
    /**
     * Get doctor details
     */
    public function details($id) {
        if (!$id) {
            \Response::error('معرف الطبيب مطلوب', 400);
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                u.name, u.email, u.phone, u.avatar, u.address, u.city, u.country,
                u.latitude, u.longitude, u.created_at as user_created_at,
                s.name as specialization_name, s.name_en as specialization_name_en,
                s.icon as specialization_icon, s.color as specialization_color
            FROM doctors d
            INNER JOIN users u ON d.user_id = u.id
            LEFT JOIN specializations s ON d.specialization_id = s.id
            WHERE d.id = ? AND d.status = 'approved' AND u.status = 'active'
        ");
        $stmt->execute([$id]);
        $doctor = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$doctor) {
            \Response::notFound('الطبيب غير موجود');
        }
        
        // Get doctor schedule
        $stmt = $this->db->prepare("
            SELECT * FROM doctor_schedules 
            WHERE doctor_id = ? AND is_available = 1
            ORDER BY FIELD(day_of_week, 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
        ");
        $stmt->execute([$id]);
        $schedule = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $doctor['schedule'] = $schedule;
        
        \Response::success($doctor);
    }
    
    /**
     * Get doctor availability
     */
    public function availability($id) {
        if (!$id) {
            \Response::error('معرف الطبيب مطلوب', 400);
        }
        
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Validate date
        $validator = new \Validator(['date' => $date]);
        $validator->date('date');
        
        if ($validator->fails()) {
            \Response::validationError($validator->errors());
        }
        
        // Get day of week
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        // Get doctor schedule for this day
        $stmt = $this->db->prepare("
            SELECT * FROM doctor_schedules 
            WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1
        ");
        $stmt->execute([$id, $dayOfWeek]);
        $schedule = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$schedule) {
            \Response::success([
                'available' => false,
                'message' => 'الطبيب غير متاح في هذا اليوم',
                'slots' => []
            ]);
        }
        
        // Get booked appointments for this date
        $stmt = $this->db->prepare("
            SELECT appointment_time, duration 
            FROM appointments 
            WHERE doctor_id = ? AND appointment_date = ? 
            AND status NOT IN ('cancelled', 'no_show')
        ");
        $stmt->execute([$id, $date]);
        $bookedSlots = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Generate available time slots
        $slots = $this->generateTimeSlots(
            $schedule['start_time'],
            $schedule['end_time'],
            $schedule['break_start'],
            $schedule['break_end'],
            $bookedSlots
        );
        
        \Response::success([
            'available' => !empty($slots),
            'date' => $date,
            'slots' => $slots
        ]);
    }
    
    /**
     * Generate available time slots
     */
    private function generateTimeSlots($startTime, $endTime, $breakStart, $breakEnd, $bookedSlots) {
        $slots = [];
        $slotDuration = 30; // minutes
        
        $current = strtotime($startTime);
        $end = strtotime($endTime);
        
        while ($current < $end) {
            $slotTime = date('H:i:s', $current);
            
            // Skip break time
            if ($breakStart && $breakEnd) {
                if ($slotTime >= $breakStart && $slotTime < $breakEnd) {
                    $current = strtotime('+' . $slotDuration . ' minutes', $current);
                    continue;
                }
            }
            
            // Check if slot is booked
            $isBooked = false;
            foreach ($bookedSlots as $booked) {
                if ($slotTime === $booked['appointment_time']) {
                    $isBooked = true;
                    break;
                }
            }
            
            if (!$isBooked) {
                $slots[] = [
                    'time' => $slotTime,
                    'display_time' => date('h:i A', $current),
                    'available' => true
                ];
            }
            
            $current = strtotime('+' . $slotDuration . ' minutes', $current);
        }
        
        return $slots;
    }
    
    /**
     * Get specializations
     */
    public function specializations() {
        $stmt = $this->db->prepare("
            SELECT * FROM specializations 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, name ASC
        ");
        $stmt->execute();
        $specializations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        \Response::success($specializations);
    }
}

