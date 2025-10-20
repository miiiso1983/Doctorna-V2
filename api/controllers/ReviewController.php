<?php
namespace API;

class ReviewController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get reviews for a doctor
     * GET /api/reviews/doctor/{doctor_id}
     */
    public function getDoctorReviews($doctorId) {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM reviews 
                WHERE doctor_id = ?
            ");
            $countStmt->execute([$doctorId]);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Get reviews
            $stmt = $this->db->prepare("
                SELECT r.*, 
                       u.name as patient_name,
                       u.avatar as patient_avatar
                FROM reviews r
                INNER JOIN patients p ON r.patient_id = p.id
                INNER JOIN users u ON p.user_id = u.id
                WHERE r.doctor_id = ?
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$doctorId, $limit, $offset]);
            $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            Response::success([
                'data' => $reviews,
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);
            
        } catch (\Exception $e) {
            Response::error('فشل في جلب التقييمات: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get doctor rating summary
     * GET /api/reviews/doctor/{doctor_id}/summary
     */
    public function getDoctorRatingSummary($doctorId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_stars,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_stars,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_stars,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_stars,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM reviews
                WHERE doctor_id = ?
            ");
            $stmt->execute([$doctorId]);
            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            Response::success($summary);
            
        } catch (\Exception $e) {
            Response::error('فشل في جلب ملخص التقييمات: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Add a review
     * POST /api/reviews/add
     */
    public function addReview() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate
            if (!isset($data['doctor_id']) || !isset($data['rating'])) {
                Response::error('معرف الطبيب والتقييم مطلوبان', 400);
                return;
            }
            
            $doctorId = (int)$data['doctor_id'];
            $rating = (int)$data['rating'];
            $comment = isset($data['comment']) ? trim($data['comment']) : null;
            
            // Validate rating
            if ($rating < 1 || $rating > 5) {
                Response::error('التقييم يجب أن يكون بين 1 و 5', 400);
                return;
            }
            
            // Get patient ID from user
            $userId = $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT id FROM patients WHERE user_id = ?");
            $stmt->execute([$userId]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$patient) {
                Response::error('المستخدم ليس مريضاً', 403);
                return;
            }
            
            $patientId = $patient['id'];
            
            // Check if patient has completed appointment with this doctor
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM appointments 
                WHERE patient_id = ? 
                AND doctor_id = ? 
                AND status = 'completed'
            ");
            $stmt->execute([$patientId, $doctorId]);
            $hasAppointment = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] > 0;
            
            if (!$hasAppointment) {
                Response::error('يجب أن يكون لديك موعد مكتمل مع الطبيب لتقييمه', 403);
                return;
            }
            
            // Check if already reviewed
            $stmt = $this->db->prepare("
                SELECT id FROM reviews 
                WHERE patient_id = ? AND doctor_id = ?
            ");
            $stmt->execute([$patientId, $doctorId]);
            $existingReview = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existingReview) {
                // Update existing review
                $stmt = $this->db->prepare("
                    UPDATE reviews 
                    SET rating = ?, comment = ?, created_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$rating, $comment, $existingReview['id']]);
                $reviewId = $existingReview['id'];
                $message = 'تم تحديث التقييم بنجاح';
            } else {
                // Insert new review
                $stmt = $this->db->prepare("
                    INSERT INTO reviews (doctor_id, patient_id, rating, comment, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$doctorId, $patientId, $rating, $comment]);
                $reviewId = $this->db->lastInsertId();
                $message = 'تم إضافة التقييم بنجاح';
            }
            
            // Update doctor's average rating
            $this->updateDoctorRating($doctorId);
            
            Response::success([
                'id' => $reviewId,
                'message' => $message
            ], 201);
            
        } catch (\Exception $e) {
            Response::error('فشل في إضافة التقييم: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete a review
     * POST /api/reviews/delete/{id}
     */
    public function deleteReview($reviewId) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Get patient ID
            $stmt = $this->db->prepare("SELECT id FROM patients WHERE user_id = ?");
            $stmt->execute([$userId]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$patient) {
                Response::error('المستخدم ليس مريضاً', 403);
                return;
            }
            
            // Check if review belongs to user
            $stmt = $this->db->prepare("
                SELECT doctor_id FROM reviews 
                WHERE id = ? AND patient_id = ?
            ");
            $stmt->execute([$reviewId, $patient['id']]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$review) {
                Response::error('التقييم غير موجود أو لا تملك صلاحية حذفه', 404);
                return;
            }
            
            $doctorId = $review['doctor_id'];
            
            // Delete review
            $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$reviewId]);
            
            // Update doctor's average rating
            $this->updateDoctorRating($doctorId);
            
            Response::success(['message' => 'تم حذف التقييم بنجاح']);
            
        } catch (\Exception $e) {
            Response::error('فشل في حذف التقييم: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get user's review for a doctor
     * GET /api/reviews/my-review/{doctor_id}
     */
    public function getMyReview($doctorId) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Get patient ID
            $stmt = $this->db->prepare("SELECT id FROM patients WHERE user_id = ?");
            $stmt->execute([$userId]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$patient) {
                Response::success(['review' => null]);
                return;
            }
            
            // Get review
            $stmt = $this->db->prepare("
                SELECT * FROM reviews 
                WHERE doctor_id = ? AND patient_id = ?
            ");
            $stmt->execute([$doctorId, $patient['id']]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            Response::success(['review' => $review]);
            
        } catch (\Exception $e) {
            Response::error('فشل في جلب التقييم: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update doctor's average rating
     */
    private function updateDoctorRating($doctorId) {
        $stmt = $this->db->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
            FROM reviews
            WHERE doctor_id = ?
        ");
        $stmt->execute([$doctorId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $avgRating = $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
        $totalReviews = $result['total_reviews'];
        
        $stmt = $this->db->prepare("
            UPDATE doctors 
            SET rating = ?, total_reviews = ?
            WHERE id = ?
        ");
        $stmt->execute([$avgRating, $totalReviews, $doctorId]);
    }
}

