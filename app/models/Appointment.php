<?php
/**
 * Appointment Model
 */

require_once APP_PATH . '/core/Model.php';

class Appointment extends Model {
    protected $table = 'appointments';
    protected $fillable = [
        'patient_id', 'doctor_id', 'appointment_date', 'appointment_time',
        'duration', 'status', 'type', 'symptoms', 'notes', 'doctor_notes',
        'prescription', 'fee', 'payment_status', 'cancellation_reason',
        'cancelled_by'
    ];

    /**
     * Get appointment with full details
     */
    public function getAppointmentDetails($appointmentId) {
        $sql = "SELECT a.*,
                       p.name as patient_name, p.email as patient_email, p.phone as patient_phone,
                       pat.gender, pat.date_of_birth, pat.blood_type,
                       d.name as doctor_name, d.email as doctor_email, d.phone as doctor_phone,
                       doc.consultation_fee, doc.clinic_name, doc.clinic_address,
                       s.name as specialization_name
                FROM {$this->table} a
                LEFT JOIN patients pt ON a.patient_id = pt.id
                LEFT JOIN users p ON pt.user_id = p.id
                LEFT JOIN patients pat ON pt.id = pat.id
                LEFT JOIN doctors doc ON a.doctor_id = doc.id
                LEFT JOIN users d ON doc.user_id = d.id
                LEFT JOIN specializations s ON doc.specialization_id = s.id
                WHERE a.id = :id";

        return $this->db->fetch($sql, ['id' => $appointmentId]);
    }

    /**
     * Get patient appointments
     */
    public function getPatientAppointments($patientId, $status = null, $page = 1) {
        $conditions = ['a.patient_id = :patient_id'];
        $params = ['patient_id' => $patientId];

        if ($status) {
            $conditions[] = 'a.status = :status';
            $params['status'] = $status;
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT a.*,
                       d.name as doctor_name, d.avatar as doctor_avatar,
                       doc.clinic_name, doc.clinic_address,
                       s.name as specialization_name
                FROM {$this->table} a
                LEFT JOIN doctors doc ON a.doctor_id = doc.id
                LEFT JOIN users d ON doc.user_id = d.id
                LEFT JOIN specializations s ON doc.specialization_id = s.id
                WHERE {$whereClause}
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        // Get total count (use alias-aware query)
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} a WHERE {$whereClause}";
        $totalRow = $this->db->fetch($countSql, $params);
        $total = (int)($totalRow['count'] ?? 0);

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
     * Get doctor appointments
     */
    public function getDoctorAppointments($doctorId, $status = null, $date = null, $page = 1) {
        $conditions = ['a.doctor_id = :doctor_id'];
        $params = ['doctor_id' => $doctorId];

        if ($status) {
            $conditions[] = 'a.status = :status';
            $params['status'] = $status;
        }

        if ($date) {
            $conditions[] = 'a.appointment_date = :date';
            $params['date'] = $date;
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT a.*,
                       p.name as patient_name, p.avatar as patient_avatar,
                       pat.gender, pat.date_of_birth, pat.blood_type, pat.medical_history
                FROM {$this->table} a
                LEFT JOIN patients pt ON a.patient_id = pt.id
                LEFT JOIN users p ON pt.user_id = p.id
                LEFT JOIN patients pat ON pt.id = pat.id
                WHERE {$whereClause}
                ORDER BY a.appointment_date ASC, a.appointment_time ASC";

        // Get total count (use alias-aware query)
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} a WHERE {$whereClause}";
        $totalRow = $this->db->fetch($countSql, $params);
        $total = (int)($totalRow['count'] ?? 0);

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
     * Get all appointments for admin
     */
    public function getAllAppointments($status = null, $date = null, $page = 1) {
        $conditions = ['1=1'];
        $params = [];

        if ($status) {
            $conditions[] = 'a.status = :status';
            $params['status'] = $status;
        }

        if ($date) {
            $conditions[] = 'a.appointment_date = :date';
            $params['date'] = $date;
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT a.*,
                       p.name as patient_name, p.email as patient_email,
                       d.name as doctor_name, d.email as doctor_email,
                       s.name as specialization_name
                FROM {$this->table} a
                LEFT JOIN patients pt ON a.patient_id = pt.id
                LEFT JOIN users p ON pt.user_id = p.id
                LEFT JOIN doctors doc ON a.doctor_id = doc.id
                LEFT JOIN users d ON doc.user_id = d.id
                LEFT JOIN specializations s ON doc.specialization_id = s.id
                WHERE {$whereClause}
                ORDER BY a.created_at DESC";

        // Get total count (use alias-aware query)
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} a WHERE {$whereClause}";
        $totalRow = $this->db->fetch($countSql, $params);
        $total = (int)($totalRow['count'] ?? 0);

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
     * Create new appointment
     */
    public function createAppointment($data) {
        // Check if the time slot is available
        if (!$this->isTimeSlotAvailable($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            return false;
        }

        // Set default values
        $data['status'] = APPOINTMENT_PENDING;
        $data['duration'] = $data['duration'] ?? 30;
        $data['type'] = $data['type'] ?? 'consultation';

        return $this->create($data);
    }

    /**
     * Check if time slot is available
     */
    public function isTimeSlotAvailable($doctorId, $date, $time) {
        $count = $this->count(
            'doctor_id = :doctor_id AND appointment_date = :date AND appointment_time = :time AND status IN (:status1, :status2)',
            [
                'doctor_id' => $doctorId,
                'date' => $date,
                'time' => $time,
                'status1' => APPOINTMENT_PENDING,
                'status2' => APPOINTMENT_CONFIRMED
            ]
        );

        return $count === 0;
    }

    /**
     * Update appointment status
     */
    public function updateStatus($appointmentId, $status, $notes = null) {
        $data = ['status' => $status];

        switch ($status) {
            case APPOINTMENT_CONFIRMED:
                $data['confirmed_at'] = date('Y-m-d H:i:s');
                break;

            case APPOINTMENT_COMPLETED:
                $data['completed_at'] = date('Y-m-d H:i:s');
                if ($notes) {
                    $data['doctor_notes'] = $notes;
                }
                break;

            case APPOINTMENT_CANCELLED:
                $data['cancelled_at'] = date('Y-m-d H:i:s');
                if ($notes) {
                    $data['cancellation_reason'] = $notes;
                }
                break;
        }

        return $this->update($appointmentId, $data);
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment($appointmentId, $reason, $cancelledBy) {
        return $this->update($appointmentId, [
            'status' => APPOINTMENT_CANCELLED,
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get appointment statistics
     */
    public function getStatistics($startDate = null, $endDate = null) {
        $conditions = ['1=1'];
        $params = [];

        if ($startDate && $endDate) {
            $conditions[] = 'appointment_date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }

        $whereClause = implode(' AND ', $conditions);

        $stats = [];

        // Total appointments
        $stats['total_appointments'] = $this->count($whereClause, $params);

        // By status
        $stats['pending_appointments'] = $this->count($whereClause . ' AND status = :status', array_merge($params, ['status' => APPOINTMENT_PENDING]));
        $stats['confirmed_appointments'] = $this->count($whereClause . ' AND status = :status', array_merge($params, ['status' => APPOINTMENT_CONFIRMED]));
        $stats['completed_appointments'] = $this->count($whereClause . ' AND status = :status', array_merge($params, ['status' => APPOINTMENT_COMPLETED]));
        $stats['cancelled_appointments'] = $this->count($whereClause . ' AND status = :status', array_merge($params, ['status' => APPOINTMENT_CANCELLED]));

        // Today's appointments
        $stats['today_appointments'] = $this->count('appointment_date = :today', ['today' => date('Y-m-d')]);

        // This week's appointments
        $stats['week_appointments'] = $this->count(
            'appointment_date BETWEEN :start AND :end',
            [
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => date('Y-m-d', strtotime('sunday this week'))
            ]
        );

        // Revenue (completed appointments only)
        $revenueSql = "SELECT SUM(fee) as total_revenue FROM {$this->table} WHERE status = :status";
        $revenueParams = ['status' => APPOINTMENT_COMPLETED];

        if ($startDate && $endDate) {
            $revenueSql .= " AND appointment_date BETWEEN :start_date AND :end_date";
            $revenueParams = array_merge($revenueParams, $params);
        }

        $revenue = $this->db->fetch($revenueSql, $revenueParams);
        $stats['total_revenue'] = $revenue['total_revenue'] ?? 0;

        return $stats;
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcomingAppointments($limit = 10) {
        $sql = "SELECT a.*,
                       p.name as patient_name,
                       d.name as doctor_name,
                       s.name as specialization_name
                FROM {$this->table} a
                LEFT JOIN patients pt ON a.patient_id = pt.id
                LEFT JOIN users p ON pt.user_id = p.id
                LEFT JOIN doctors doc ON a.doctor_id = doc.id
                LEFT JOIN users d ON doc.user_id = d.id
                LEFT JOIN specializations s ON doc.specialization_id = s.id
                WHERE a.appointment_date >= CURDATE()
                AND a.status IN (:status1, :status2)
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'status1' => APPOINTMENT_PENDING,
            'status2' => APPOINTMENT_CONFIRMED,
            'limit' => $limit
        ]);
    }

    /**
     * Get appointments by date range
     */
    public function getAppointmentsByDateRange($startDate, $endDate, $doctorId = null, $patientId = null) {
        $conditions = ['appointment_date BETWEEN :start_date AND :end_date'];
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($doctorId) {
            $conditions[] = 'doctor_id = :doctor_id';
            $params['doctor_id'] = $doctorId;
        }

        if ($patientId) {
            $conditions[] = 'patient_id = :patient_id';
            $params['patient_id'] = $patientId;
        }

        $whereClause = implode(' AND ', $conditions);

        return $this->where($whereClause, $params, 'appointment_date ASC, appointment_time ASC');
    }
    /**
     * Get upcoming appointments for a specific patient
     */
    public function getPatientUpcomingAppointments($patientId, $limit = 5) {
        $sql = "SELECT a.*,
                       d.name as doctor_name, d.avatar as doctor_avatar,
                       doc.clinic_name, doc.clinic_address,
                       s.name as specialization_name
                FROM {$this->table} a
                LEFT JOIN doctors doc ON a.doctor_id = doc.id
                LEFT JOIN users d ON doc.user_id = d.id
                LEFT JOIN specializations s ON doc.specialization_id = s.id
                WHERE a.patient_id = :patient_id
                  AND a.appointment_date >= CURDATE()
                  AND a.status IN (:status1, :status2)
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'patient_id' => $patientId,
            'status1' => APPOINTMENT_PENDING,
            'status2' => APPOINTMENT_CONFIRMED,
            'limit' => (int)$limit
        ]);
    }

    /**
     * Get recent appointment history for a specific patient
     */
    public function getPatientAppointmentHistory($patientId, $limit = 5) {
        $sql = "SELECT a.*,
                       d.name as doctor_name, d.avatar as doctor_avatar,
                       doc.clinic_name, doc.clinic_address,
                       s.name as specialization_name
                FROM {$this->table} a
                LEFT JOIN doctors doc ON a.doctor_id = doc.id
                LEFT JOIN users d ON doc.user_id = d.id
                LEFT JOIN specializations s ON doc.specialization_id = s.id
                WHERE a.patient_id = :patient_id
                  AND (
                        a.appointment_date < CURDATE()
                        OR a.status IN (:status1, :status2)
                  )
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'patient_id' => $patientId,
            'status1' => APPOINTMENT_COMPLETED,
            'status2' => APPOINTMENT_CANCELLED,
            'limit' => (int)$limit
        ]);
    }

    /**
     * Check if the patient already has an appointment at the same time
     */
    public function findExistingAppointment($patientId, $date, $time) {
        $sql = "SELECT a.*
                FROM {$this->table} a
                WHERE a.patient_id = :patient_id
                  AND a.appointment_date = :date
                  AND a.appointment_time = :time
                  AND a.status IN (:status1, :status2)
                LIMIT 1";

        return $this->db->fetch($sql, [
            'patient_id' => $patientId,
            'date' => $date,
            'time' => $time,
            'status1' => APPOINTMENT_PENDING,
            'status2' => APPOINTMENT_CONFIRMED
        ]);
    }

    /**
     * Get booked slots for a doctor on a specific date
     */
    public function getBookedSlots($doctorId, $date) {
        $sql = "SELECT appointment_time
                FROM {$this->table}
                WHERE doctor_id = :doctor_id
                  AND appointment_date = :date
                  AND status IN (:status1, :status2)";

        return $this->db->fetchAll($sql, [
            'doctor_id' => $doctorId,
            'date' => $date,
            'status1' => APPOINTMENT_PENDING,
            'status2' => APPOINTMENT_CONFIRMED
        ]);
    }

}
