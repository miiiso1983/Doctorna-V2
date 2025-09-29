<?php
/**
 * HealthPost Model
 * Handles health posts/articles created by doctors
 */

require_once APP_PATH . '/core/Model.php';

class HealthPost extends Model {
    protected $table = 'health_posts';

    /**
     * Create a new health post
     */
    public function createPost($data) {
        $sql = "INSERT INTO {$this->table} 
                (doctor_id, title, content, image_path, category, status) 
                VALUES (:doctor_id, :title, :content, :image_path, :category, :status)";
        
        return $this->query($sql, [
            'doctor_id' => $data['doctor_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'image_path' => $data['image_path'] ?? null,
            'category' => $data['category'] ?? null,
            'status' => $data['status'] ?? 'pending'
        ]);
    }

    /**
     * Get all posts by a specific doctor
     */
    public function getByDoctor($doctorId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT hp.*, u.name as doctor_name, s.name_ar as specialization_name
                FROM {$this->table} hp
                LEFT JOIN doctors d ON hp.doctor_id = d.id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE hp.doctor_id = :doctor_id
                ORDER BY hp.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $posts = $this->fetchRaw($sql, [
            'doctor_id' => $doctorId,
            'limit' => $perPage,
            'offset' => $offset
        ]);

        $total = $this->count('doctor_id = :doctor_id', ['doctor_id' => $doctorId]);

        return [
            'data' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Get all approved posts (for public viewing)
     */
    public function getAllApproved($page = 1, $perPage = 10, $category = null) {
        $offset = ($page - 1) * $perPage;
        
        $conditions = ["hp.status = 'approved'"];
        $params = ['limit' => $perPage, 'offset' => $offset];
        
        if ($category) {
            $conditions[] = "hp.category = :category";
            $params['category'] = $category;
        }
        
        $where = implode(' AND ', $conditions);
        
        $sql = "SELECT hp.*, u.name as doctor_name, u.avatar as doctor_avatar,
                       s.name_ar as specialization_name
                FROM {$this->table} hp
                LEFT JOIN doctors d ON hp.doctor_id = d.id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE {$where}
                ORDER BY hp.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $posts = $this->fetchRaw($sql, $params);

        // Count total
        $countSql = "SELECT COUNT(*) as cnt FROM {$this->table} hp WHERE {$where}";
        $countParams = $category ? ['category' => $category] : [];
        $totalRow = $this->fetchRaw($countSql, $countParams);
        $total = (int)($totalRow[0]['cnt'] ?? 0);

        return [
            'data' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Get all posts for admin (all statuses)
     */
    public function getAllForAdmin($page = 1, $perPage = 10, $status = null) {
        $offset = ($page - 1) * $perPage;
        
        $conditions = [];
        $params = ['limit' => $perPage, 'offset' => $offset];
        
        if ($status) {
            $conditions[] = "hp.status = :status";
            $params['status'] = $status;
        }
        
        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT hp.*, u.name as doctor_name, u.avatar as doctor_avatar,
                       s.name_ar as specialization_name
                FROM {$this->table} hp
                LEFT JOIN doctors d ON hp.doctor_id = d.id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                {$where}
                ORDER BY hp.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $posts = $this->fetchRaw($sql, $params);

        // Count total
        $countSql = "SELECT COUNT(*) as cnt FROM {$this->table} hp {$where}";
        $countParams = $status ? ['status' => $status] : [];
        $totalRow = $this->fetchRaw($countSql, $countParams);
        $total = (int)($totalRow[0]['cnt'] ?? 0);

        return [
            'data' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Get a single post by ID with doctor details
     */
    public function getPostWithDetails($postId) {
        $sql = "SELECT hp.*, u.name as doctor_name, u.avatar as doctor_avatar,
                       s.name_ar as specialization_name
                FROM {$this->table} hp
                LEFT JOIN doctors d ON hp.doctor_id = d.id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                WHERE hp.id = :id
                LIMIT 1";
        
        $result = $this->fetchRaw($sql, ['id' => $postId]);
        return $result[0] ?? null;
    }

    /**
     * Update post status (approve/reject)
     */
    public function updateStatus($postId, $status) {
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            return false;
        }
        
        return $this->update($postId, ['status' => $status]);
    }

    /**
     * Update post content
     */
    public function updatePost($postId, $data) {
        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }
        if (isset($data['image_path'])) {
            $updateData['image_path'] = $data['image_path'];
        }
        if (isset($data['category'])) {
            $updateData['category'] = $data['category'];
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return $this->update($postId, $updateData);
    }

    /**
     * Delete a post
     */
    public function deletePost($postId) {
        return $this->delete($postId);
    }

    /**
     * Get statistics for doctor
     */
    public function getDoctorStats($doctorId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM {$this->table}
                WHERE doctor_id = :doctor_id";
        
        $result = $this->fetchRaw($sql, ['doctor_id' => $doctorId]);
        return $result[0] ?? [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
    }

    /**
     * Get statistics for admin
     */
    public function getAdminStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM {$this->table}";
        
        $result = $this->fetchRaw($sql);
        return $result[0] ?? [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
    }

    /**
     * Get available categories
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT category 
                FROM {$this->table} 
                WHERE category IS NOT NULL AND category != ''
                ORDER BY category";
        
        $result = $this->fetchRaw($sql);
        return array_column($result, 'category');
    }
}

