<?php
/**
 * Home Controller
 * Handles the main landing page and public pages
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Specialization.php';
require_once APP_PATH . '/models/Appointment.php';

class HomeController extends Controller {
    private $userModel;
    private $doctorModel;
    private $specializationModel;
    private $appointmentModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->doctorModel = new Doctor();
        $this->specializationModel = new Specialization();
        $this->appointmentModel = new Appointment();
    }
    
    /**
     * Show home page
     */
    public function index() {
        // Redirect authenticated users to their dashboard
        if ($this->auth->check()) {
            $this->redirectToDashboard();
        }
        
        // Get data for home page
        $data = [
            'title' => 'الصفحة الرئيسية',
            'specializations' => $this->specializationModel->getSpecializationsWithDoctorCount(),
            'featured_doctors' => $this->getFeaturedDoctors(),
            'statistics' => $this->getSystemStatistics()
        ];
        
        $this->renderWithLayout('home.index', $data, 'main');
    }
    
    /**
     * Show about page
     */
    public function about() {
        $data = [
            'title' => 'عن النظام',
            'statistics' => $this->getSystemStatistics()
        ];
        
        $this->renderWithLayout('home.about', $data, 'main');
    }
    
    /**
     * Show contact page
     */
    public function contact() {
        $data = [
            'title' => 'اتصل بنا',
            'csrf_token' => $this->csrf->token()
        ];
        
        $this->renderWithLayout('home.contact', $data, 'main');
    }
    
    /**
     * Handle contact form submission
     */
    public function sendContact() {
        if (!$this->isPost()) {
            $this->redirect('/contact');
        }
        
        $this->validateCSRF();
        
        $data = $this->post();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'subject' => 'required|min:5|max:255',
            'message' => 'required|min:10'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/contact');
        }
        
        // Process contact form (send email, save to database, etc.)
        $this->processContactForm($data);
        
        $this->flash('success', 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً');
        $this->redirect('/contact');
    }
    
    /**
     * Show privacy policy
     */
    public function privacy() {
        $data = [
            'title' => 'سياسة الخصوصية'
        ];
        
        $this->renderWithLayout('home.privacy', $data, 'main');
    }
    
    /**
     * Show terms of service
     */
    public function terms() {
        $data = [
            'title' => 'شروط الاستخدام'
        ];

        $this->renderWithLayout('home.terms', $data, 'main');
    }

    /**
     * Public: search doctors (no login required)
     */
    public function searchDoctors() {
        $search = $_GET['search'] ?? '';
        $specialization = $_GET['specialization'] ?? '';
        $city = $_GET['city'] ?? '';
        $rating = $_GET['rating'] ?? '';
        $maxFee = $_GET['max_fee'] ?? '';
        $page = (int)($_GET['page'] ?? 1);

        $perPage = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 12;
        $offset = ($page - 1) * $perPage;

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
        if ($rating) {
            $conditions[] = "d.rating >= :rating";
            $params['rating'] = $rating;
        }
        if ($maxFee) {
            $conditions[] = "d.consultation_fee <= :max_fee";
            $params['max_fee'] = $maxFee;
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT d.*, s.name as specialization_name, s.icon as specialization_icon,
                       u.name, u.email, u.phone, u.city, u.avatar,
                       (SELECT COUNT(a.id) FROM appointments a WHERE a.doctor_id = d.id) as total_appointments
                FROM doctors d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE {$whereClause}
                ORDER BY d.rating DESC, d.total_reviews DESC
                LIMIT :limit OFFSET :offset";

        $countSql = "SELECT COUNT(*) as count
                     FROM doctors d
                     LEFT JOIN users u ON d.user_id = u.id
                     WHERE {$whereClause}";

        // Execute
        $paramsWithPaging = array_merge($params, ['limit' => $perPage, 'offset' => $offset]);
        $doctors = $this->doctorModel->db->fetchAll($sql, $paramsWithPaging);
        $countRow = $this->doctorModel->db->fetch($countSql, $params);
        $total = (int)($countRow['count'] ?? 0);

        $data = [
            'title' => 'البحث عن الأطباء',
            'doctors' => [
                'data' => $doctors,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $total ? ($offset + 1) : 0,
                'to' => min($offset + $perPage, $total)
            ],
            'specializations' => $this->specializationModel->getActiveSpecializations(),
            'cities' => $this->userModel->getCities(),
            'filters' => [
                'search' => $search,
                'specialization' => $specialization,
                'city' => $city,
                'rating' => $rating,
                'max_fee' => $maxFee,
            ]
        ];

        // Reuse patient search view but render with main layout (public)
        $this->renderWithLayout('patient.search-doctors', $data, 'main');
    }

    /**
     * Public: map search page
     */
    public function mapSearch() {
        $data = [
            'title' => 'البحث بالخريطة',
            'specializations' => $this->specializationModel->getActiveSpecializations()
        ];
        $this->renderWithLayout('patient.map-search', $data, 'main');
    }

    /**
     * Show FAQ page
     */
    public function faq() {
        $data = [
            'title' => 'الأسئلة الشائعة',
            'faqs' => $this->getFAQs()
        ];
        
        $this->renderWithLayout('home.faq', $data, 'main');
    }
    
    /**
     * Show services page
     */
    public function services() {
        $data = [
            'title' => 'خدماتنا',
            'specializations' => $this->specializationModel->getSpecializationsWithDoctorCount()
        ];
        
        $this->renderWithLayout('home.services', $data, 'main');
    }
    
    /**
     * Get featured doctors for home page
     */
    private function getFeaturedDoctors() {
        $sql = "SELECT d.*, s.name as specialization_name, s.color as specialization_color,
                       u.name, u.avatar, u.city
                FROM doctors d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.status = 'approved' AND u.status = 'active'
                ORDER BY d.rating DESC, d.total_reviews DESC
                LIMIT 8";
        
        return $this->doctorModel->fetchRaw($sql);
    }
    
    /**
     * Get system statistics for display
     */
    private function getSystemStatistics() {
        $userStats = $this->userModel->getStatistics();
        $doctorStats = $this->doctorModel->getStatistics();
        $appointmentStats = $this->appointmentModel->getStatistics();
        
        return [
            'total_doctors' => $doctorStats['approved_doctors'],
            'total_patients' => $userStats['total_patients'],
            'total_appointments' => $appointmentStats['completed_appointments'],
            'total_specializations' => count($this->specializationModel->getActiveSpecializations()),
            'average_rating' => $doctorStats['average_rating']
        ];
    }
    
    /**
     * Get FAQ data
     */
    private function getFAQs() {
        return [
            [
                'question' => 'كيف يمكنني حجز موعد مع طبيب؟',
                'answer' => 'يمكنك حجز موعد من خلال إنشاء حساب جديد، ثم البحث عن الطبيب المناسب وتحديد الموعد المتاح.'
            ],
            [
                'question' => 'هل يمكنني إلغاء الموعد؟',
                'answer' => 'نعم، يمكنك إلغاء الموعد قبل 24 ساعة على الأقل من موعد الزيارة.'
            ],
            [
                'question' => 'كيف يتم تأكيد الموعد؟',
                'answer' => 'سيتم تأكيد الموعد من قبل الطبيب، وستصلك رسالة تأكيد عبر البريد الإلكتروني أو الهاتف.'
            ],
            [
                'question' => 'هل الخدمة مجانية؟',
                'answer' => 'حجز الموعد مجاني، لكن رسوم الاستشارة تختلف حسب الطبيب والتخصص.'
            ],
            [
                'question' => 'كيف يمكنني تقييم الطبيب؟',
                'answer' => 'بعد انتهاء الموعد، يمكنك تقييم الطبيب وكتابة مراجعة من خلال حسابك الشخصي.'
            ],
            [
                'question' => 'هل يمكنني تغيير موعدي؟',
                'answer' => 'نعم، يمكنك تعديل الموعد من خلال حسابك الشخصي إذا كان هناك مواعيد متاحة أخرى.'
            ]
        ];
    }
    
    /**
     * Process contact form
     */
    private function processContactForm($data) {
        // Here you can:
        // 1. Save the message to database
        // 2. Send email to admin
        // 3. Send auto-reply to user
        
        // For now, we'll just log it
        error_log("Contact form submission: " . json_encode($data));
        
        // You can implement email sending here using PHPMailer
        // $this->sendContactEmail($data);
    }
    
    /**
     * Redirect to appropriate dashboard based on user role
     */
    private function redirectToDashboard() {
        $user = $this->auth->user();
        
        switch ($user['role']) {
            case ROLE_SUPER_ADMIN:
                $this->redirect('/admin');
                break;
            case ROLE_DOCTOR:
                $this->redirect('/doctor');
                break;
            case ROLE_PATIENT:
                $this->redirect('/patient');
                break;
            default:
                $this->redirect('/');
        }
    }
}
