<?php
/**
 * Admin Controller
 * Handles super admin dashboard and management functions
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Patient.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Specialization.php';
require_once APP_PATH . '/models/HealthPost.php';

class AdminController extends Controller {
    private $userModel;
    private $doctorModel;
    private $patientModel;
    private $appointmentModel;
    private $specializationModel;
    private $healthPostModel;
    
    public function __construct() {
        parent::__construct();
        
        // Require super admin role
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $this->userModel = new User();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
        $this->specializationModel = new Specialization();
        $this->healthPostModel = new HealthPost();
    }
    
    /**
     * Admin dashboard
     */
    public function index() {
        $data = [
            'title' => 'لوحة الإدارة',
            'user_stats' => $this->userModel->getStatistics(),
            'doctor_stats' => $this->doctorModel->getStatistics(),
            'patient_stats' => $this->patientModel->getStatistics(),
            'appointment_stats' => $this->appointmentModel->getStatistics(),
            'recent_appointments' => $this->appointmentModel->getUpcomingAppointments(5),
            'pending_doctors' => $this->getPendingDoctors(),
            'monthly_stats' => $this->getMonthlyStatistics()
        ];
        
        $this->renderWithLayout('admin.dashboard', $data, 'admin');
    }
    
    /**
     * User management
     */
    public function users() {
        $page = $this->get('page', 1);
        $search = $this->get('search', '');
        $role = $this->get('role', '');
        
        if ($search) {
            $users = $this->userModel->searchUsers($search, $role, $page);
        } else {
            $conditions = $role ? 'role = :role' : '1=1';
            $params = $role ? ['role' => $role] : [];
            $users = $this->userModel->paginate($page, ITEMS_PER_PAGE, $conditions, $params, 'created_at DESC');
        }
        
        $data = [
            'title' => 'إدارة المستخدمين',
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'roles' => [
                ROLE_SUPER_ADMIN => 'مدير النظام',
                ROLE_DOCTOR => 'طبيب',
                ROLE_PATIENT => 'مريض'
            ]
        ];
        
        $this->renderWithLayout('admin.users', $data, 'admin');
    }
    
    /**
     * Doctor management
     */
    public function doctors() {
        $page = $this->get('page', 1);
        $search = $this->get('search', '');
        $status = $this->get('status', '');
        
        $conditions = ['u.role = :role'];
        $params = ['role' => ROLE_DOCTOR];
        
        if ($search) {
            $conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR d.license_number LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($status) {
            $conditions[] = "d.status = :status";
            $params['status'] = $status;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT d.*, s.name as specialization_name,
                       u.name, u.email, u.phone, u.city, u.created_at
                FROM doctors d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE {$whereClause}
                ORDER BY d.created_at DESC";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count
                     FROM doctors d
                     LEFT JOIN users u ON d.user_id = u.id
                     WHERE {$whereClause}";
        
        $countRows = $this->doctorModel->fetchRaw($countSql, $params);
        $total = (int)($countRows[0]['count'] ?? 0);

        // Get paginated results
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $results = $this->doctorModel->fetchRaw($sql, $params);
        
        $doctors = [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
        
        $data = [
            'title' => 'إدارة الأطباء',
            'doctors' => $doctors,
            'search' => $search,
            'status' => $status,
            'statuses' => [
                'pending' => 'في الانتظار',
                'approved' => 'مقبول',
                'suspended' => 'موقوف'
            ],
            'specializations' => $this->specializationModel->getActiveSpecializations()
        ];
        
        $this->renderWithLayout('admin.doctors', $data, 'admin');
    }
    
    /**
     * Patient management
     */
    public function patients() {
        $page = $this->get('page', 1);
        $search = $this->get('search', '');
        
        if ($search) {
            $patients = $this->patientModel->searchPatients($search, $page);
        } else {
            $patients = $this->patientModel->getAllPatients($page);
        }
        
        $data = [
            'title' => 'إدارة المرضى',
            'patients' => $patients,
            'search' => $search
        ];
        
        $this->renderWithLayout('admin.patients', $data, 'admin');
    }
    
    /**
     * Specialization management
     */
    public function specializations() {
        $specializations = $this->specializationModel->getSpecializationsWithDoctorCount();
        
        $data = [
            'title' => 'إدارة التخصصات',
            'specializations' => $specializations,
            'csrf_token' => $this->csrf->token()
        ];
        
        $this->renderWithLayout('admin.specializations', $data, 'admin');
    }
    
    /**
     * Appointment management
     */
    public function appointments() {
        $page = $this->get('page', 1);
        $status = $this->get('status', '');
        $date = $this->get('date', '');
        
        $appointments = $this->appointmentModel->getAllAppointments($status, $date, $page);
        
        $data = [
            'title' => 'إدارة المواعيد',
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
        
        $this->renderWithLayout('admin.appointments', $data, 'admin');
    }
    
    /**
     * Analytics and reports
     */
    public function analytics() {
        $startDate = $this->get('start_date', date('Y-m-01'));
        $endDate = $this->get('end_date', date('Y-m-t'));
        
        $data = [
            'title' => 'التقارير والإحصائيات',
            'appointment_stats' => $this->appointmentModel->getStatistics($startDate, $endDate),
            'user_registrations' => $this->getUserRegistrationStats($startDate, $endDate),
            'revenue_stats' => $this->getRevenueStats($startDate, $endDate),
            'popular_specializations' => $this->getPopularSpecializations(),
            'top_doctors' => $this->getTopDoctors(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        $this->renderWithLayout('admin.analytics', $data, 'admin');
    }
    
    /**
     * Update doctor status
     */
    public function updateDoctorStatus() {
        if (!$this->isPost()) {
            $this->error('طريقة الطلب غير صحيحة');
        }
        
        $this->validateCSRF();
        
        $doctorId = $this->post('doctor_id');
        $status = $this->post('status');
        
        if (!in_array($status, ['pending', 'approved', 'suspended'])) {
            $this->error('حالة غير صحيحة');
        }
        
        if ($this->doctorModel->updateStatus($doctorId, $status)) {
            $this->success('تم تحديث حالة الطبيب بنجاح');
        } else {
            $this->error('حدث خطأ أثناء تحديث حالة الطبيب');
        }
    }
    
    /**
     * Get pending doctors
     */
    private function getPendingDoctors() {
        $sql = "SELECT d.*, u.name, u.email, s.name as specialization_name
                FROM doctors d
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE d.status = 'pending'
                ORDER BY d.created_at DESC
                LIMIT 5";
        
        return $this->doctorModel->fetchRaw($sql);
    }
    
    /**
     * Get monthly statistics
     */
    private function getMonthlyStatistics() {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-{$i} months"));
            $months[] = [
                'month' => $date,
                'month_name' => date('F Y', strtotime($date . '-01')),
                'users' => $this->userModel->count("DATE_FORMAT(created_at, '%Y-%m') = :month", ['month' => $date]),
                'appointments' => $this->appointmentModel->count("DATE_FORMAT(created_at, '%Y-%m') = :month", ['month' => $date])
            ];
        }
        
        return $months;
    }
    
    /**
     * Get user registration statistics
     */
    private function getUserRegistrationStats($startDate, $endDate) {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                FROM users
                WHERE created_at BETWEEN :start_date AND :end_date
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        return $this->userModel->fetchRaw($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    /**
     * Get revenue statistics
     */
    private function getRevenueStats($startDate, $endDate) {
        $sql = "SELECT DATE(appointment_date) as date, SUM(fee) as revenue
                FROM appointments
                WHERE appointment_date BETWEEN :start_date AND :end_date
                AND status = 'completed'
                GROUP BY DATE(appointment_date)
                ORDER BY date ASC";
        
        return $this->appointmentModel->fetchRaw($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    /**
     * Get popular specializations
     */
    private function getPopularSpecializations() {
        $sql = "SELECT s.name, COUNT(a.id) as appointment_count
                FROM specializations s
                LEFT JOIN doctors d ON s.id = d.specialization_id
                LEFT JOIN appointments a ON d.id = a.doctor_id
                WHERE s.is_active = 1
                GROUP BY s.id
                ORDER BY appointment_count DESC
                LIMIT 10";
        
        return $this->specializationModel->fetchRaw($sql);
    }
    
    /**
     * Get top doctors
     */
    private function getTopDoctors() {
        $sql = "SELECT u.name, d.rating, d.total_reviews, d.total_appointments, s.name as specialization_name
                FROM doctors d
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE d.status = 'approved'
                ORDER BY d.rating DESC, d.total_reviews DESC
                LIMIT 10";
        
        return $this->doctorModel->fetchRaw($sql);
    }

    /**
     * Health posts management
     */
    public function healthPosts() {
        $page = $this->get('page', 1);
        $status = $this->get('status', null);

        $posts = $this->healthPostModel->getAllForAdmin($page, 15, $status);
        $stats = $this->healthPostModel->getAdminStats();

        $data = [
            'title' => 'إدارة المنشورات الصحية',
            'posts' => $posts,
            'stats' => $stats,
            'selected_status' => $status
        ];

        $this->renderWithLayout('admin.health-posts', $data, 'admin');
    }

    /**
     * Approve health post
     */
    public function approveHealthPost($id) {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $post = $this->healthPostModel->getPostWithDetails($id);

        if (!$post) {
            $this->error('المنشور غير موجود');
        }

        if ($this->healthPostModel->updateStatus($id, 'approved')) {
            $this->success('تم الموافقة على المنشور بنجاح');
        } else {
            $this->error('حدث خطأ أثناء الموافقة على المنشور');
        }
    }

    /**
     * Reject health post
     */
    public function rejectHealthPost($id) {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $post = $this->healthPostModel->getPostWithDetails($id);

        if (!$post) {
            $this->error('المنشور غير موجود');
        }

        if ($this->healthPostModel->updateStatus($id, 'rejected')) {
            $this->success('تم رفض المنشور بنجاح');
        } else {
            $this->error('حدث خطأ أثناء رفض المنشور');
        }
    }

    /**
     * Delete health post
     */
    public function deleteHealthPost($id) {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $post = $this->healthPostModel->getPostWithDetails($id);

        if (!$post) {
            $this->error('المنشور غير موجود');
        }

        if ($this->healthPostModel->deletePost($id)) {
            $this->success('تم حذف المنشور بنجاح');
        } else {
            $this->error('حدث خطأ أثناء حذف المنشور');
        }
    }
}

