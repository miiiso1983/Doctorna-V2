<?php
/**
 * Patient Controller
 * Handles patient dashboard and appointment booking functions
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Patient.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Specialization.php';
require_once APP_PATH . '/models/Notification.php';

class PatientController extends Controller {
    private $userModel;
    private $doctorModel;
    private $patientModel;
    private $appointmentModel;
    private $specializationModel;
    private $patientProfile;

    public function __construct() {
        parent::__construct();

        // Require patient role
        $this->requireRole(ROLE_PATIENT);

        $this->userModel = new User();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
        $this->specializationModel = new Specialization();

        // Get patient profile
        $this->patientProfile = $this->patientModel->getByUserId($this->auth->id());

        if (!$this->patientProfile) {
            // Create patient profile if doesn't exist
            $patientData = [
                'user_id' => $this->auth->id(),
                'date_of_birth' => null,
                'gender' => null,
                'blood_type' => null,
                'emergency_contact' => null,
                'medical_history' => null,
                'allergies' => null,
                'current_medications' => null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $patientId = $this->patientModel->create($patientData);
            $this->patientProfile = $this->patientModel->find($patientId);
        }
    }

    /**
     * Patient dashboard
     */
    public function index() {
        // Get upcoming appointments
        $upcomingAppointments = $this->appointmentModel->getPatientUpcomingAppointments($this->patientProfile['id']);

        // Get appointment history
        $appointmentHistory = $this->appointmentModel->getPatientAppointmentHistory($this->patientProfile['id'], 5);

        // Get appointment statistics
        $appointmentStats = $this->getAppointmentStatistics();

        // Get recommended doctors based on previous appointments
        $recommendedDoctors = $this->getRecommendedDoctors();

        $data = [
            'title' => 'لوحة المريض',
            'patient' => $this->patientProfile,
            'upcoming_appointments' => $upcomingAppointments,
            'appointment_history' => $appointmentHistory,
            'appointment_stats' => $appointmentStats,
            'recommended_doctors' => $recommendedDoctors
        ];

        $this->renderWithLayout('patient.dashboard', $data, 'patient');
    }

    /**
     * Search doctors
     */
    public function searchDoctors() {
        $search = $this->get('search', '');
        $specialization = $this->get('specialization', '');
        $city = $this->get('city', '');
        $gender = $this->get('gender', ''); // موقوف مؤقتًا لعدم وجود عمود gender في users
        $rating = $this->get('rating', '');
        $maxFee = $this->get('max_fee', '');
        $page = (int)$this->get('page', 1);

        // Optional distance filter
        $lat = $this->get('lat', '');
        $lng = $this->get('lng', '');
        $radius = (float)$this->get('radius', ''); // KM

        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        // Distance-based search if coordinates provided
        if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng) && $radius > 0) {
            $filters = [];
            if ($specialization) $filters['specialization'] = $specialization;
            if ($rating) $filters['rating'] = $rating;
            // gender موقوف لعدم وجود العمود في users
            if ($maxFee) $filters['max_fee'] = $maxFee;

            // Fetch all within radius then paginate in memory
            $all = $this->doctorModel->findDoctorsWithinRadius((float)$lat, (float)$lng, $radius, $filters);
            $total = count($all);
            $results = array_slice($all, $offset, $perPage);

            $doctors = [
                'data' => $results,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $total ? ($offset + 1) : 0,
                'to' => min($offset + $perPage, $total)
            ];
        } else {
            // Build search conditions
            $conditions = ['d.status = :status'];
            $params = ['status' => 'approved'];

            if ($search) {
                $conditions[] = "(u.name LIKE :search OR d.biography LIKE :search OR d.clinic_name LIKE :search)";
                $params['search'] = "%{$search}%";
            }
            if ($specialization) {
                $conditions[] = "d.specialization_id = :specialization";
                $params['specialization'] = $specialization;
            }
            if ($city) {
                $conditions[] = "u.city = :city";
                $params['city'] = $city;
            }
            // gender موقوف لعدم وجود العمود في users
            if ($rating) {
                $conditions[] = "d.rating >= :rating";
                $params['rating'] = $rating;
            }
            if ($maxFee) {
                $conditions[] = "d.consultation_fee <= :max_fee";
                $params['max_fee'] = $maxFee;
            }

            $whereClause = implode(' AND ', $conditions);

            // Query
            $sql = "SELECT d.*, s.name as specialization_name, s.icon as specialization_icon,
                           u.name, u.email, u.phone, u.city, u.avatar
                    FROM doctors d
                    LEFT JOIN specializations s ON d.specialization_id = s.id
                    LEFT JOIN users u ON d.user_id = u.id
                    WHERE {$whereClause}
                    ORDER BY d.rating DESC, d.total_reviews DESC";

            $countSql = "SELECT COUNT(*) as count
                         FROM doctors d
                         LEFT JOIN users u ON d.user_id = u.id
                         WHERE {$whereClause}";

            $countRows = $this->doctorModel->fetchRaw($countSql, $params);
            $total = (int)($countRows[0]['count'] ?? 0);

            $sql .= " LIMIT {$perPage} OFFSET {$offset}";
            $results = $this->doctorModel->fetchRaw($sql, $params);

            $doctors = [
                'data' => $results,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $total ? ($offset + 1) : 0,
                'to' => min($offset + $perPage, $total)
            ];
        }

        // Get filter options
        $specializations = $this->specializationModel->getActiveSpecializations();
        $cities = $this->userModel->getCities();

        $data = [
            'title' => 'البحث عن الأطباء',
            'patient' => $this->patientProfile,
            'doctors' => $doctors,
            'specializations' => $specializations,
            'cities' => $cities,
            'filters' => [
                'search' => $search,
                'specialization' => $specialization,
                'city' => $city,
                'gender' => $gender,
                'rating' => $rating,
                'max_fee' => $maxFee,
                'lat' => $lat,
                'lng' => $lng,
                'radius' => $radius
            ]
        ];

        $this->renderWithLayout('patient.search-doctors', $data, 'patient');
    }

    /**
     * View doctor profile
     */
    public function viewDoctor() {
        $doctorId = $this->get('id');

        if (!$doctorId) {
            $this->flash('error', 'معرف الطبيب مطلوب');
            $this->redirect('/patient/search-doctors');
        }

        $doctor = $this->doctorModel->getDoctorWithDetails($doctorId);

        if (!$doctor || $doctor['status'] !== 'approved') {
            $this->flash('error', 'الطبيب غير موجود أو غير متاح');
            $this->redirect('/patient/search-doctors');
        }

        // Get doctor's schedule
        $schedule = $this->doctorModel->getSchedule($doctorId);

        // Get doctor's reviews
        $reviews = $this->doctorModel->getReviews($doctorId);

        // Get available time slots for next 30 days
        $availableSlots = $this->getAvailableTimeSlots($doctorId);

        $data = [
            'title' => 'ملف الطبيب - ' . $doctor['name'],
            'patient' => $this->patientProfile,
            'doctor' => $doctor,
            'schedule' => $schedule,
            'reviews' => $reviews,
            'available_slots' => $availableSlots,
            'csrf_token' => $this->csrf->token()
        ];

        $this->renderWithLayout('patient.doctor-profile', $data, 'patient');
    }

    /**
     * Book appointment
     */
    public function bookAppointment() {
        if (!$this->isPost()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $data = $this->post();

        // Validate input
        $errors = $this->validate($data, [
            'doctor_id' => 'required|numeric',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'symptoms' => 'required|min:10'
        ]);

        if (!empty($errors)) {
            $this->error('يرجى تصحيح الأخطاء والمحاولة مرة أخرى');
        }

        // Check if doctor exists and is available
        $doctor = $this->doctorModel->find($data['doctor_id']);
        if (!$doctor || $doctor['status'] !== 'approved') {
            $this->error('الطبيب غير متاح');
        }

        // Check if time slot is available
        if (!$this->isTimeSlotAvailable($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            $this->error('الموعد المحدد غير متاح');
        }

        // Check for existing appointment at same time
        $existingAppointment = $this->appointmentModel->findExistingAppointment(
            $this->patientProfile['id'],
            $data['appointment_date'],
            $data['appointment_time']
        );

        if ($existingAppointment) {
            $this->error('لديك موعد آخر في نفس الوقت');
        }

        try {
            $appointmentData = [
                'patient_id' => $this->patientProfile['id'],
                'doctor_id' => $data['doctor_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'symptoms' => $data['symptoms'],
                'notes' => $data['notes'] ?? '',
                'status' => APPOINTMENT_PENDING,
                'fee' => $doctor['consultation_fee'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $appointmentId = $this->appointmentModel->create($appointmentData);

            // Send notification to doctor (implement notification system)

            $this->success('تم حجز الموعد بنجاح. سيتم إشعارك عند موافقة الطبيب.');

        } catch (Exception $e) {
            $this->error('حدث خطأ أثناء حجز الموعد');
        }
    }

    /**
     * My appointments
     */
    public function appointments() {
        $page = $this->get('page', 1);
        $status = $this->get('status', '');

        $appointments = $this->appointmentModel->getPatientAppointments(
            $this->patientProfile['id'],
            $status,
            $page
        );

        $data = [
            'title' => 'مواعيدي',
            'patient' => $this->patientProfile,
            'appointments' => $appointments,
            'status' => $status,
            'statuses' => [
                'pending' => 'في الانتظار',
                'confirmed' => 'مؤكد',
                'completed' => 'مكتمل',
                'cancelled' => 'ملغي'
            ]
        ];

        $this->renderWithLayout('patient.appointments', $data, 'patient');
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $appointmentId = $this->post('appointment_id');
        $reason = $this->post('reason', '');

        // Verify appointment belongs to this patient
        $appointment = $this->appointmentModel->getAppointmentDetails($appointmentId);

        if (!$appointment || $appointment['patient_id'] != $this->patientProfile['id']) {
            $this->error('الموعد غير موجود أو غير مخول لك');
        }

        if ($appointment['status'] === APPOINTMENT_COMPLETED) {
            $this->error('لا يمكن إلغاء موعد مكتمل');
        }

        if ($this->appointmentModel->cancelAppointment($appointmentId, $reason, 'patient')) {
            $this->success('تم إلغاء الموعد بنجاح');
        } else {
            $this->error('حدث خطأ أثناء إلغاء الموعد');
        }
    }

    /**
     * Patient profile
     */
    public function profile() {
        $data = [
            'title' => 'الملف الشخصي',
            'patient' => $this->patientProfile,
            'csrf_token' => $this->csrf->token()
        ];

        $this->renderWithLayout('patient.profile', $data, 'patient');
    }

    /**
     * Update patient profile
     */
    public function updateProfile() {
        if (!$this->isPost()) {
            $this->redirect('/patient/profile');
        }

        $this->validateCSRF();

        $data = $this->post();

        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'phone' => 'required|phone'
        ]);

        // Check if email already exists (excluding current user)
        if ($this->userModel->emailExistsExcept($data['email'], $this->auth->id())) {
            $errors['email'][] = 'البريد الإلكتروني مستخدم بالفعل';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/patient/profile');
        }

        try {
            $this->userModel->beginTransaction();

            // Update user data
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? '',
                'city' => $data['city'] ?? '',
                'gender' => $data['gender'] ?? null
            ];

            $this->userModel->update($this->auth->id(), $userData);

            // Update patient data
            $patientData = [
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'blood_type' => $data['blood_type'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? '',
                'medical_history' => $data['medical_history'] ?? '',
                'allergies' => $data['allergies'] ?? '',
                'current_medications' => $data['current_medications'] ?? ''
            ];

            $this->patientModel->update($this->patientProfile['id'], $patientData);

            // Handle avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $avatar = $this->uploadFile($_FILES['avatar'], 'uploads/profiles', ['jpg', 'jpeg', 'png']);
                if ($avatar) {
                    $this->userModel->update($this->auth->id(), ['avatar' => $avatar]);
                }
            }

            $this->userModel->commit();

            $this->flash('success', 'تم تحديث الملف الشخصي بنجاح');
            $this->redirect('/patient/profile');

        } catch (Exception $e) {
            $this->userModel->rollback();
            $this->flash('error', 'حدث خطأ أثناء تحديث الملف الشخصي');
            $this->redirect('/patient/profile');
        }
    }

    /**
     * Get appointment statistics
     */
    private function getAppointmentStatistics() {
        $patientId = $this->patientProfile['id'];

        return [
            'total' => $this->appointmentModel->count('patient_id = :patient_id', ['patient_id' => $patientId]),
            'pending' => $this->appointmentModel->count('patient_id = :patient_id AND status = :status', [
                'patient_id' => $patientId, 'status' => APPOINTMENT_PENDING
            ]),
            'confirmed' => $this->appointmentModel->count('patient_id = :patient_id AND status = :status', [
                'patient_id' => $patientId, 'status' => APPOINTMENT_CONFIRMED
            ]),
            'completed' => $this->appointmentModel->count('patient_id = :patient_id AND status = :status', [
                'patient_id' => $patientId, 'status' => APPOINTMENT_COMPLETED
            ]),
            'upcoming' => $this->appointmentModel->count(
                'patient_id = :patient_id AND appointment_date >= CURDATE() AND status IN (:status1, :status2)',
                [
                    'patient_id' => $patientId,
                    'status1' => APPOINTMENT_PENDING,
                    'status2' => APPOINTMENT_CONFIRMED
                ]
            )
        ];
    }

    /**
     * Get recommended doctors
     */
    private function getRecommendedDoctors() {
        // Get doctors from previous appointments or highly rated doctors
        $sql = "SELECT DISTINCT d.*, s.name as specialization_name,
                       u.name, u.avatar, u.city
                FROM doctors d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.status = 'approved' AND d.rating >= 4.0
                ORDER BY d.rating DESC, d.total_reviews DESC
                LIMIT 6";

        return $this->doctorModel->fetchRaw($sql);
    }

    /**
     * Get available time slots for a doctor
     */
    private function getAvailableTimeSlots($doctorId) {
        $slots = [];
        $schedule = $this->doctorModel->getSchedule($doctorId);

        // Generate slots for next 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $dayOfWeek = date('w', strtotime($date));

            if (isset($schedule[$dayOfWeek]) && $schedule[$dayOfWeek]['is_available']) {
                $daySchedule = $schedule[$dayOfWeek];
                $timeSlots = $this->generateTimeSlots(
                    $daySchedule['start_time'],
                    $daySchedule['end_time'],
                    $daySchedule['break_start'] ?? null,
                    $daySchedule['break_end'] ?? null
                );

                // Filter out booked slots
                $availableSlots = $this->filterBookedSlots($doctorId, $date, $timeSlots);

                if (!empty($availableSlots)) {
                    $slots[$date] = $availableSlots;
                }
            }
        }

        return $slots;
    }

    /**
     * Generate time slots
     */
    private function generateTimeSlots($startTime, $endTime, $breakStart = null, $breakEnd = null) {
        $slots = [];
        $current = strtotime($startTime);
        $end = strtotime($endTime);
        $breakStartTime = $breakStart ? strtotime($breakStart) : null;
        $breakEndTime = $breakEnd ? strtotime($breakEnd) : null;

        while ($current < $end) {
            $timeSlot = date('H:i', $current);

            // Skip break time
            if ($breakStartTime && $breakEndTime &&
                $current >= $breakStartTime && $current < $breakEndTime) {
                $current = strtotime('+30 minutes', $current);
                continue;
            }

            $slots[] = $timeSlot;

            $current = strtotime('+30 minutes', $current);
        }

        return $slots;
    }

    /**
     * Filter out booked time slots
     */
    private function filterBookedSlots($doctorId, $date, $timeSlots) {
        $bookedSlots = $this->appointmentModel->getBookedSlots($doctorId, $date);
        $bookedTimes = array_column($bookedSlots, 'appointment_time');

        return array_filter($timeSlots, function($slot) use ($bookedTimes) {
            return !in_array($slot, $bookedTimes);
        });
    }

    /**
     * Medical history page
     */
    public function medicalHistory() {
        $patientId = $this->patientProfile['id'];
        $history = $this->patientModel->getMedicalHistory($patientId);

        $data = [
            'title' => 'التاريخ الطبي',
            'patient' => $this->patientProfile,
            'history' => $history,
        ];
        $this->renderWithLayout('patient.medical-history', $data, 'patient');
    }

    /**
     * Check if time slot is available
     */
    private function isTimeSlotAvailable($doctorId, $date, $time) {
        $availableSlots = $this->getAvailableTimeSlots($doctorId);
        return isset($availableSlots[$date]) && in_array($time, $availableSlots[$date]);
    }

    /**
     * Prescriptions list for patient
     */
    public function prescriptions() {
        $patientId = $this->patientProfile['id'];
        // Fetch prescriptions from completed appointments that have prescription text
        $rows = $this->appointmentModel->fetchRaw(
            "SELECT a.id, a.appointment_date, a.appointment_time, a.prescription, a.doctor_notes,
                    u.name AS doctor_name, u.avatar AS doctor_avatar, s.name AS specialization_name
             FROM appointments a
             LEFT JOIN doctors d ON a.doctor_id = d.id
             LEFT JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             WHERE a.patient_id = :p AND a.status = :status AND a.prescription IS NOT NULL AND a.prescription != ''
             ORDER BY a.appointment_date DESC, a.appointment_time DESC",
            ['p' => $patientId, 'status' => APPOINTMENT_COMPLETED]
        );

        $data = [
            'title' => 'الوصفات الطبية',
            'patient' => $this->patientProfile,
            'prescriptions' => $rows,
        ];
        $this->renderWithLayout('patient.prescriptions', $data, 'patient');
    }


    /**
     * Print prescription as a print-friendly page (user can save as PDF from browser)
     */
    public function printPrescription($id) {
        $appointmentId = (int)$id;
        $patientId = $this->patientProfile['id'];
        $row = $this->appointmentModel->fetchRaw(
            "SELECT a.*, u.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             LEFT JOIN doctors d ON a.doctor_id = d.id
             LEFT JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             WHERE a.id = :id AND a.patient_id = :p AND a.status = :status",
            ['id' => $appointmentId, 'p' => $patientId, 'status' => APPOINTMENT_COMPLETED]
        );
        if (!$row) {
            http_response_code(404);
            echo 'Prescription not found';
            return;
        }
        $this->renderWithLayout('patient.prescription-print', [
            'title' => 'طباعة وصفة طبية',
            'appointment' => $row,
            'patient' => $this->patientProfile,
        ], 'patient');
    }

    /**
     * Request prescription refill (sends notification to doctor)
     */
    public function requestRefill($id) {
        $this->validateCSRF();
        $appointmentId = (int)$id;
        $patientId = $this->patientProfile['id'];
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment || (int)$appointment['patient_id'] !== $patientId) {
            return $this->error('الوصفة غير موجودة');
        }
        // notify doctor
        $doctor = $this->doctorModel->find((int)$appointment['doctor_id']);
        if ($doctor) {
            $userModel = new User();
            $docUser = $userModel->find((int)$doctor['user_id']);
            if ($docUser) {
                $notif = new Notification();
                $notif->createNotification(
                    (int)$docUser['id'],
                    'prescription_refill',
                    'طلب إعادة صرف وصفة',
                    'لديك طلب إعادة صرف لوصفة مرتبطة بالموعد #' . $appointmentId,
                    ['appointment_id' => $appointmentId, 'patient_id' => $patientId]
                );
            }
        }
        return $this->success('تم إرسال طلب إعادة الصرف للطبيب');
    }

    /**
     * Emergency info page
     */
    public function emergency() {
        $data = [
            'title' => 'معلومات الطوارئ',
            'patient' => $this->patientProfile,
        ];
        $this->renderWithLayout('patient.emergency', $data, 'patient');
    }

    /**
     * Update emergency info
     */
    public function updateEmergency() {
        if (!$this->isPost()) {
            return $this->redirect('/patient/emergency');
        }
        $this->validateCSRF();
        $data = $this->post();
        $fields = [
            'emergency_contact' => $data['emergency_contact'] ?? '',
            'emergency_contact_name' => $data['emergency_contact_name'] ?? '',
            'blood_type' => $data['blood_type'] ?? null,
            'allergies' => $data['allergies'] ?? '',
            'current_medications' => $data['current_medications'] ?? '',
        ];
        $this->patientModel->update($this->patientProfile['id'], $fields);
        $_SESSION['flash']['success'] = 'تم تحديث بيانات الطوارئ';
        return $this->redirect('/patient/emergency');
    }

}
