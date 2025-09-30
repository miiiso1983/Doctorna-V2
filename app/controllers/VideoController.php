<?php
/**
 * Video Controller - renders the WebRTC room page (session auth)
 */
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Patient.php';

class VideoController extends Controller {
    public function room($appointmentId) {
        $this->requireAuth();
        $appointmentId = (int)$appointmentId;
        $appointmentModel = new Appointment();
        $appt = $appointmentModel->find($appointmentId);
        if (!$appt) { return $this->renderError(404, 'الموعد غير موجود'); }

        $user = $this->auth->user();
        $authorized = false;
        if ($user['role'] === ROLE_SUPER_ADMIN) { $authorized = true; }
        elseif ($user['role'] === ROLE_PATIENT) {
            $patient = (new Patient())->getByUserId($user['id']);
            if ($patient && (int)$patient['id'] === (int)$appt['patient_id']) { $authorized = true; }
        } elseif ($user['role'] === ROLE_DOCTOR) {
            $doctor = (new Doctor())->getByUserId($user['id']);
            if ($doctor && (int)$doctor['id'] === (int)$appt['doctor_id']) { $authorized = true; }
        }
        if (!$authorized) { return $this->renderError(403, 'غير مصرح'); }

        $title = 'غرفة المكالمة';
        $this->view('video/room', compact('title','appointmentId','appt'));
    }
}

