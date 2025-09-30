<?php
/**
 * API Controller
 * RESTful API endpoints for mobile app
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Patient.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Specialization.php';
require_once APP_PATH . '/models/HealthPost.php';

class ApiController extends Controller {
    private $userModel;
    private $doctorModel;
    private $patientModel;
    private $appointmentModel;
    private $specializationModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
        $this->specializationModel = new Specialization();

        // Set JSON content type
        header('Content-Type: application/json; charset=utf-8');

        // Enable CORS for mobile apps
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    // ----- Helpers for API responses and auth -----
    private function apiSuccess($message = 'OK', $data = [], $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function apiError($message = 'Error', $statusCode = 400, $errors = []) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function isPut() {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }

    private function getJsonInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }

    private function getAuthorizationHeader() {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about case)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    private function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private function generateApiToken($userId, $ttlSeconds = 86400) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'uid' => (int)$userId,
            'exp' => time() + $ttlSeconds,
            'iat' => time()
        ]));
        $signature = hash_hmac('sha256', $header . '.' . $payload, APP_KEY);
        return $header . '.' . $payload . '.' . $signature;
    }

    private function validateApiToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        [$header, $payload, $signature] = $parts;
        $expected = hash_hmac('sha256', $header . '.' . $payload, APP_KEY);
        if (!hash_equals($expected, $signature)) return false;
        $data = json_decode(base64_decode($payload), true);
        if (!$data || ($data['exp'] ?? 0) < time()) return false;
        $user = $this->userModel->find((int)$data['uid']);
        if (!$user || $user['status'] !== 'active') return false;
        return $user;
    }

    private function requireApiAuth() {
        $token = $this->getBearerToken();
        if (!$token) {
            $this->apiError('Unauthorized', 401);
        }
        $user = $this->validateApiToken($token);
        if (!$user) {
            $this->apiError('Invalid or expired token', 401);
        }
        return $user;
    }

    private function getUserProfile($user) {
        // Use User model helper to get role-specific profile
        if (method_exists($this->userModel, 'getFullProfile')) {
            return $this->userModel->getFullProfile($user['id']);
        }
        return $user;
    }

    private function formatDoctorForApi($doc) {
        return [
            'id' => (int)$doc['id'],
            'name' => $doc['name'] ?? '',
            'specialization' => $doc['specialization_name'] ?? null,
            'rating' => isset($doc['rating']) ? round((float)$doc['rating'], 2) : null,
            'total_reviews' => isset($doc['total_reviews']) ? (int)$doc['total_reviews'] : null,
            'consultation_fee' => isset($doc['consultation_fee']) ? (float)$doc['consultation_fee'] : null,
            'city' => $doc['city'] ?? null,
            'address' => $doc['address'] ?? ($doc['clinic_address'] ?? null),
            'avatar' => $doc['avatar'] ?? null,
            'latitude' => isset($doc['latitude']) ? (float)$doc['latitude'] : null,
            'longitude' => isset($doc['longitude']) ? (float)$doc['longitude'] : null,
            'distance_km' => isset($doc['distance']) ? round((float)$doc['distance'], 2) : null,
        ];
    }

    private function formatAppointmentForApi($a) {
        return [
            'id' => (int)$a['id'],
            'doctor_id' => (int)$a['doctor_id'],
            'patient_id' => (int)$a['patient_id'],
            'date' => $a['appointment_date'],
            'time' => $a['appointment_time'],
            'status' => $a['status'],
            'symptoms' => $a['symptoms'] ?? null,
            'notes' => $a['notes'] ?? ($a['patient_notes'] ?? null),
            'doctor_notes' => $a['doctor_notes'] ?? null,
            'prescription' => $a['prescription'] ?? null,
        ];
    }

    /**
     * API Authentication
     */
    public function login() {
        if (!$this->isPost()) {
            $this->apiError('Method not allowed', 405);
        }

        $email = $this->post('email');
        $password = $this->post('password');

        if (!$email || !$password) {
            $this->apiError('Email and password are required', 400);
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                $this->apiError('Invalid credentials', 401);
            }

            if ($user['status'] !== 'active') {
                $this->apiError('Account is not active', 403);
            }

            // Generate API token
            $token = $this->generateApiToken($user['id']);

            // Update last login
            $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

            // Get user profile based on role
            $profile = $this->getUserProfile($user);

            $this->apiSuccess('Login successful', [
                'token' => $token,
                'user' => $profile,
                'expires_in' => 86400 // 24 hours
            ]);

        } catch (Exception $e) {
            $this->apiError('Login failed', 500);
        }
    }

    /**
     * API Registration
     */
    public function register() {
        if (!$this->isPost()) {
            $this->apiError('Method not allowed', 405);
        }

        $name = $this->post('name');
        $email = $this->post('email');
        $password = $this->post('password');
        $phone = $this->post('phone');
        $role = $this->post('role', ROLE_PATIENT);

        // Validation
        if (!$name || !$email || !$password || !$phone) {
            $this->apiError('All fields are required', 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->apiError('Invalid email format', 400);
        }

        if (strlen($password) < 6) {
            $this->apiError('Password must be at least 6 characters', 400);
        }

        if (!in_array($role, [ROLE_PATIENT, ROLE_DOCTOR])) {
            $this->apiError('Invalid role', 400);
        }

        try {
            // Check if email exists
            if ($this->userModel->findByEmail($email)) {
                $this->apiError('Email already exists', 409);
            }

            // Create user
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => $phone,
                'role' => $role,
                'status' => $role === ROLE_DOCTOR ? 'pending' : 'active'
            ];

            $userId = $this->userModel->create($userData);

            // Create role-specific profile
            if ($role === ROLE_PATIENT) {
                $this->patientModel->create(['user_id' => $userId]);
            } elseif ($role === ROLE_DOCTOR) {
                $this->doctorModel->create([
                    'user_id' => $userId,
                    'status' => 'pending'
                ]);
            }

            $this->apiSuccess('Registration successful', [
                'user_id' => $userId,
                'status' => $userData['status']
            ], 201);

        } catch (Exception $e) {
            $this->apiError('Registration failed', 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile() {
        $user = $this->requireApiAuth();
        $profile = $this->getUserProfile($user);

        $this->apiSuccess('Profile retrieved', ['user' => $profile]);
    }

    /**
     * Update user profile
     */
    public function updateProfile() {
        if (!$this->isPut()) {
            $this->apiError('Method not allowed', 405);
        }

        $user = $this->requireApiAuth();
        $data = $this->getJsonInput();

        try {
            // Update user data
            $userFields = ['name', 'phone', 'city', 'address', 'date_of_birth', 'gender'];
            $userData = array_intersect_key($data, array_flip($userFields));

            if (!empty($userData)) {
                $this->userModel->update($user['id'], $userData);
            }

            // Update role-specific data
            if ($user['role'] === ROLE_PATIENT) {
                $patientFields = ['emergency_contact', 'blood_type', 'allergies', 'medical_history'];
                $patientData = array_intersect_key($data, array_flip($patientFields));

                if (!empty($patientData)) {
                    $patient = $this->patientModel->getByUserId($user['id']);
                    if ($patient) {
                        $this->patientModel->update($patient['id'], $patientData);
                    }
                }
            } elseif ($user['role'] === ROLE_DOCTOR) {
                $doctorFields = ['specialization_id', 'license_number', 'experience_years', 'biography', 'consultation_fee'];
                $doctorData = array_intersect_key($data, array_flip($doctorFields));

                if (!empty($doctorData)) {
                    $doctor = $this->doctorModel->getByUserId($user['id']);
                    if ($doctor) {
                        $this->doctorModel->update($doctor['id'], $doctorData);
                    }
                }
            }

            $this->apiSuccess('Profile updated successfully');

        } catch (Exception $e) {
            $this->apiError('Profile update failed', 500);
        }
    }

    /**
     * Get doctors list
     */
    public function doctors() {
        $page = $this->get('page', 1);
        $search = $this->get('search', '');
        $specialization = $this->get('specialization', '');
        $city = $this->get('city', '');
        $latitude = $this->get('latitude', '');
        $longitude = $this->get('longitude', '');
        $radius = $this->get('radius', 50);

        try {
            if ($latitude && $longitude) {
                // Location-based search
                $doctors = $this->doctorModel->findNearbyDoctors($latitude, $longitude, $radius, $specialization, $page);
            } else {
                // Regular search
                $doctors = $this->doctorModel->searchDoctors($search, $specialization, $city, $page);
            }

            // Format doctors data for API
            $doctors['data'] = array_map([$this, 'formatDoctorForApi'], $doctors['data']);

            $this->apiSuccess('Doctors retrieved', $doctors);

        } catch (Exception $e) {
            $this->apiError('Failed to retrieve doctors', 500);
        }
    }

    /**
     * Get doctor details
     */
    public function doctorDetails($id) {
        $doctorId = (int)$id;

        if (!$doctorId) {
            $this->apiError('Doctor ID is required', 400);
        }

        try {
            $doctor = $this->doctorModel->getDoctorWithDetails($doctorId);

            if (!$doctor) {
                $this->apiError('Doctor not found', 404);
            }

            $formattedDoctor = $this->formatDoctorForApi($doctor);

            // Get doctor's schedule
            $schedule = $this->doctorModel->getSchedule($doctorId);
            $formattedDoctor['schedule'] = $schedule;



            $this->apiSuccess('Doctor details retrieved', ['doctor' => $formattedDoctor]);

        } catch (Exception $e) {
            $this->apiError('Failed to retrieve doctor details', 500);
        }
    }

    /**
     * Get available time slots
     */
    public function availableSlots($doctor_id) {
        $doctorId = (int)$doctor_id;
        $date = $this->get('date');

        if (!$doctorId || !$date) {
            $this->apiError('Doctor ID and date are required', 400);
        }

        try {
            $slots = $this->doctorModel->getAvailableSlots($doctorId, $date);

            // Format slots
            $formattedSlots = array_map(function($slot) {
                return [
                    'time' => $slot,
                    'formatted' => date('H:i', strtotime($slot))
                ];
            }, $slots);

            $this->apiSuccess('Available slots retrieved', ['slots' => $formattedSlots]);

        } catch (Exception $e) {
            $this->apiError('Failed to retrieve available slots', 500);
        }
    }

    /**
     * Book appointment
     */
    public function bookAppointment() {
        if (!$this->isPost()) {
            $this->apiError('Method not allowed', 405);
        }

        $user = $this->requireApiAuth();

        if ($user['role'] !== ROLE_PATIENT) {
            $this->apiError('Only patients can book appointments', 403);
        }

        $doctorId = $this->post('doctor_id');
        $appointmentDate = $this->post('appointment_date');
        $appointmentTime = $this->post('appointment_time');
        $symptoms = $this->post('symptoms', '');
        $notes = $this->post('notes', '');

        if (!$doctorId || !$appointmentDate || !$appointmentTime) {
            $this->apiError('Doctor ID, date, and time are required', 400);
        }

        try {
            // Get patient
            $patient = $this->patientModel->getByUserId($user['id']);
            if (!$patient) {
                $this->apiError('Patient profile not found', 404);
            }

            // Check if slot is available
            if (!$this->doctorModel->isAvailable($doctorId, $appointmentDate, $appointmentTime)) {
                $this->apiError('Time slot is not available', 409);
            }

            // Create appointment
            $appointmentData = [
                'patient_id' => $patient['id'],
                'doctor_id' => $doctorId,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'symptoms' => $symptoms,
                'notes' => $notes,
                'status' => APPOINTMENT_PENDING
            ];

            $appointment = $this->appointmentModel->createAppointment($appointmentData);
            if (!$appointment || empty($appointment['id'])) {
                $this->apiError('Time slot is not available', 409);
            }
            $appointmentId = (int)$appointment['id'];

            $this->apiSuccess('Appointment booked successfully', [
                'appointment_id' => $appointmentId
            ], 201);

        } catch (Exception $e) {
            $this->apiError('Failed to book appointment', 500);
        }
    }

    /**
     * Get a single appointment by ID (authorized to owner doctor/patient)
     */
    public function getAppointment($id) {
        $user = $this->requireApiAuth();
        $appointment = $this->appointmentModel->find((int)$id);
        if (!$appointment) {
            $this->apiError('Appointment not found', 404);
        }

        // Authorization: only the owning patient/doctor can access
        if ($user['role'] === ROLE_PATIENT) {
            $patient = $this->patientModel->getByUserId($user['id']);
            if (!$patient || (int)$appointment['patient_id'] !== (int)$patient['id']) {
                $this->apiError('Forbidden', 403);
            }
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $doctor = $this->doctorModel->getByUserId($user['id']);
            if (!$doctor || (int)$appointment['doctor_id'] !== (int)$doctor['id']) {
                $this->apiError('Forbidden', 403);
            }
        } else {
            $this->apiError('Forbidden', 403);
        }

        $details = $this->appointmentModel->getAppointmentDetails((int)$id) ?: $appointment;
        $this->apiSuccess('Appointment retrieved', ['appointment' => $details]);
    }

    /**
     * Update appointment (doctor confirm/complete/cancel; patient cancel)
     */
    public function updateAppointment($id) {
        if (!$this->isPut()) {
            $this->apiError('Method not allowed', 405);
        }
        $user = $this->requireApiAuth();
        $appointment = $this->appointmentModel->find((int)$id);
        if (!$appointment) {
            $this->apiError('Appointment not found', 404);
        }

        $data = $this->getJsonInput();
        $status = $data['status'] ?? '';
        $notes = $data['notes'] ?? null;
        $prescription = $data['prescription'] ?? null;
        $reason = $data['reason'] ?? ($data['cancellation_reason'] ?? null);

        try {
            if ($user['role'] === ROLE_PATIENT) {
                $patient = $this->patientModel->getByUserId($user['id']);
                if (!$patient || (int)$appointment['patient_id'] !== (int)$patient['id']) {
                    $this->apiError('Forbidden', 403);
                }
                // Patient can only cancel
                if (!in_array(strtolower($status), ['cancelled', APPOINTMENT_CANCELLED])) {
                    $this->apiError('Patients can only cancel appointments', 400);
                }
                $updated = $this->appointmentModel->cancelAppointment((int)$id, (string)$reason, 'patient');
                $this->apiSuccess('Appointment cancelled', ['appointment' => $updated]);
            } elseif ($user['role'] === ROLE_DOCTOR) {
                $doctor = $this->doctorModel->getByUserId($user['id']);
                if (!$doctor || (int)$appointment['doctor_id'] !== (int)$doctor['id']) {
                    $this->apiError('Forbidden', 403);
                }
                // Doctor can confirm/complete/cancel and add notes/prescription
                $allowed = [APPOINTMENT_CONFIRMED, APPOINTMENT_COMPLETED, APPOINTMENT_CANCELLED, 'confirmed', 'completed', 'cancelled'];
                if ($status && !in_array($status, $allowed)) {
                    // normalize lowercase string statuses to constants if provided as strings
                    $map = ['confirmed' => APPOINTMENT_CONFIRMED, 'completed' => APPOINTMENT_COMPLETED, 'cancelled' => APPOINTMENT_CANCELLED];
                    $status = $map[strtolower((string)$status)] ?? $status;
                }
                if ($status) {
                    $this->appointmentModel->updateStatus((int)$id, $status, $notes);
                }
                $updateFields = [];
                if ($prescription !== null) $updateFields['prescription'] = $prescription;
                if (!empty($updateFields)) {
                    $this->appointmentModel->update((int)$id, $updateFields);
                }
                $updated = $this->appointmentModel->getAppointmentDetails((int)$id);
                $this->apiSuccess('Appointment updated', ['appointment' => $updated]);
            } else {
                $this->apiError('Forbidden', 403);
            }
        } catch (Exception $e) {
            $this->apiError('Failed to update appointment', 500);
        }
    }



    /**
     * Get user appointments
     */
    public function appointments() {
        $user = $this->requireApiAuth();
        $page = (int)$this->get('page', 1);
        $status = $this->get('status', '');

        try {
            if ($user['role'] === ROLE_PATIENT) {
                $patient = $this->patientModel->getByUserId($user['id']);
                $appointments = $this->appointmentModel->getPatientAppointments($patient['id'], $status, $page);
            } elseif ($user['role'] === ROLE_DOCTOR) {
                $doctor = $this->doctorModel->getByUserId($user['id']);
                $appointments = $this->appointmentModel->getDoctorAppointments($doctor['id'], $status, null, $page);
            } else {
                $this->apiError('Invalid user role', 403);
            }

            // Format appointments for API
            $appointments['data'] = array_map([$this, 'formatAppointmentForApi'], $appointments['data']);

            $this->apiSuccess('Appointments retrieved', $appointments);

        } catch (Exception $e) {
            $this->apiError('Failed to retrieve appointments', 500);
        }
    }

    /**
     * Get specializations
     */
    public function specializations() {
        try {
            $specializations = $this->specializationModel->getActiveSpecializations();
            $this->apiSuccess('Specializations retrieved', ['specializations' => $specializations]);
        } catch (Exception $e) {
            $this->apiError('Failed to retrieve specializations', 500);
        }
    }

    // ----- Reviews API -----

    /**
     * Create a new review (Patients only)
     * POST /api/reviews
     */
    public function createReview() {
        if (!$this->isPost()) {
            $this->apiError('Method not allowed', 405);
        }
        $user = $this->requireApiAuth();
        if ($user['role'] !== ROLE_PATIENT) {
            $this->apiError('Only patients can create reviews', 403);
        }

        $appointmentId = (int)$this->post('appointment_id');
        $doctorId = (int)$this->post('doctor_id');
        $rating = (int)$this->post('rating');
        $reviewText = trim((string)$this->post('review', ''));
        $isAnonymous = (bool)$this->post('is_anonymous', false);

        if (!$appointmentId || !$doctorId || !$rating) {
            $this->apiError('Appointment ID, Doctor ID and rating are required', 422);
        }
        if ($rating < 1 || $rating > 5) {
            $this->apiError('Rating must be between 1 and 5', 422);
        }

        // Get patient by user
        $patient = $this->patientModel->getByUserId($user['id']);
        if (!$patient) {
            $this->apiError('Patient profile not found', 404);
        }

        // Verify patient has a completed appointment with doctor
        $hasCompleted = $this->appointmentModel->exists(
            'patient_id = :p AND doctor_id = :d AND status = :s',
            ['p' => (int)$patient['id'], 'd' => $doctorId, 's' => APPOINTMENT_COMPLETED]
        );
        if (!$hasCompleted) {
            $this->apiError('You can only review after a completed appointment', 403);
        }

        // Create review
        $reviewModel = new Review();
        $created = $reviewModel->createReview([
            'appointment_id' => $appointmentId,
            'patient_id' => (int)$patient['id'],
            'doctor_id' => $doctorId,
            'rating' => $rating,
            'review' => $reviewText,
            'is_anonymous' => $isAnonymous ? 1 : 0,
            'is_approved' => 0 // pending
        ]);

        if (!$created) {
            $this->apiError('Review already exists for this appointment', 400);
        }

        // Update doctor rating aggregates
        $this->doctorModel->updateRating($doctorId);

        $this->apiSuccess('Review created successfully', [
            'review' => $created
        ], 201);
    }

    /**
     * Get reviews for a doctor with statistics
     * GET /api/doctors/{id}/reviews
     */
    public function doctorReviews($id) {
        $doctorId = (int)$id;
        if (!$doctorId) { $this->apiError('Doctor ID is required', 422); }
        $page = (int)$this->get('page', 1);

        $reviewModel = new Review();
        $reviews = $reviewModel->getDoctorReviews($doctorId, true, $page);
        $stats = $reviewModel->getDoctorReviewStats($doctorId);

        $this->apiSuccess('Doctor reviews retrieved', [
            'reviews' => $reviews,
            'statistics' => $stats
        ]);
    }

    /**
     * Get a single review by ID
     * GET /api/reviews/{id}
     */
    public function getReviewById($id) {
        $reviewId = (int)$id;
        $reviewModel = new Review();
        $review = $reviewModel->find($reviewId);
        if (!$review) { $this->apiError('Review not found', 404); }
        $this->apiSuccess('Review retrieved', ['review' => $review]);
    }

    /**
     * Update a review (owner patient only)
     * PUT /api/reviews/{id}
     */
    public function updateReview($id) {
        if (!$this->isPut()) {
            $this->apiError('Method not allowed', 405);
        }
        $user = $this->requireApiAuth();
        if ($user['role'] !== ROLE_PATIENT) {
            $this->apiError('Only patients can update reviews', 403);
        }

        $reviewId = (int)$id;
        $reviewModel = new Review();
        $review = $reviewModel->find($reviewId);
        if (!$review) { $this->apiError('Review not found', 404); }

        // Ownership check
        $patient = $this->patientModel->getByUserId($user['id']);
        if (!$patient || (int)$review['patient_id'] !== (int)$patient['id']) {
            $this->apiError('Forbidden', 403);
        }

        $data = $this->getJsonInput();
        $update = [];
        if (isset($data['rating'])) {
            $r = (int)$data['rating'];
            if ($r < 1 || $r > 5) { $this->apiError('Rating must be between 1 and 5', 422); }
            $update['rating'] = $r;
        }
        if (isset($data['review'])) { $update['review'] = trim((string)$data['review']); }

        if (empty($update)) {
            $this->apiError('Nothing to update', 400);
        }

        $reviewModel->update($reviewId, $update);
        // Update doctor rating aggregates
        $this->doctorModel->updateRating((int)$review['doctor_id']);

        $this->apiSuccess('Review updated', ['review' => $reviewModel->find($reviewId)]);
    }

    /**
     * Delete a review (owner patient or super admin)
     * DELETE /api/reviews/{id}
     */
    public function deleteReview($id) {
        $user = $this->requireApiAuth();
        $reviewId = (int)$id;
        $reviewModel = new Review();
        $review = $reviewModel->find($reviewId);
        if (!$review) { $this->apiError('Review not found', 404); }

        $authorized = false;
        if ($user['role'] === ROLE_SUPER_ADMIN) {
            $authorized = true;
        } elseif ($user['role'] === ROLE_PATIENT) {
            $patient = $this->patientModel->getByUserId($user['id']);
            if ($patient && (int)$review['patient_id'] === (int)$patient['id']) {
                $authorized = true;
            }
        }
        if (!$authorized) { $this->apiError('Forbidden', 403); }

        $reviewModel->delete($reviewId);
        // Update doctor rating aggregates
        $this->doctorModel->updateRating((int)$review['doctor_id']);

        $this->apiSuccess('Review deleted');
    }

    // ----- Health Posts API -----

    /**
     * List approved health posts (public)
     * GET /api/health-posts
     */
    public function healthPosts() {
        $page = (int)$this->get('page', 1);
        $perPage = (int)$this->get('per_page', 10);
        $category = $this->get('category');

        $model = new HealthPost();
        $posts = $model->getAllApproved($page, $perPage, $category ?: null);
        $this->apiSuccess('Health posts retrieved', $posts);
    }

    /**
     * Get a health post details (approved only unless owner/admin)
     * GET /api/health-posts/{id}
     */
    public function healthPostDetails($id) {
        $postId = (int)$id;
        $model = new HealthPost();
        $post = $model->getPostWithDetails($postId);
        if (!$post) { $this->apiError('Post not found', 404); }

        if (($post['status'] ?? 'pending') !== 'approved') {
            // Allow only owner doctor or super admin to view non-approved
            $user = $this->requireApiAuth();
            if ($user['role'] === ROLE_SUPER_ADMIN) {
                // ok
            } elseif ($user['role'] === ROLE_DOCTOR) {
                $doctor = $this->doctorModel->getByUserId($user['id']);
                if (!$doctor || (int)$doctor['id'] !== (int)$post['doctor_id']) {
                    $this->apiError('Forbidden', 403);
                }
            } else {
                $this->apiError('Forbidden', 403);
            }
        }

        $this->apiSuccess('Post retrieved', ['post' => $post]);
    }

    /**
     * Create a health post (Doctor only)
     * POST /api/health-posts
     */
    public function createHealthPost() {
        if (!$this->isPost()) { $this->apiError('Method not allowed', 405); }
        $user = $this->requireApiAuth();
        if ($user['role'] !== ROLE_DOCTOR) { $this->apiError('Only doctors can create posts', 403); }

        $doctor = $this->doctorModel->getByUserId($user['id']);
        if (!$doctor) { $this->apiError('Doctor profile not found', 404); }

        $title = trim((string)$this->post('title'));
        $content = trim((string)$this->post('content'));
        $category = trim((string)$this->post('category', '')) ?: null;

        if (!$title || !$content) {
            $this->apiError('Title and content are required', 422);
        }

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->uploadFile($_FILES['image'], 'uploads/health-posts', ['jpg','jpeg','png','webp']);
        }

        $model = new HealthPost();
        $ok = $model->createPost([
            'doctor_id' => (int)$doctor['id'],
            'title' => $title,
            'content' => $content,
            'image_path' => $imagePath,
            'category' => $category,
            'status' => 'pending' // requires admin approval
        ]);

        if ($ok === false) {
            $this->apiError('Failed to create post', 500);
        }

        $this->apiSuccess('Post created and pending approval');
    }

    /**
     * Update a health post (owner doctor only or super admin)
     * PUT /api/health-posts/{id}
     */
    public function updateHealthPost($id) {
        if (!$this->isPut()) { $this->apiError('Method not allowed', 405); }
        $user = $this->requireApiAuth();
        $postId = (int)$id;

        $model = new HealthPost();
        $post = $model->find($postId);
        if (!$post) { $this->apiError('Post not found', 404); }

        $authorized = false;
        if ($user['role'] === ROLE_SUPER_ADMIN) {
            $authorized = true;
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $doctor = $this->doctorModel->getByUserId($user['id']);
            if ($doctor && (int)$doctor['id'] === (int)$post['doctor_id']) { $authorized = true; }
        }
        if (!$authorized) { $this->apiError('Forbidden', 403); }

        $data = $this->getJsonInput();
        $update = [];
        if (isset($data['title'])) { $update['title'] = trim((string)$data['title']); }
        if (isset($data['content'])) { $update['content'] = trim((string)$data['content']); }
        if (isset($data['category'])) { $update['category'] = trim((string)$data['category']) ?: null; }

        // Support optional file upload via multipart (if request came as POST override)
        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->uploadFile($_FILES['image'], 'uploads/health-posts', ['jpg','jpeg','png','webp']);
            if ($imagePath) { $update['image_path'] = $imagePath; }
        }

        if (empty($update)) { $this->apiError('Nothing to update', 400); }

        $model->updatePost($postId, $update);
        $this->apiSuccess('Post updated', ['post' => $model->getPostWithDetails($postId)]);
    }

    /**
     * Delete a health post (owner doctor or super admin)
     * DELETE /api/health-posts/{id}
     */
    public function deleteHealthPost($id) {
        $user = $this->requireApiAuth();
        $postId = (int)$id;
        $model = new HealthPost();
        $post = $model->find($postId);
        if (!$post) { $this->apiError('Post not found', 404); }

        $authorized = false;
        if ($user['role'] === ROLE_SUPER_ADMIN) {
            $authorized = true;
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $doctor = $this->doctorModel->getByUserId($user['id']);
            if ($doctor && (int)$doctor['id'] === (int)$post['doctor_id']) { $authorized = true; }
        }
        if (!$authorized) { $this->apiError('Forbidden', 403); }

        $model->deletePost($postId);
        $this->apiSuccess('Post deleted');
    }

    /**
     * List approved posts for a specific doctor (public)
     * GET /api/doctors/{id}/health-posts
     */
    public function doctorHealthPosts($id) {
        $doctorId = (int)$id;
        if (!$doctorId) { $this->apiError('Doctor ID is required', 422); }
        $page = (int)$this->get('page', 1);
        $perPage = (int)$this->get('per_page', 10);

        $model = new HealthPost();
        $result = $model->getByDoctor($doctorId, $page, $perPage);
        // Filter only approved for public endpoint
        $result['data'] = array_values(array_filter($result['data'], function($p){ return ($p['status'] ?? 'pending') === 'approved'; }));
        $this->apiSuccess('Doctor health posts retrieved', $result);
    }



}
