<?php
/**
 * Doctor Controller
 * Handles doctor dashboard and management functions
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Patient.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Specialization.php';
require_once APP_PATH . '/models/HealthPost.php';

class DoctorController extends Controller {
    private $userModel;
    private $doctorModel;
    private $patientModel;
    private $appointmentModel;
    private $specializationModel;
    private $healthPostModel;
    private $doctorProfile;

    public function __construct() {
        parent::__construct();

        // Require doctor role
        $this->requireRole(ROLE_DOCTOR);

        $this->userModel = new User();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
        $this->specializationModel = new Specialization();
        $this->healthPostModel = new HealthPost();

        // Get doctor profile
        $this->doctorProfile = $this->doctorModel->getByUserId($this->auth->id());

        if (!$this->doctorProfile) {
            $this->flash('error', 'لم يتم العثور على ملف الطبيب');
            $this->redirect('/logout');
        }
    }

    /**
     * Doctor dashboard
     */
    public function index() {
        $today = date('Y-m-d');

        // Get today's appointments (flat list for dashboard)
        $todayAppointments = $this->appointmentModel->fetchRaw(
            "SELECT a.*, p.name as patient_name, p.phone as patient_phone
             FROM appointments a
             LEFT JOIN patients pt ON a.patient_id = pt.id
             LEFT JOIN users p ON pt.user_id = p.id
             WHERE a.doctor_id = :doctor_id
               AND a.appointment_date = :date
             ORDER BY a.appointment_time ASC",
            [
                'doctor_id' => $this->doctorProfile['id'],
                'date' => $today
            ]
        );

        // Get appointment statistics
        $appointmentStats = $this->getAppointmentStatistics();

        // Get recent patient reviews
        $recentReviews = $this->getRecentReviews();

        // Get upcoming appointments (next 7 days)
        $upcomingAppointments = $this->getUpcomingAppointments();

        $data = [
            'title' => 'لوحة الطبيب',
            'doctor' => $this->doctorProfile,
            'today_appointments' => $todayAppointments,
            'appointment_stats' => $appointmentStats,
            'recent_reviews' => $recentReviews,
            'upcoming_appointments' => $upcomingAppointments
        ];

        $this->renderWithLayout('doctor.dashboard', $data, 'doctor');
    }

    /**
     * Manage appointments
     */
    public function appointments() {
        $page = $this->get('page', 1);
        $status = $this->get('status', '');
        $date = $this->get('date', '');

        $appointments = $this->appointmentModel->getDoctorAppointments(
            $this->doctorProfile['id'],
            $status,
            $date,
            $page
        );

        $data = [
            'title' => 'إدارة المواعيد',
            'doctor' => $this->doctorProfile,
            'appointments' => $appointments,
            'status' => $status,
            'date' => $date,
            'statuses' => [
                'pending' => 'في الانتظار',
                'confirmed' => 'مؤكد',
                'completed' => 'مكتمل',
                'cancelled' => 'ملغي'
            ]
        ];

        $this->renderWithLayout('doctor.appointments', $data, 'doctor');
    }

    /**
     * Doctor profile management
     */
    public function profile() {
        $specializations = $this->specializationModel->getActiveSpecializations();

        $data = [
            'title' => 'الملف الشخصي',
            'doctor' => $this->doctorProfile,
            'specializations' => $specializations
        ];

        $this->renderWithLayout('doctor.profile', $data, 'doctor');
    }

    /**
     * Update doctor profile
     */
    public function updateProfile() {
        if (!$this->isPost()) {
            $this->redirect('/doctor/profile');
        }

        $this->validateCSRF();

        $data = $this->post();

        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'specialization_id' => 'required|numeric',
            'experience_years' => 'required|numeric',
            'consultation_fee' => 'required|numeric'
        ]);

        // Check if email already exists (excluding current user)
        if ($this->userModel->emailExistsExcept($data['email'], $this->auth->id())) {
            $errors['email'][] = 'البريد الإلكتروني مستخدم بالفعل';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/doctor/profile');
        }

        try {
            $this->userModel->beginTransaction();

            // Update user data
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? '',
                'city' => $data['city'] ?? ''
            ];

            $this->userModel->update($this->auth->id(), $userData);

            // Update doctor data
            $doctorData = [
                'specialization_id' => $data['specialization_id'],
                'license_number' => $data['license_number'] ?? '',
                'experience_years' => $data['experience_years'],
                'biography' => $data['biography'] ?? '',
                'education' => $data['education'] ?? '',
                'certifications' => $data['certifications'] ?? '',
                'languages' => $data['languages'] ?? '',
                'consultation_fee' => $data['consultation_fee'],
                'clinic_name' => $data['clinic_name'] ?? '',
                'clinic_address' => $data['clinic_address'] ?? '',
                'clinic_phone' => $data['clinic_phone'] ?? ''
            ];

            $this->doctorModel->update($this->doctorProfile['id'], $doctorData);

            // Handle avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $avatar = $this->uploadFile($_FILES['avatar'], 'uploads/profiles', ['jpg', 'jpeg', 'png']);
                if ($avatar) {
                    $this->userModel->update($this->auth->id(), ['avatar' => $avatar]);
                }
            }

            $this->userModel->commit();

            $this->flash('success', 'تم تحديث الملف الشخصي بنجاح');
            $this->redirect('/doctor/profile');

        } catch (Exception $e) {
            $this->userModel->rollback();
            $this->flash('error', 'حدث خطأ أثناء تحديث الملف الشخصي');
            $this->redirect('/doctor/profile');
        }
    }

    /**
     * Manage schedule
     */
    public function schedule() {
        $schedule = $this->doctorModel->getSchedule($this->doctorProfile['id']);

        $data = [
            'title' => 'إدارة الجدول',
            'doctor' => $this->doctorProfile,
            'schedule' => $schedule,
            'working_days' => WORKING_DAYS,
            'time_slots' => TIME_SLOTS
        ];

        $this->renderWithLayout('doctor.schedule', $data, 'doctor');
    }

    /**
     * Update schedule
     */
    public function updateSchedule() {
        if (!$this->isPost()) {
            $this->redirect('/doctor/schedule');
        }

        $this->validateCSRF();

        $scheduleData = $this->post('schedule', []);

        try {
            $this->doctorModel->beginTransaction();

            // Delete existing schedule
            $this->doctorModel->query(
                "DELETE FROM doctor_schedules WHERE doctor_id = :doctor_id",
                ['doctor_id' => $this->doctorProfile['id']]
            );

            // Insert new schedule
            foreach ($scheduleData as $day => $times) {
                $isAvailable = !empty($times['is_available']) ? 1 : 0;
                $record = [
                    'doctor_id' => $this->doctorProfile['id'],
                    'day_of_week' => $day,
                    'start_time' => $times['start_time'] ?? null,
                    'end_time' => $times['end_time'] ?? null,
                    'is_available' => $isAvailable,
                    'break_start' => $times['break_start'] ?? null,
                    'break_end' => $times['break_end'] ?? null,
                    'max_appointments' => $times['max_appointments'] ?? 20
                ];
                $this->doctorModel->query(
                    "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_available, break_start, break_end, max_appointments)
                     VALUES (:doctor_id, :day_of_week, :start_time, :end_time, :is_available, :break_start, :break_end, :max_appointments)",
                    $record
                );
            }

            $this->doctorModel->commit();

            $this->flash('success', 'تم تحديث الجدول بنجاح');
            $this->redirect('/doctor/schedule');

        } catch (Exception $e) {
            $this->doctorModel->rollback();
            $this->flash('error', 'حدث خطأ أثناء تحديث الجدول');
            $this->redirect('/doctor/schedule');
        }
    }

    /**
     * Accept appointment
     */
    public function acceptAppointment() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $appointmentId = $this->post('appointment_id');

        // Verify appointment belongs to this doctor
        $appointment = $this->appointmentModel->getAppointmentDetails($appointmentId);

        if (!$appointment || $appointment['doctor_id'] != $this->doctorProfile['id']) {
            $this->error('الموعد غير موجود أو غير مخول لك');
        }

        if ($this->appointmentModel->updateStatus($appointmentId, APPOINTMENT_CONFIRMED)) {
            // Send notification to patient (implement notification system)
            $this->success('تم قبول الموعد بنجاح');
        } else {
            $this->error('حدث خطأ أثناء قبول الموعد');
        }
    }

    /**
     * Reject appointment
     */
    public function rejectAppointment() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $appointmentId = $this->post('appointment_id');
        $reason = $this->post('reason', '');

        // Verify appointment belongs to this doctor
        $appointment = $this->appointmentModel->getAppointmentDetails($appointmentId);

        if (!$appointment || $appointment['doctor_id'] != $this->doctorProfile['id']) {
            $this->error('الموعد غير موجود أو غير مخول لك');
        }

        if ($this->appointmentModel->cancelAppointment($appointmentId, $reason, 'doctor')) {
            // Send notification to patient (implement notification system)
            $this->success('تم رفض الموعد بنجاح');
        } else {
            $this->error('حدث خطأ أثناء رفض الموعد');
        }
    }

    /**
     * Complete appointment
     */
    public function completeAppointment() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $appointmentId = $this->post('appointment_id');
        $notes = $this->post('notes', '');
        $prescription = $this->post('prescription', '');

        // Verify appointment belongs to this doctor
        $appointment = $this->appointmentModel->getAppointmentDetails($appointmentId);

        if (!$appointment || $appointment['doctor_id'] != $this->doctorProfile['id']) {
            $this->error('الموعد غير موجود أو غير مخول لك');
        }

        $updateData = [
            'status' => APPOINTMENT_COMPLETED,
            'doctor_notes' => $notes,
            'prescription' => $prescription,
            'completed_at' => date('Y-m-d H:i:s')
        ];

        if ($this->appointmentModel->update($appointmentId, $updateData)) {
            // Update doctor statistics
            $this->doctorModel->query(
                "UPDATE doctors SET total_appointments = total_appointments + 1 WHERE id = :doctor_id",
                ['doctor_id' => $this->doctorProfile['id']]
            );

            $this->success('تم إكمال الموعد بنجاح');
        } else {
            $this->error('حدث خطأ أثناء إكمال الموعد');
        }
    }

    /**
     * Get appointment statistics
     */
    private function getAppointmentStatistics() {
        $doctorId = $this->doctorProfile['id'];

        return [
            'total' => $this->appointmentModel->count('doctor_id = :doctor_id', ['doctor_id' => $doctorId]),
            'pending' => $this->appointmentModel->count('doctor_id = :doctor_id AND status = :status', [
                'doctor_id' => $doctorId, 'status' => APPOINTMENT_PENDING
            ]),
            'confirmed' => $this->appointmentModel->count('doctor_id = :doctor_id AND status = :status', [
                'doctor_id' => $doctorId, 'status' => APPOINTMENT_CONFIRMED
            ]),
            'completed' => $this->appointmentModel->count('doctor_id = :doctor_id AND status = :status', [
                'doctor_id' => $doctorId, 'status' => APPOINTMENT_COMPLETED
            ]),
            'today' => $this->appointmentModel->count('doctor_id = :doctor_id AND appointment_date = :date', [
                'doctor_id' => $doctorId, 'date' => date('Y-m-d')
            ]),
            'this_week' => $this->appointmentModel->count(
                'doctor_id = :doctor_id AND appointment_date BETWEEN :start AND :end',
                [
                    'doctor_id' => $doctorId,
                    'start' => date('Y-m-d', strtotime('monday this week')),
                    'end' => date('Y-m-d', strtotime('sunday this week'))
                ]
            )
        ];
    }

    /**
     * Get recent reviews
     */
    private function getRecentReviews() {
        $sql = "SELECT r.*, p.name as patient_name, p.avatar as patient_avatar
                FROM reviews r
                LEFT JOIN patients pt ON r.patient_id = pt.id
                LEFT JOIN users p ON pt.user_id = p.id
                WHERE r.doctor_id = :doctor_id AND r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT 5";

        return $this->doctorModel->fetchRaw($sql, ['doctor_id' => $this->doctorProfile['id']]);
    }

    /**
     * Get upcoming appointments
     */
    private function getUpcomingAppointments() {
        $sql = "SELECT a.*, p.name as patient_name, p.phone as patient_phone
                FROM appointments a
                LEFT JOIN patients pt ON a.patient_id = pt.id
                LEFT JOIN users p ON pt.user_id = p.id
                WHERE a.doctor_id = :doctor_id
                AND a.appointment_date >= CURDATE()
                AND a.status IN ('pending', 'confirmed')
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT 10";

        return $this->appointmentModel->fetchRaw($sql, ['doctor_id' => $this->doctorProfile['id']]);
    }
    /**
     * List doctor patients
     */
    public function patients() {
        $page = $this->get('page', 1);
        $q = $this->get('q', '');
        // Build query of patients who have had any appointment with this doctor
        $conditions = ["a.doctor_id = :doctor_id", "u.status = 'active'"];
        $params = ['doctor_id' => $this->doctorProfile['id']];
        if ($q) {
            $conditions[] = "(u.name LIKE :q OR u.phone LIKE :q)";
            $params['q'] = "%{$q}%";
        }
        $where = implode(' AND ', $conditions);
        // Count
        $countSql = "SELECT COUNT(DISTINCT pt.id) AS cnt
                     FROM appointments a
                     LEFT JOIN patients pt ON a.patient_id = pt.id
                     LEFT JOIN users u ON pt.user_id = u.id
                     WHERE {$where}";
        $totalRow = $this->appointmentModel->fetchRaw($countSql, $params);
        $total = (int)($totalRow[0]['cnt'] ?? 0);
        // Page
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT DISTINCT pt.id, u.name, u.phone, u.avatar, p.date_of_birth, p.blood_type,
                       (SELECT COUNT(*) FROM patient_symptoms ps WHERE ps.patient_id = pt.id) AS symptom_count,
                       (SELECT COUNT(*) FROM appointments a2 WHERE a2.patient_id = pt.id AND a2.doctor_id = a.doctor_id) AS visit_count
                FROM appointments a
                LEFT JOIN patients pt ON a.patient_id = pt.id
                LEFT JOIN users u ON pt.user_id = u.id
                LEFT JOIN patients p ON pt.id = p.id
                WHERE {$where}
                ORDER BY u.name ASC
                LIMIT {$perPage} OFFSET {$offset}";
        $rows = $this->appointmentModel->fetchRaw($sql, $params);
        $patients = [
            'data' => $rows,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
        $data = [
            'title' => 'قائمة المرضى',
            'doctor' => $this->doctorProfile,
            'patients' => $patients,
            'q' => $q
        ];
        $this->renderWithLayout('doctor.patients', $data, 'doctor');
    }

    /**
     * Patient details for this doctor
     */
    public function patientDetails($id) {
        // Basic privacy: ensure this patient has at least one appointment with this doctor
        $exists = $this->appointmentModel->fetchRaw(
            "SELECT 1 FROM appointments WHERE doctor_id = :d AND patient_id = :p LIMIT 1",
            ['d' => $this->doctorProfile['id'], 'p' => $id]
        );
        if (empty($exists)) {
            $this->flash('error', 'هذا المريض غير مرتبط بك');
            $this->redirect('/doctor/patients');
        }
        // Fetch patient core info
        $sql = "SELECT pt.id, u.name, u.phone, u.email, u.avatar, u.address, u.city, u.country,
                       p.date_of_birth, p.gender, p.blood_type, p.medical_history, p.allergies, p.current_medications
                FROM patients pt
                LEFT JOIN users u ON pt.user_id = u.id
                LEFT JOIN patients p ON pt.id = p.id
                WHERE pt.id = :p LIMIT 1";
        $patient = $this->appointmentModel->fetchRaw($sql, ['p' => $id])[0] ?? null;
        if (!$patient) {
            $this->flash('error', 'لم يتم العثور على المريض');
            $this->redirect('/doctor/patients');
        }
        // Recent appointments between this doctor and patient
        $appointments = $this->appointmentModel->fetchRaw(
            "SELECT a.* FROM appointments a
             WHERE a.doctor_id = :d AND a.patient_id = :p
             ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 20",
            ['d' => $this->doctorProfile['id'], 'p' => $id]
        );
        $data = [
            'title' => 'تفاصيل المريض',
            'doctor' => $this->doctorProfile,
            'patient' => $patient,
            'appointments' => $appointments
        ];
        // Reuse a simple view; create if missing later
        $this->renderWithLayout('doctor.patient-details', $data, 'doctor');
    }

    /**
     * Doctor reviews list
     */
    public function reviews() {
        $page = $this->get('page', 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $doctorId = $this->doctorProfile['id'];

        // Total count
        $countRow = $this->doctorModel->fetchRaw(
            "SELECT COUNT(*) AS cnt FROM reviews WHERE doctor_id = :d AND is_approved = 1",
            ['d' => $doctorId]
        );
        $total = (int)($countRow[0]['cnt'] ?? 0);

        // Page data with patient names/avatars
        $rows = $this->doctorModel->fetchRaw(
            "SELECT r.*, u.name AS patient_name, u.avatar AS patient_avatar
             FROM reviews r
             LEFT JOIN patients pt ON r.patient_id = pt.id
             LEFT JOIN users u ON pt.user_id = u.id
             WHERE r.doctor_id = :d AND r.is_approved = 1
             ORDER BY r.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['d' => $doctorId]
        );

        $reviews = [
            'data' => $rows,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];

        $data = [
            'title' => 'التقييمات',
            'doctor' => $this->doctorProfile,
            'reviews' => $reviews
        ];

        $this->renderWithLayout('doctor.reviews', $data, 'doctor');
    }
    /**
     * Doctor analytics dashboard
     */
    public function analytics() {
        $doctorId = $this->doctorProfile['id'];
        // Basic KPIs
        $totalAppointments = $this->appointmentModel->count('doctor_id = :d', ['d' => $doctorId]);
        $completed = $this->appointmentModel->count('doctor_id = :d AND status = :s', ['d' => $doctorId, 's' => APPOINTMENT_COMPLETED]);
        $cancelled = $this->appointmentModel->count('doctor_id = :d AND status = :s', ['d' => $doctorId, 's' => APPOINTMENT_CANCELLED]);
        $confirmed = $this->appointmentModel->count('doctor_id = :d AND status = :s', ['d' => $doctorId, 's' => APPOINTMENT_CONFIRMED]);

        // Revenue estimation based on consultation_fee per completed appointment
        $fee = (float)($this->doctorProfile['consultation_fee'] ?? 0);
        $totalRevenue = $fee * $completed;

        // Appointments by day (last 14 days)
        $trend = $this->appointmentModel->fetchRaw(
            "SELECT appointment_date AS d, COUNT(*) AS c
             FROM appointments
             WHERE doctor_id = :d AND appointment_date >= :start
             GROUP BY appointment_date ORDER BY appointment_date",
            ['d' => $doctorId, 'start' => date('Y-m-d', strtotime('-14 days'))]
        );

        $data = [
            'title' => 'التحليلات',
            'doctor' => $this->doctorProfile,
            'kpis' => [
                'total' => $totalAppointments,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'confirmed' => $confirmed,
                'revenue' => $totalRevenue
            ],
            'trend' => $trend,
        ];
        $this->renderWithLayout('doctor.analytics', $data, 'doctor');
    }

    /**
     * Doctor settings page
     */
    public function settings() {
        $data = [
            'title' => 'الإعدادات',
            'doctor' => $this->doctorProfile
        ];
        $this->renderWithLayout('doctor.settings', $data, 'doctor');
    }

    /**
     * Health posts management - doctor's own posts
     */
    public function healthPosts() {
        $page = $this->get('page', 1);
        $posts = $this->healthPostModel->getByDoctor($this->doctorProfile['id'], $page);
        $stats = $this->healthPostModel->getDoctorStats($this->doctorProfile['id']);

        $data = [
            'title' => 'منشوراتي الصحية',
            'doctor' => $this->doctorProfile,
            'posts' => $posts,
            'stats' => $stats
        ];

        $this->renderWithLayout('doctor.health-posts', $data, 'doctor');
    }

    /**
     * Show create health post form
     */
    public function createHealthPost() {
        $specializations = $this->specializationModel->getActiveSpecializations();

        $data = [
            'title' => 'إنشاء منشور صحي جديد',
            'doctor' => $this->doctorProfile,
            'specializations' => $specializations
        ];

        $this->renderWithLayout('doctor.create-health-post', $data, 'doctor');
    }

    /**
     * Store new health post
     */
    public function storeHealthPost() {
        if (!$this->isPost()) {
            $this->redirect('/doctor/health-posts/create');
        }

        // Debug: Log CSRF validation
        error_log('POST data: ' . print_r($_POST, true));
        error_log('SESSION tokens: ' . print_r($_SESSION['csrf_tokens'] ?? [], true));
        error_log('CSRF_TOKEN_NAME: ' . CSRF_TOKEN_NAME);

        $this->validateCSRF();

        $postData = $this->post();

        // Validate input
        $errors = $this->validate($postData, [
            'title' => 'required|min:5|max:255',
            'content' => 'required|min:50'
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $postData;
            $this->redirect('/doctor/health-posts/create');
        }

        try {
            // Handle image upload
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = $this->uploadFile($_FILES['image'], 'uploads/health-posts', ['jpg', 'jpeg', 'png', 'webp']);
            }

            $data = [
                'doctor_id' => $this->doctorProfile['id'],
                'title' => $postData['title'],
                'content' => $postData['content'],
                'image_path' => $imagePath,
                'category' => $postData['category'] ?? null,
                'status' => 'pending'
            ];

            $this->healthPostModel->createPost($data);

            $this->flash('success', 'تم إنشاء المنشور بنجاح. سيتم مراجعته من قبل الإدارة قبل النشر.');
            $this->redirect('/doctor/health-posts');

        } catch (Exception $e) {
            $this->flash('error', 'حدث خطأ أثناء إنشاء المنشور');
            $this->redirect('/doctor/health-posts/create');
        }
    }

    /**
     * Show edit health post form
     */
    public function editHealthPost($id) {
        $post = $this->healthPostModel->getPostWithDetails($id);

        if (!$post || $post['doctor_id'] != $this->doctorProfile['id']) {
            $this->flash('error', 'المنشور غير موجود أو غير مخول لك');
            $this->redirect('/doctor/health-posts');
        }

        $specializations = $this->specializationModel->getActiveSpecializations();

        $data = [
            'title' => 'تعديل المنشور الصحي',
            'doctor' => $this->doctorProfile,
            'post' => $post,
            'specializations' => $specializations
        ];

        $this->renderWithLayout('doctor.edit-health-post', $data, 'doctor');
    }

    /**
     * Update health post
     */
    public function updateHealthPost($id) {
        if (!$this->isPost()) {
            $this->redirect('/doctor/health-posts');
        }

        $this->validateCSRF();

        $post = $this->healthPostModel->getPostWithDetails($id);

        if (!$post || $post['doctor_id'] != $this->doctorProfile['id']) {
            $this->flash('error', 'المنشور غير موجود أو غير مخول لك');
            $this->redirect('/doctor/health-posts');
        }

        $postData = $this->post();

        // Validate input
        $errors = $this->validate($postData, [
            'title' => 'required|min:5|max:255',
            'content' => 'required|min:50'
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $postData;
            $this->redirect('/doctor/health-posts/' . $id . '/edit');
        }

        try {
            // Handle image upload
            $imagePath = $post['image_path'];
            if (!empty($_FILES['image']['name'])) {
                $newImage = $this->uploadFile($_FILES['image'], 'uploads/health-posts', ['jpg', 'jpeg', 'png', 'webp']);
                if ($newImage) {
                    $imagePath = $newImage;
                }
            }

            $data = [
                'title' => $postData['title'],
                'content' => $postData['content'],
                'image_path' => $imagePath,
                'category' => $postData['category'] ?? null
            ];

            $this->healthPostModel->updatePost($id, $data);

            $this->flash('success', 'تم تحديث المنشور بنجاح');
            $this->redirect('/doctor/health-posts');

        } catch (Exception $e) {
            $this->flash('error', 'حدث خطأ أثناء تحديث المنشور');
            $this->redirect('/doctor/health-posts/' . $id . '/edit');
        }
    }

    /**
     * Delete health post
     */
    public function deleteHealthPost($id) {
        if (!$this->isPost()) {
            $this->redirect('/doctor/health-posts');
        }

        $this->validateCSRF();

        $post = $this->healthPostModel->getPostWithDetails($id);

        if (!$post || $post['doctor_id'] != $this->doctorProfile['id']) {
            $this->error('المنشور غير موجود أو غير مخول لك');
        }

        if ($this->healthPostModel->deletePost($id)) {
            $this->success('تم حذف المنشور بنجاح');
        } else {
            $this->error('حدث خطأ أثناء حذف المنشور');
        }
    }

    /**
     * View all approved health posts (public health info)
     */
    public function healthInfo() {
        $page = $this->get('page', 1);
        $category = $this->get('category', null);

        $posts = $this->healthPostModel->getAllApproved($page, 10, $category);
        $categories = $this->healthPostModel->getCategories();

        $data = [
            'title' => 'معلومات صحية',
            'doctor' => $this->doctorProfile,
            'posts' => $posts,
            'categories' => $categories,
            'selected_category' => $category
        ];

        $this->renderWithLayout('doctor.health-info', $data, 'doctor');
    }
}
