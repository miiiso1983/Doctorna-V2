<?php
namespace API;

class HealthPostController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function list() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : API_PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;
        
        $where = ["hp.status = 'approved'"];
        $params = [];
        
        if (isset($_GET['category'])) {
            $where[] = "hp.category = ?";
            $params[] = $_GET['category'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM health_posts hp WHERE $whereClause");
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->prepare("
            SELECT hp.*, 
                   d.user_id as doctor_user_id,
                   u.name as author_name, u.avatar as author_avatar,
                   s.name as specialization_name
            FROM health_posts hp
            INNER JOIN doctors d ON hp.doctor_id = d.id
            INNER JOIN users u ON d.user_id = u.id
            LEFT JOIN specializations s ON d.specialization_id = s.id
            WHERE $whereClause
            ORDER BY hp.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        \Response::paginated($posts, $total, $page, $limit);
    }
    
    public function details($id) {
        if (!$id) {
            \Response::error('معرف المنشور مطلوب', 400);
        }
        
        $stmt = $this->db->prepare("
            SELECT hp.*, 
                   d.user_id as doctor_user_id,
                   u.name as author_name, u.avatar as author_avatar, u.email as author_email,
                   d.specialization_id, d.experience_years, d.biography,
                   s.name as specialization_name
            FROM health_posts hp
            INNER JOIN doctors d ON hp.doctor_id = d.id
            INNER JOIN users u ON d.user_id = u.id
            LEFT JOIN specializations s ON d.specialization_id = s.id
            WHERE hp.id = ?
        ");
        $stmt->execute([$id]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$post) {
            \Response::notFound('المنشور غير موجود');
        }
        
        // Increment views
        $stmt = $this->db->prepare("UPDATE health_posts SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        \Response::success($post);
    }
    
    public function create() {
        \AuthMiddleware::requireRole('doctor');
        
        $userId = \AuthMiddleware::userId();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $validator = new \Validator($data);
        $validator->required(['title', 'content', 'category']);
        
        if ($validator->fails()) {
            \Response::validationError($validator->errors());
        }
        
        // Get doctor ID
        $stmt = $this->db->prepare("SELECT id FROM doctors WHERE user_id = ?");
        $stmt->execute([$userId]);
        $doctor = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $stmt = $this->db->prepare("
            INSERT INTO health_posts (doctor_id, title, content, category, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $doctor['id'],
            $data['title'],
            $data['content'],
            $data['category']
        ]);
        
        $postId = $this->db->lastInsertId();
        
        \Response::success(['id' => $postId], SUCCESS_MESSAGES['post_created'], 201);
    }
    
    public function update($id) {
        \AuthMiddleware::requireRole('doctor');
        
        if (!$id) {
            \Response::error('معرف المنشور مطلوب', 400);
        }
        
        $userId = \AuthMiddleware::userId();
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Check ownership
        $stmt = $this->db->prepare("
            SELECT hp.* FROM health_posts hp
            INNER JOIN doctors d ON hp.doctor_id = d.id
            WHERE hp.id = ? AND d.user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$post) {
            \Response::forbidden('ليس لديك صلاحية لتعديل هذا المنشور');
        }
        
        $updates = [];
        $params = [];
        
        foreach (['title', 'content', 'category'] as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($updates)) {
            $params[] = $id;
            $sql = "UPDATE health_posts SET " . implode(', ', $updates) . ", status = 'pending' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
        
        \Response::success([], SUCCESS_MESSAGES['post_updated']);
    }
    
    public function delete($id) {
        \AuthMiddleware::requireRole('doctor');
        
        if (!$id) {
            \Response::error('معرف المنشور مطلوب', 400);
        }
        
        $userId = \AuthMiddleware::userId();
        
        // Check ownership
        $stmt = $this->db->prepare("
            SELECT hp.* FROM health_posts hp
            INNER JOIN doctors d ON hp.doctor_id = d.id
            WHERE hp.id = ? AND d.user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$post) {
            \Response::forbidden('ليس لديك صلاحية لحذف هذا المنشور');
        }
        
        $stmt = $this->db->prepare("DELETE FROM health_posts WHERE id = ?");
        $stmt->execute([$id]);
        
        \Response::success([], SUCCESS_MESSAGES['post_deleted']);
    }
}

