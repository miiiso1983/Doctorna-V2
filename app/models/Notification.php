<?php
/**
 * Notification Model
 */

require_once APP_PATH . '/core/Model.php';

class Notification extends Model {
    protected $table = 'notifications';
    protected $fillable = ['user_id', 'type', 'title', 'message', 'data', 'is_read'];

    public function createNotification($userId, $type, $title, $message, $data = []) {
        $payload = [
            'user_id' => (int)$userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            'is_read' => 0,
        ];
        return $this->create($payload);
    }

    public function unreadCount($userId) {
        $row = $this->db->fetch("SELECT COUNT(*) AS c FROM {$this->table} WHERE user_id = :u AND is_read = 0", ['u' => $userId]);
        return (int)($row['c'] ?? 0);
    }

    public function getRecent($userId, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :u ORDER BY created_at DESC LIMIT :l";
        return $this->db->fetchAll($sql, ['u' => $userId, 'l' => (int)$limit]);
    }

    public function markAsRead($notificationId, $userId) {
        return $this->update($notificationId, ['is_read' => 1]);
    }
}

