<?php
namespace API;

class ChatController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get user's conversations
     * GET /api/chat/conversations
     */
    public function getConversations() {
        try {
            $userId = $_SESSION['user_id'];
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;
            
            // Get conversations with last message
            $stmt = $this->db->prepare("
                SELECT 
                    c.id,
                    c.user1_id,
                    c.user2_id,
                    c.created_at,
                    c.updated_at,
                    CASE 
                        WHEN c.user1_id = ? THEN u2.name
                        ELSE u1.name
                    END as other_user_name,
                    CASE 
                        WHEN c.user1_id = ? THEN u2.avatar
                        ELSE u1.avatar
                    END as other_user_avatar,
                    CASE 
                        WHEN c.user1_id = ? THEN c.user2_id
                        ELSE c.user1_id
                    END as other_user_id,
                    (SELECT message FROM chat_messages 
                     WHERE conversation_id = c.id 
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM chat_messages 
                     WHERE conversation_id = c.id 
                     ORDER BY created_at DESC LIMIT 1) as last_message_time,
                    (SELECT COUNT(*) FROM chat_messages 
                     WHERE conversation_id = c.id 
                     AND receiver_id = ? 
                     AND is_read = 0) as unread_count
                FROM conversations c
                INNER JOIN users u1 ON c.user1_id = u1.id
                INNER JOIN users u2 ON c.user2_id = u2.id
                WHERE c.user1_id = ? OR c.user2_id = ?
                ORDER BY c.updated_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $limit, $offset]);
            $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get total count
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM conversations 
                WHERE user1_id = ? OR user2_id = ?
            ");
            $countStmt->execute([$userId, $userId]);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            Response::success([
                'data' => $conversations,
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);
            
        } catch (\Exception $e) {
            Response::error('فشل في جلب المحادثات: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get messages in a conversation
     * GET /api/chat/messages/{conversation_id}
     */
    public function getMessages($conversationId) {
        try {
            $userId = $_SESSION['user_id'];
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = ($page - 1) * $limit;
            
            // Verify user is part of conversation
            $stmt = $this->db->prepare("
                SELECT * FROM conversations 
                WHERE id = ? AND (user1_id = ? OR user2_id = ?)
            ");
            $stmt->execute([$conversationId, $userId, $userId]);
            $conversation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$conversation) {
                Response::error('المحادثة غير موجودة أو لا تملك صلاحية الوصول', 404);
                return;
            }
            
            // Get messages
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    u.name as sender_name,
                    u.avatar as sender_avatar
                FROM chat_messages m
                INNER JOIN users u ON m.sender_id = u.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$conversationId, $limit, $offset]);
            $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Reverse to show oldest first
            $messages = array_reverse($messages);
            
            // Mark messages as read
            $stmt = $this->db->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$conversationId, $userId]);
            
            // Get total count
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM chat_messages 
                WHERE conversation_id = ?
            ");
            $countStmt->execute([$conversationId]);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            Response::success([
                'data' => $messages,
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);
            
        } catch (\Exception $e) {
            Response::error('فشل في جلب الرسائل: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Send a message
     * POST /api/chat/send
     */
    public function sendMessage() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $senderId = $_SESSION['user_id'];
            $receiverId = isset($data['receiver_id']) ? (int)$data['receiver_id'] : null;
            $message = isset($data['message']) ? trim($data['message']) : null;
            $attachmentUrl = isset($data['attachment_url']) ? trim($data['attachment_url']) : null;
            $attachmentType = isset($data['attachment_type']) ? trim($data['attachment_type']) : null;
            
            // Validate
            if (!$receiverId) {
                Response::error('معرف المستقبل مطلوب', 400);
                return;
            }
            
            if (!$message && !$attachmentUrl) {
                Response::error('الرسالة أو المرفق مطلوب', 400);
                return;
            }
            
            // Check if receiver exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$receiverId]);
            if (!$stmt->fetch()) {
                Response::error('المستقبل غير موجود', 404);
                return;
            }
            
            // Get or create conversation
            $stmt = $this->db->prepare("
                SELECT id FROM conversations 
                WHERE (user1_id = ? AND user2_id = ?) 
                   OR (user1_id = ? AND user2_id = ?)
            ");
            $stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);
            $conversation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($conversation) {
                $conversationId = $conversation['id'];
                
                // Update conversation timestamp
                $stmt = $this->db->prepare("
                    UPDATE conversations 
                    SET updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$conversationId]);
            } else {
                // Create new conversation
                $stmt = $this->db->prepare("
                    INSERT INTO conversations (user1_id, user2_id, created_at, updated_at)
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([$senderId, $receiverId]);
                $conversationId = $this->db->lastInsertId();
            }
            
            // Insert message
            $stmt = $this->db->prepare("
                INSERT INTO chat_messages 
                (conversation_id, sender_id, receiver_id, message, attachment_url, attachment_type, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            $stmt->execute([
                $conversationId,
                $senderId,
                $receiverId,
                $message,
                $attachmentUrl,
                $attachmentType
            ]);
            $messageId = $this->db->lastInsertId();
            
            // Get the created message
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    u.name as sender_name,
                    u.avatar as sender_avatar
                FROM chat_messages m
                INNER JOIN users u ON m.sender_id = u.id
                WHERE m.id = ?
            ");
            $stmt->execute([$messageId]);
            $messageData = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            Response::success([
                'message' => 'تم إرسال الرسالة بنجاح',
                'data' => $messageData
            ], 201);
            
        } catch (\Exception $e) {
            Response::error('فشل في إرسال الرسالة: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Mark messages as read
     * POST /api/chat/mark-read/{conversation_id}
     */
    public function markAsRead($conversationId) {
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$conversationId, $userId]);
            
            Response::success(['message' => 'تم تعليم الرسائل كمقروءة']);
            
        } catch (\Exception $e) {
            Response::error('فشل في تعليم الرسائل كمقروءة: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get unread messages count
     * GET /api/chat/unread-count
     */
    public function getUnreadCount() {
        try {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM chat_messages 
                WHERE receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            Response::success(['count' => (int)$result['count']]);
            
        } catch (\Exception $e) {
            Response::error('فشل في جلب عدد الرسائل غير المقروءة: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Upload attachment
     * POST /api/chat/upload
     */
    public function uploadAttachment() {
        try {
            if (!isset($_FILES['file'])) {
                Response::error('لم يتم رفع ملف', 400);
                return;
            }
            
            $file = $_FILES['file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            // Validate file type
            if (!in_array($file['type'], $allowedTypes)) {
                Response::error('نوع الملف غير مسموح', 400);
                return;
            }
            
            // Validate file size
            if ($file['size'] > $maxSize) {
                Response::error('حجم الملف كبير جداً (الحد الأقصى 5MB)', 400);
                return;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $uploadDir = ROOT_PATH . '/uploads/chat/';
            
            // Create directory if not exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $fileUrl = '/uploads/chat/' . $filename;
                
                Response::success([
                    'url' => $fileUrl,
                    'type' => strpos($file['type'], 'image') !== false ? 'image' : 'file'
                ]);
            } else {
                Response::error('فشل في رفع الملف', 500);
            }
            
        } catch (\Exception $e) {
            Response::error('فشل في رفع الملف: ' . $e->getMessage(), 500);
        }
    }
}

