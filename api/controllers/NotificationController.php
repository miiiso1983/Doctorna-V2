<?php
namespace API;

class NotificationController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function list() {
        $userId = \AuthMiddleware::userId();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : API_PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;
        
        $where = "user_id = ?";
        $params = [$userId];
        
        if (isset($_GET['is_read'])) {
            $where .= " AND is_read = ?";
            $params[] = $_GET['is_read'];
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM notifications WHERE $where");
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE $where
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        \Response::paginated($notifications, $total, $page, $limit);
    }
    
    public function markAsRead($id) {
        $userId = \AuthMiddleware::userId();
        
        if (!$id) {
            \Response::error('معرف الإشعار مطلوب', 400);
        }
        
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        
        \Response::success([], SUCCESS_MESSAGES['notification_read']);
    }
    
    public function markAllAsRead() {
        $userId = \AuthMiddleware::userId();
        
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = NOW()
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        
        \Response::success([], 'تم تحديد جميع الإشعارات كمقروءة');
    }
    
    public function unreadCount() {
        $userId = \AuthMiddleware::userId();
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        \Response::success(['count' => (int)$result['count']]);
    }
}

