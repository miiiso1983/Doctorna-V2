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

    // Stubs for endpoints referenced in routes; implement as needed
    public function acceptAppointment() { return $this->error('غير مُنفّذ', 501); }
    public function rejectAppointment() { return $this->error('غير مُنفّذ', 501); }
    public function checkAvailability() { return $this->error('غير مُنفّذ', 501); }
}

