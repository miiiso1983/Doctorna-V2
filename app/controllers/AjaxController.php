<?php
/**
 * Ajax Controller
 * Handles asynchronous endpoints (symptoms recommendation, availability checks, etc.)
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/Specialization.php';

class AjaxController extends Controller {
    private $specializationModel;

    public function __construct() {
        parent::__construct();
        $this->specializationModel = new Specialization();
    }

    /**
     * Notifications: check unread and recent
     * GET /notifications/check
     */
    public function notificationsCheck() {
        try {
            $userId = $this->auth->id();
            if (!$userId) {
                return $this->success('ok', [
                    'count' => 0,
                    'notifications' => []
                ]);
            }
            require_once APP_PATH . '/models/Notification.php';
            $model = new Notification();
            $count = $model->unreadCount($userId);
            $recent = $model->getRecent($userId, 5);
            return $this->success('ok', [
                'count' => $count,
                'notifications' => array_map(function($n){
                    return [
                        'id' => (int)$n['id'],
                        'title' => $n['title'] ?? '',
                        'message' => $n['message'] ?? '',
                        'is_read' => (int)($n['is_read'] ?? 0),
                        'created_at' => $n['created_at'] ?? null
                    ];
                }, $recent)
            ]);
        } catch (\Throwable $e) {
            return $this->error('failed', 500);
        }
    }

    /**
     * Notifications: mark as read
     * POST /notifications/{id}/read
     */
    public function readNotification($id) {
        try {
            $userId = $this->auth->id();
            if (!$userId) { return $this->error('غير مصرح', 401); }
            require_once APP_PATH . '/models/Notification.php';
            $model = new Notification();
            $model->markAsRead((int)$id, $userId);
            return $this->success('تم التحديث', []);
        } catch (\Throwable $e) {
            return $this->error('failed', 500);
        }
    }


    /**
     * Recommend specialization(s) based on symptoms text or IDs
     * GET /ajax/symptoms/recommend?q=... or &ids=1,2,3
     */
    public function recommendSpecialization() {
        $query = trim($this->get('q', ''));
        $idsParam = trim($this->get('ids', ''));

        $symptomIds = [];

        // If ids provided, parse them
        if ($idsParam !== '') {
            $symptomIds = array_values(array_filter(array_map('intval', explode(',', $idsParam))));
        }

        // If only query provided, try to map top matching symptoms to IDs
        if (empty($symptomIds) && $query !== '') {
            $symptomModel = new Symptom();
            $symptoms = $symptomModel->searchSymptoms($query);
            $symptomIds = array_map(function($s){ return (int)$s['id']; }, array_slice($symptoms, 0, 5));
        }

        if (empty($symptomIds)) {
            return $this->success('لا توجد أعراض كافية للاقتراح', [
                'recommendations' => []
            ]);
        }

        $recs = $this->specializationModel->getRecommendedSpecializations($symptomIds);

        // Format for frontend
        $recommendations = array_map(function($s){
            return [
                'id' => (int)$s['id'],
                'name' => $s['name'],
                'icon' => $s['icon'],
                'color' => $s['color'],
                'score' => isset($s['avg_relevance']) ? (float)$s['avg_relevance'] : null,
                'matching_symptoms' => isset($s['matching_symptoms']) ? (int)$s['matching_symptoms'] : null,
            ];
        }, $recs);

        return $this->success('تم توليد التوصيات', [
            'recommendations' => $recommendations
        ]);
    }

    // ----- Appointments: accept/reject/check availability -----

    /**
     * Accept appointment (Doctor only)
     * POST /ajax/appointment/accept
     */
    public function acceptAppointment() {
        if (!$this->isPost()) {
            return $this->error('طريقة الطلب غير صحيحة', 405);
        }
        $this->validateCSRF();
        // Require doctor role (returns JSON error for AJAX if unauthorized)
        $this->requireRole(ROLE_DOCTOR);

        $appointmentId = (int)$this->post('appointment_id');
        if (!$appointmentId) {
            return $this->error('رقم الموعد مطلوب', 422);
        }

        require_once APP_PATH . '/models/Appointment.php';
        require_once APP_PATH . '/models/Doctor.php';
        require_once APP_PATH . '/models/Patient.php';
        require_once APP_PATH . '/models/Notification.php';

        $appointmentModel = new Appointment();
        $doctorModel = new Doctor();
        $patientModel = new Patient();
        $notificationModel = new Notification();

        // Current doctor by user
        $userId = $this->auth->id();
        $doctor = $doctorModel->getByUserId($userId);
        if (!$doctor) {
            return $this->error('حساب الطبيب غير موجود', 404);
        }

        // Verify appointment belongs to this doctor
        $appointment = $appointmentModel->getAppointmentDetails($appointmentId);
        if (!$appointment || (int)$appointment['doctor_id'] !== (int)$doctor['id']) {
            return $this->error('الموعد غير موجود أو غير مخول لك', 403);
        }

        if ($appointmentModel->updateStatus($appointmentId, APPOINTMENT_CONFIRMED)) {
            // Notify patient
            $patient = $patientModel->find((int)$appointment['patient_id']);
            if ($patient && !empty($patient['user_id'])) {
                $notificationModel->createNotification(
                    (int)$patient['user_id'],
                    'appointment_confirmed',
                    'تم قبول موعدك',
                    'تم قبول موعدك مع الطبيب ' . ($appointment['doctor_name'] ?? ''),
                    ['appointment_id' => $appointmentId]
                );
            }
            return $this->success('تم قبول الموعد بنجاح');
        }

        return $this->error('حدث خطأ أثناء قبول الموعد', 500);
    }

    /**
     * Reject appointment (Doctor only)
     * POST /ajax/appointment/reject
     */
    public function rejectAppointment() {
        if (!$this->isPost()) {
            return $this->error('طريقة الطلب غير صحيحة', 405);
        }
        $this->validateCSRF();
        $this->requireRole(ROLE_DOCTOR);

        $appointmentId = (int)$this->post('appointment_id');
        $reason = trim((string)$this->post('reason', ''));
        if (!$appointmentId) {
            return $this->error('رقم الموعد مطلوب', 422);
        }

        require_once APP_PATH . '/models/Appointment.php';
        require_once APP_PATH . '/models/Doctor.php';
        require_once APP_PATH . '/models/Patient.php';
        require_once APP_PATH . '/models/Notification.php';
        require_once APP_PATH . '/services/Realtime.php';

        $appointmentModel = new Appointment();
        $doctorModel = new Doctor();
        $patientModel = new Patient();
        $notificationModel = new Notification();

        $userId = $this->auth->id();
        $doctor = $doctorModel->getByUserId($userId);
        if (!$doctor) {
            return $this->error('حساب الطبيب غير موجود', 404);
        }

        $appointment = $appointmentModel->getAppointmentDetails($appointmentId);
        if (!$appointment || (int)$appointment['doctor_id'] !== (int)$doctor['id']) {
            return $this->error('الموعد غير موجود أو غير مخول لك', 403);
        }

        if ($appointmentModel->updateStatus($appointmentId, APPOINTMENT_CANCELLED, $reason ?: null)) {
            // Notify patient
            $patient = $patientModel->find((int)$appointment['patient_id']);
            if ($patient && !empty($patient['user_id'])) {
                $notificationModel->createNotification(
                    (int)$patient['user_id'],
                    'appointment_rejected',
                    'تم رفض الموعد',
                    ($reason ? ('السبب: ' . $reason) : 'تم رفض الموعد من قبل الطبيب'),
                    ['appointment_id' => $appointmentId]
                );
            }
            return $this->success('تم رفض الموعد');
        }

        return $this->error('حدث خطأ أثناء رفض الموعد', 500);
    }

    /**
     * Check doctor availability or list available slots
     * POST /ajax/doctor/availability
     * params: doctor_id, date (Y-m-d), [time (H:i or H:i:s)]
     */
    public function checkAvailability() {
        // Allow GET or POST
        $doctorId = (int)($this->post('doctor_id') ?: $this->get('doctor_id'));
        $date = $this->post('date') ?: $this->get('date');
        $time = $this->post('time') ?: $this->get('time');

        if (!$doctorId || !$date) {
            return $this->error('معرّف الطبيب والتاريخ مطلوبان', 422);
        }

        require_once APP_PATH . '/models/Doctor.php';
        $doctorModel = new Doctor();

        // If time provided, return a boolean availability; else return slots
        if ($time) {
            // Normalize time to H:i:s
            $t = date('H:i:s', strtotime($time));
            $slots = $doctorModel->getAvailableSlots($doctorId, $date);
            $available = in_array($t, $slots, true);
            return $this->success('التحقق من التوفر', [
                'available' => $available,
                'time' => $t,
                'date' => $date
            ]);
        }

        $slots = $doctorModel->getAvailableSlots($doctorId, $date);
        return $this->success('الأوقات المتاحة', [
            'date' => $date,
            'slots' => array_values($slots)
        ]);
    }

    // ----- Chat (AJAX, session auth) -----

    /**
     * GET /ajax/chats/{appointment_id}
     */
    public function chatMessages($appointmentId) {
        $this->requireAuth();
        require_once APP_PATH . '/models/Appointment.php';
        require_once APP_PATH . '/models/ChatMessage.php';
        require_once APP_PATH . '/models/Doctor.php';
        require_once APP_PATH . '/models/Patient.php';

        $appointmentModel = new Appointment();
        $chat = new ChatMessage();
        $doctorModel = new Doctor();
        $patientModel = new Patient();

        $appt = $appointmentModel->find((int)$appointmentId);
        if (!$appt) { return $this->error('الموعد غير موجود', 404); }

        $user = $this->auth->user();
        $authorized = false;
        if ($user['role'] === ROLE_SUPER_ADMIN) { $authorized = true; }
        elseif ($user['role'] === ROLE_PATIENT) {
            $patient = $patientModel->getByUserId($user['id']);
            if ($patient && (int)$patient['id'] === (int)$appt['patient_id']) { $authorized = true; }
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $doctor = $doctorModel->getByUserId($user['id']);
            if ($doctor && (int)$doctor['id'] === (int)$appt['doctor_id']) { $authorized = true; }
        }
        if (!$authorized) { return $this->error('غير مصرح', 403); }

        $page = (int)($this->get('page', 1));
        $perPage = (int)($this->get('per_page', 50));
        $messages = $chat->getMessages((int)$appointmentId, $page, $perPage);
        $chat->markAsReadForRecipient((int)$appointmentId, (int)$user['id']);
        return $this->success('ok', $messages);
    }

    /**
     * POST /ajax/chats/send
     */
    public function sendChatMessage() {
        if (!$this->isPost()) { return $this->error('طريقة الطلب غير صحيحة', 405); }
        $this->validateCSRF();
        $this->requireAuth();

        require_once APP_PATH . '/models/Appointment.php';
        require_once APP_PATH . '/models/ChatMessage.php';
        require_once APP_PATH . '/models/Notification.php';
        require_once APP_PATH . '/models/Doctor.php';
        require_once APP_PATH . '/models/Patient.php';
        require_once APP_PATH . '/services/Realtime.php';

        $appointmentId = (int)$this->post('appointment_id');
        $message = trim((string)$this->post('message'));
        if (!$appointmentId || $message === '') { return $this->error('حقول ناقصة', 422); }

        $appointmentModel = new Appointment();
        $appt = $appointmentModel->getAppointmentDetails($appointmentId);
        if (!$appt) { return $this->error('الموعد غير موجود', 404); }

        $user = $this->auth->user();
        $doctorModel = new Doctor();
        $patientModel = new Patient();
        if ($user['role'] === ROLE_PATIENT) {
            $patient = $patientModel->getByUserId($user['id']);
            if (!$patient || (int)$patient['id'] !== (int)$appt['patient_id']) { return $this->error('غير مصرح', 403); }
            $recipientUserId = (int)($doctorModel->find((int)$appt['doctor_id'])['user_id'] ?? 0);
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $doctor = $doctorModel->getByUserId($user['id']);
            if (!$doctor || (int)$doctor['id'] !== (int)$appt['doctor_id']) { return $this->error('غير مصرح', 403); }
            $recipientUserId = (int)($patientModel->find((int)$appt['patient_id'])['user_id'] ?? 0);
        } else { return $this->error('غير مصرح', 403); }
        if (!$recipientUserId) { return $this->error('تعذر تحديد المستلم', 404); }

        $chat = new ChatMessage();
        $msgId = $chat->createMessage([
            'appointment_id' => $appointmentId,
            'sender_user_id' => (int)$user['id'],
            'recipient_user_id' => $recipientUserId,
            'message' => $message
        ]);
        if (!$msgId) { return $this->error('فشل الإرسال', 500); }

        $notif = new Notification();
        $notif->createNotification($recipientUserId, 'chat_message', 'رسالة جديدة', 'لديك رسالة جديدة بخصوص الموعد #'.$appointmentId, ['appointment_id' => $appointmentId]);
        // Realtime push (no-op if REALTIME_DRIVER=none)
        $rt = new Realtime();
        $rt->publish('user-' . $recipientUserId, 'chat_message', [
            'appointment_id' => (int)$appointmentId,
            'sender_user_id' => (int)$user['id'],
            'recipient_user_id' => (int)$recipientUserId,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->success('تم الإرسال', ['message_id' => (int)$msgId]);
    }

    /**
     * POST /ajax/chats/{appointment_id}/read
     */
    public function readChat($appointmentId) {
        $this->requireAuth();
        require_once APP_PATH . '/models/ChatMessage.php';
        $chat = new ChatMessage();
        $chat->markAsReadForRecipient((int)$appointmentId, (int)$this->auth->id());
        return $this->success('ok');
    }

    // ----- Video (AJAX, session auth) -----

    /**
     * POST /ajax/video/rooms
     */
    public function createVideoRoom() {
        if (!$this->isPost()) { return $this->error('طريقة الطلب غير صحيحة', 405); }
        $this->validateCSRF();
        $this->requireAuth();

        require_once APP_PATH . '/models/VideoCall.php';
        require_once APP_PATH . '/models/Appointment.php';
        require_once APP_PATH . '/models/Notification.php';
        require_once APP_PATH . '/models/Doctor.php';
        require_once APP_PATH . '/models/Patient.php';
        require_once APP_PATH . '/services/Realtime.php';

        $appointmentId = (int)$this->post('appointment_id');
        if (!$appointmentId) { return $this->error('رقم الموعد مطلوب', 422); }
        $appointmentModel = new Appointment();
        $appt = $appointmentModel->find($appointmentId);
        if (!$appt) { return $this->error('الموعد غير موجود', 404); }

        $user = $this->auth->user();
        $authorized = false;
        $doctorModel = new Doctor();
        $patientModel = new Patient();
        if ($user['role'] === ROLE_PATIENT) {
            $patient = $patientModel->getByUserId($user['id']);
            if ($patient && (int)$patient['id'] === (int)$appt['patient_id']) { $authorized = true; }
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $doctor = $doctorModel->getByUserId($user['id']);
            if ($doctor && (int)$doctor['id'] === (int)$appt['doctor_id']) { $authorized = true; }
        } elseif ($user['role'] === ROLE_SUPER_ADMIN) { $authorized = true; }
        if (!$authorized) { return $this->error('غير مصرح', 403); }

        $vc = new VideoCall();
        $existing = $vc->findByAppointment($appointmentId);
        if ($existing) { return $this->success('تم الاسترجاع', ['room' => $existing]); }

        $roomCode = 'VC-' . $appointmentId . '-' . bin2hex(random_bytes(4));
        $roomId = $vc->createRoom($appointmentId, (int)$user['id'], $roomCode);
        if (!$roomId) { return $this->error('فشل إنشاء الغرفة', 500); }

        // Notify counterpart
        $recipientUserId = null;
        if ($user['role'] === ROLE_PATIENT) {
            $recipientUserId = (int)($doctorModel->find((int)$appt['doctor_id'])['user_id'] ?? 0);
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $recipientUserId = (int)($patientModel->find((int)$appt['patient_id'])['user_id'] ?? 0);
        }
        if ($recipientUserId) {
            $n = new Notification();
            $n->createNotification($recipientUserId, 'video_call', 'دعوة مكالمة فيديو', 'هناك مكالمة فيديو للموعد #'.$appointmentId, ['appointment_id'=>$appointmentId,'room_code'=>$roomCode]);
        }
        // Realtime push to counterpart and appointment channel
        $rt = new Realtime();
        $rt->publish('user-' . ($recipientUserId ?: (int)$user['id']), 'video_room', [
            'appointment_id' => (int)$appointmentId,
            'room_code' => $roomCode,
            'status' => 'scheduled'
        ]);
        $rt->publish('appointment-' . (int)$appointmentId, 'video_room', [
            'appointment_id' => (int)$appointmentId,
            'room_code' => $roomCode,
            'status' => 'scheduled'
        ]);

        return $this->success('تم الإنشاء', ['room' => $vc->find($roomId)]);
    }

    /**
     * GET /ajax/video/rooms/{appointment_id}
     */
    public function getVideoRoom($appointmentId) {
        $this->requireAuth();
        require_once APP_PATH . '/models/VideoCall.php';
        $vc = new VideoCall();
        $room = $vc->findByAppointment((int)$appointmentId);
        if (!$room) { return $this->error('غير موجود', 404); }
        return $this->success('ok', ['room' => $room]);
    }

    /**
     * POST /ajax/video/rooms/{appointment_id}/status
     */
    public function updateVideoRoomStatus($appointmentId) {
        if (!$this->isPost()) { return $this->error('طريقة الطلب غير صحيحة', 405); }
        $this->validateCSRF();
        $this->requireAuth();
        require_once APP_PATH . '/models/VideoCall.php';
        require_once APP_PATH . '/services/Realtime.php';
        $status = strtolower(trim((string)$this->post('status','')));
        $vc = new VideoCall();
        $room = $vc->findByAppointment((int)$appointmentId);
        if (!$room) { return $this->error('غير موجود', 404); }
        if ($status === 'ongoing') { $vc->markStarted((int)$room['id']); }
        elseif (in_array($status, ['ended','cancelled'])) { $vc->markEnded((int)$room['id']); }
        else { return $this->error('حالة غير صحيحة', 422); }
        // Realtime push
        $rt = new Realtime();
        $rt->publish('appointment-' . (int)$appointmentId, 'video_status', [
            'appointment_id' => (int)$appointmentId,
            'status' => $status
        ]);
        return $this->success('تم التحديث', ['room' => $vc->find((int)$room['id'])]);
    }




}
