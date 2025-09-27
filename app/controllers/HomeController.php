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
