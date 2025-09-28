<?php
/**
 * Authentication Controller
 * Handles user authentication, registration, and password management
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Patient.php';
require_once APP_PATH . '/models/Specialization.php';

class AuthController extends Controller {
    private $userModel;
    private $doctorModel;
    private $patientModel;
    private $specializationModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
        $this->specializationModel = new Specialization();
    }
    
    /**
     * Show login form
     */
    public function login() {
        // Redirect if already authenticated
        if ($this->auth->check()) {
            $this->redirectToDashboard();
        }
        
        $data = [
            'title' => 'تسجيل الدخول',
            'csrf_token' => $this->csrf->token()
        ];
        
        $this->renderWithLayout('auth.login', $data, 'auth');
    }
    
    /**
     * Handle login authentication
     */
    public function authenticate() {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }
        
        $this->validateCSRF();
        
        $email = $this->post('email');
        $password = $this->post('password');
        $remember = $this->post('remember');
        
        // Validate input
        $errors = $this->validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $this->post();
            $this->redirect('/login');
        }
        
        // Attempt authentication
        if ($this->auth->attempt($email, $password)) {
            // Set remember me cookie if requested
            if ($remember) {
                $this->setRememberMeCookie();
            }
            
            $this->flash('success', 'تم تسجيل الدخول بنجاح');
            $this->redirectToDashboard();
        } else {
            $this->flash('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
            $_SESSION['old'] = ['email' => $email];
            $this->redirect('/login');
        }
    }
    
    /**
     * Show registration form
     */
    public function register() {
        // Redirect if already authenticated
        if ($this->auth->check()) {
            $this->redirectToDashboard();
        }
        
        $data = [
            'title' => 'إنشاء حساب جديد',
            'specializations' => $this->specializationModel->getActiveSpecializations(),
            'csrf_token' => $this->csrf->token()
        ];
        
        $this->renderWithLayout('auth.register', $data, 'auth');
    }
    
    /**
     * Handle user registration
     */
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('/register');
        }
        
        $this->validateCSRF();
        
        $data = $this->post();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'password' => 'required|min:8',
            'password_confirmation' => 'required',
            'role' => 'required',
            'terms' => 'required'
        ]);
        
        // Check password confirmation
        if ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'][] = 'تأكيد كلمة المرور غير متطابق';
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            $errors['email'][] = 'البريد الإلكتروني مستخدم بالفعل';
        }
        
        // Role-specific validation
        if ($data['role'] === ROLE_DOCTOR) {
            if (empty($data['specialization_id'])) {
                $errors['specialization_id'][] = 'التخصص مطلوب للأطباء';
            }
            if (empty($data['license_number'])) {
                $errors['license_number'][] = 'رقم الترخيص مطلوب للأطباء';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->redirect('/register');
        }
        
        // Create user account
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => $data['role'],
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'country' => 'Iraq'
        ];
        
        $user = $this->auth->register($userData);
        
        if ($user) {
            // Create role-specific profile
            $this->createRoleProfile($user['id'], $data['role'], $data);
            
            // Auto-login the user
            $this->auth->login($user);
            
            $this->flash('success', 'تم إنشاء الحساب بنجاح');
            $this->redirectToDashboard();
        } else {
            $this->flash('error', 'حدث خطأ أثناء إنشاء الحساب');
            $_SESSION['old'] = $data;
            $this->redirect('/register');
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        $this->auth->logout();
        $this->clearRememberMeCookie();
        $this->flash('success', 'تم تسجيل الخروج بنجاح');
        $this->redirect('/');
    }
    
    /**
     * Show forgot password form
     */
    public function forgotPassword() {
        $data = [
            'title' => 'نسيت كلمة المرور',
            'csrf_token' => $this->csrf->token()
        ];
        
        $this->renderWithLayout('auth.forgot-password', $data, 'auth');
    }
    
    /**
     * Handle forgot password request
     */
    public function sendResetLink() {
        if (!$this->isPost()) {
            $this->redirect('/forgot-password');
        }
        
        $this->validateCSRF();
        
        $email = $this->post('email');
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'يرجى إدخال بريد إلكتروني صحيح');
            $this->redirect('/forgot-password');
        }
        
        $token = $this->auth->generateResetToken($email);
        
        if ($token) {
            // Send reset email (implement email sending)
            $this->sendPasswordResetEmail($email, $token);
            $this->flash('success', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني');
        } else {
            $this->flash('error', 'البريد الإلكتروني غير موجود في النظام');
        }
        
        $this->redirect('/forgot-password');
    }
    
    /**
     * Show reset password form
     */
    public function resetPassword($token) {
        $reset = $this->auth->verifyResetToken($token);
        
        if (!$reset) {
            $this->flash('error', 'رابط إعادة تعيين كلمة المرور غير صحيح أو منتهي الصلاحية');
            $this->redirect('/forgot-password');
        }
        
        $data = [
            'title' => 'إعادة تعيين كلمة المرور',
            'token' => $token,
            'csrf_token' => $this->csrf->token()
        ];
        
        $this->renderWithLayout('auth.reset-password', $data, 'auth');
    }
    
    /**
     * Handle password reset
     */
    public function updatePassword() {
        if (!$this->isPost()) {
            $this->redirect('/');
        }
        
        $this->validateCSRF();
        
        $token = $this->post('token');
        $password = $this->post('password');
        $passwordConfirmation = $this->post('password_confirmation');
        
        // Validate input
        $errors = [];
        
        if (!$password || strlen($password) < 8) {
            $errors['password'][] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        }
        
        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'][] = 'تأكيد كلمة المرور غير متطابق';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('/reset-password/' . $token);
        }
        
        if ($this->auth->resetPassword($token, $password)) {
            $this->flash('success', 'تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول');
            $this->redirect('/login');
        } else {
            $this->flash('error', 'رابط إعادة تعيين كلمة المرور غير صحيح أو منتهي الصلاحية');
            $this->redirect('/forgot-password');
        }
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
    
    /**
     * Create role-specific profile
     */
    private function createRoleProfile($userId, $role, $data) {
        switch ($role) {
            case ROLE_DOCTOR:
                $doctorData = [
                    'user_id' => $userId,
                    'specialization_id' => $data['specialization_id'],
                    'license_number' => $data['license_number'],
                    'experience_years' => $data['experience_years'] ?? 0,
                    'biography' => $data['biography'] ?? '',
                    'consultation_fee' => $data['consultation_fee'] ?? 0,
                    'status' => DOCTOR_PENDING
                ];
                $this->doctorModel->create($doctorData);
                break;
                
            case ROLE_PATIENT:
                $patientData = [
                    'user_id' => $userId,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? 'male',
                    'blood_type' => $data['blood_type'] ?? '',
                    'emergency_contact' => $data['emergency_contact'] ?? '',
                    'emergency_contact_name' => $data['emergency_contact_name'] ?? ''
                ];
                $this->patientModel->create($patientData);
                break;
        }
    }
    
    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie() {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        setcookie('remember_token', $token, $expires, '/', '', false, true);
        
        // Store token in database (implement if needed)
    }
    
    /**
     * Clear remember me cookie
     */
    private function clearRememberMeCookie() {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($email, $token) {
        // Implement email sending functionality
        // This is a placeholder for the actual email implementation
        $resetLink = APP_URL . '/reset-password/' . $token;
        
        // You can use PHPMailer or any other email library here
        // For now, we'll just log it
        error_log("Password reset link for {$email}: {$resetLink}");
    }
}
