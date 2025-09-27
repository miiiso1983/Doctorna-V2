<?php
/**
 * Specialization Model
 */

require_once APP_PATH . '/core/Model.php';

class Specialization extends Model {
    protected $table = 'specializations';
    protected $fillable = [
        'name', 'name_en', 'description', 'icon', 'color', 'is_active', 'sort_order'
    ];
    
    /**
     * Get all active specializations
     */
    public function getActiveSpecializations() {
        return $this->where('is_active = 1', [], 'sort_order ASC, name ASC');
    }
    
    /**
     * Get specializations with doctor count
     */
    public function getSpecializationsWithDoctorCount() {
        $sql = "SELECT s.*, COUNT(d.id) as doctor_count
                FROM {$this->table} s
                LEFT JOIN doctors d ON s.id = d.specialization_id AND d.status = 'approved'
                WHERE s.is_active = 1
                GROUP BY s.id
                ORDER BY s.sort_order ASC, s.name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get recommended specializations based on symptoms
     */
    public function getRecommendedSpecializations($symptomIds) {
        if (empty($symptomIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($symptomIds), '?'));
        
        $sql = "SELECT s.*, AVG(ss.relevance_score) as avg_relevance,
                       COUNT(ss.symptom_id) as matching_symptoms
                FROM {$this->table} s
                INNER JOIN symptom_specializations ss ON s.id = ss.specialization_id
                WHERE ss.symptom_id IN ({$placeholders})
                AND s.is_active = 1
                GROUP BY s.id
                ORDER BY avg_relevance DESC, matching_symptoms DESC
                LIMIT 5";
        
        return $this->db->fetchAll($sql, $symptomIds);
    }
    
    /**
     * Search specializations
     */
    public function searchSpecializations($query) {
        $conditions = 'is_active = 1 AND (name LIKE :query OR name_en LIKE :query OR description LIKE :query)';
        $params = ['query' => "%{$query}%"];
        
        return $this->where($conditions, $params, 'sort_order ASC, name ASC');
    }
    
    /**
     * Get specialization statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stats['total_specializations'] = $this->count();
        $stats['active_specializations'] = $this->count('is_active = 1');
        $stats['inactive_specializations'] = $this->count('is_active = 0');
        
        // Most popular specializations (by doctor count)
        $popularSql = "SELECT s.name, COUNT(d.id) as doctor_count
                       FROM {$this->table} s
                       LEFT JOIN doctors d ON s.id = d.specialization_id AND d.status = 'approved'
                       WHERE s.is_active = 1
                       GROUP BY s.id
                       ORDER BY doctor_count DESC
                       LIMIT 5";
        
        $stats['popular_specializations'] = $this->db->fetchAll($popularSql);
        
        return $stats;
    }
    
    /**
     * Update sort order
     */
    public function updateSortOrder($specializationId, $sortOrder) {
        return $this->update($specializationId, ['sort_order' => $sortOrder]);
    }
    
    /**
     * Toggle active status
     */
    public function toggleStatus($specializationId) {
        $specialization = $this->find($specializationId);
        if (!$specialization) {
            return false;
        }
        
        $newStatus = $specialization['is_active'] ? 0 : 1;
        return $this->update($specializationId, ['is_active' => $newStatus]);
    }
}

/**
 * Symptom Model
 */
class Symptom extends Model {
    protected $table = 'symptoms';
    protected $fillable = [
        'name', 'name_en', 'description', 'category', 'severity_level', 'is_active'
    ];
    
    /**
     * Get all active symptoms
     */
    public function getActiveSymptoms() {
        return $this->where('is_active = 1', [], 'category ASC, name ASC');
    }
    
    /**
     * Get symptoms by category
     */
    public function getSymptomsByCategory($category = null) {
        if ($category) {
            return $this->where('category = :category AND is_active = 1', ['category' => $category], 'name ASC');
        }
        
        $sql = "SELECT category, GROUP_CONCAT(name ORDER BY name ASC) as symptoms
                FROM {$this->table}
                WHERE is_active = 1
                GROUP BY category
                ORDER BY category ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Search symptoms
     */
    public function searchSymptoms($query) {
        $conditions = 'is_active = 1 AND (name LIKE :query OR name_en LIKE :query OR description LIKE :query)';
        $params = ['query' => "%{$query}%"];
        
        return $this->where($conditions, $params, 'name ASC');
    }
    
    /**
     * Get symptom categories
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM {$this->table} WHERE is_active = 1 ORDER BY category ASC";
        return array_column($this->db->fetchAll($sql), 'category');
    }
}

/**
 * Review Model
 */
class Review extends Model {
    protected $table = 'reviews';
    protected $fillable = [
        'appointment_id', 'patient_id', 'doctor_id', 'rating', 'review',
        'is_anonymous', 'is_approved'
    ];
    
    /**
     * Get doctor reviews
     */
    public function getDoctorReviews($doctorId, $approved = true, $page = 1) {
        $conditions = ['doctor_id = :doctor_id'];
        $params = ['doctor_id' => $doctorId];
        
        if ($approved) {
            $conditions[] = 'is_approved = 1';
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT r.*, p.name as patient_name, p.avatar as patient_avatar
                FROM {$this->table} r
                LEFT JOIN patients pt ON r.patient_id = pt.id
                LEFT JOIN users p ON pt.user_id = p.id
                WHERE {$whereClause}
                ORDER BY r.created_at DESC";
        
        return $this->paginate($page, ITEMS_PER_PAGE, $whereClause, $params, 'r.created_at DESC');
    }
    
    /**
     * Get review statistics for a doctor
     */
    public function getDoctorReviewStats($doctorId) {
        $sql = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM {$this->table}
                WHERE doctor_id = :doctor_id AND is_approved = 1";
        
        return $this->db->fetch($sql, ['doctor_id' => $doctorId]);
    }
    
    /**
     * Create review
     */
    public function createReview($data) {
        // Check if patient already reviewed this appointment
        if ($this->exists('appointment_id = :appointment_id', ['appointment_id' => $data['appointment_id']])) {
            return false;
        }
        
        return $this->create($data);
    }
    
    /**
     * Approve review
     */
    public function approveReview($reviewId) {
        return $this->update($reviewId, ['is_approved' => 1]);
    }
    
    /**
     * Get pending reviews
     */
    public function getPendingReviews($page = 1) {
        return $this->paginate($page, ITEMS_PER_PAGE, 'is_approved = 0', [], 'created_at DESC');
    }
}
