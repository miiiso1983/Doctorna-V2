<?php
/**
 * ChatMessage Model
 * Stores messages exchanged between patient and doctor per appointment
 */

require_once APP_PATH . '/core/Model.php';

class ChatMessage extends Model {
    protected $table = 'chat_messages';
    protected $fillable = [
        'appointment_id', 'sender_user_id', 'recipient_user_id', 'message', 'is_read'
    ];

    public function createMessage($data) {
        $data['is_read'] = $data['is_read'] ?? 0;
        return $this->create($data);
    }

    public function getMessages($appointmentId, $page = 1, $perPage = 50) {
        return $this->paginate($page, $perPage, 'appointment_id = :a', ['a' => (int)$appointmentId], 'id ASC');
    }

    public function markAsReadForRecipient($appointmentId, $recipientUserId) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE appointment_id = :a AND recipient_user_id = :u AND is_read = 0";
        return $this->query($sql, ['a' => (int)$appointmentId, 'u' => (int)$recipientUserId]);
    }

    public function getThreadsForUser($userId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT cm.appointment_id,
                       MAX(cm.id) AS last_msg_id,
                       MAX(cm.created_at) AS last_msg_at,
                       SUM(CASE WHEN cm.recipient_user_id = :u AND cm.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
                FROM {$this->table} cm
                WHERE (cm.sender_user_id = :u OR cm.recipient_user_id = :u)
                GROUP BY cm.appointment_id
                ORDER BY last_msg_at DESC
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchRaw($sql, ['u' => (int)$userId, 'limit' => (int)$perPage, 'offset' => (int)$offset]);
        $countSql = "SELECT COUNT(*) AS cnt FROM (
                        SELECT 1 FROM {$this->table} cm
                        WHERE (cm.sender_user_id = :u OR cm.recipient_user_id = :u)
                        GROUP BY cm.appointment_id
                     ) t";
        $totalRow = $this->fetchRaw($countSql, ['u' => (int)$userId]);
        $total = (int)($totalRow[0]['cnt'] ?? 0);
        return [
            'data' => $rows,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage)
        ];
    }
}

