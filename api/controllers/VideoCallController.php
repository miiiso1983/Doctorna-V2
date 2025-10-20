<?php
namespace API;

use PDO;

class VideoCallController {
    private $db;
    private $userId;
    
    // Agora credentials - يجب استبدالها بالبيانات الخاصة بك
    private $agoraAppId = 'YOUR_AGORA_APP_ID';
    private $agoraAppCertificate = 'YOUR_AGORA_APP_CERTIFICATE';
    
    public function __construct($db) {
        $this->db = $db;
        $this->userId = $_SESSION['user_id'] ?? null;
    }
    
    // Initiate a video call
    public function initiateCall() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $receiverId = $data['receiver_id'] ?? null;
            
            if (!$receiverId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'معرف المستقبل مطلوب'
                ]);
                return;
            }
            
            // Check if receiver exists
            $stmt = $this->db->prepare("SELECT id, name, avatar FROM users WHERE id = ?");
            $stmt->execute([$receiverId]);
            $receiver = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$receiver) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'المستخدم غير موجود'
                ]);
                return;
            }
            
            // Get caller info
            $stmt = $this->db->prepare("SELECT id, name, avatar FROM users WHERE id = ?");
            $stmt->execute([$this->userId]);
            $caller = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generate unique channel name
            $channelName = 'call_' . $this->userId . '_' . $receiverId . '_' . time();
            
            // Generate Agora token (simplified - in production use proper token generation)
            $token = $this->generateAgoraToken($channelName);
            
            // Create call record
            $stmt = $this->db->prepare("
                INSERT INTO video_calls 
                (caller_id, receiver_id, channel_name, token, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$this->userId, $receiverId, $channelName, $token]);
            $callId = $this->db->lastInsertId();
            
            // Get the created call
            $call = $this->getCallById($callId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'تم بدء المكالمة بنجاح',
                'data' => $call
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'خطأ في بدء المكالمة: ' . $e->getMessage()
            ]);
        }
    }
    
    // Accept a video call
    public function acceptCall($callId) {
        try {
            // Get call details
            $stmt = $this->db->prepare("
                SELECT * FROM video_calls 
                WHERE id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$callId, $this->userId]);
            $call = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$call) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'المكالمة غير موجودة أو منتهية'
                ]);
                return;
            }
            
            // Update call status
            $stmt = $this->db->prepare("
                UPDATE video_calls 
                SET status = 'accepted', started_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$callId]);
            
            // Get updated call
            $updatedCall = $this->getCallById($callId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'تم قبول المكالمة',
                'data' => $updatedCall
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'خطأ في قبول المكالمة: ' . $e->getMessage()
            ]);
        }
    }
    
    // Reject a video call
    public function rejectCall($callId) {
        try {
            // Update call status
            $stmt = $this->db->prepare("
                UPDATE video_calls 
                SET status = 'rejected', ended_at = NOW() 
                WHERE id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$callId, $this->userId]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'المكالمة غير موجودة أو منتهية'
                ]);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'تم رفض المكالمة'
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'خطأ في رفض المكالمة: ' . $e->getMessage()
            ]);
        }
    }
    
    // End a video call
    public function endCall($callId) {
        try {
            // Get call details
            $stmt = $this->db->prepare("
                SELECT * FROM video_calls 
                WHERE id = ? AND (caller_id = ? OR receiver_id = ?) AND status = 'accepted'
            ");
            $stmt->execute([$callId, $this->userId, $this->userId]);
            $call = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$call) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'المكالمة غير موجودة أو منتهية'
                ]);
                return;
            }
            
            // Calculate duration
            $startedAt = new \DateTime($call['started_at']);
            $now = new \DateTime();
            $duration = $now->getTimestamp() - $startedAt->getTimestamp();
            
            // Update call status
            $stmt = $this->db->prepare("
                UPDATE video_calls 
                SET status = 'ended', ended_at = NOW(), duration = ? 
                WHERE id = ?
            ");
            $stmt->execute([$duration, $callId]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'تم إنهاء المكالمة'
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'خطأ في إنهاء المكالمة: ' . $e->getMessage()
            ]);
        }
    }
    
    // Get call details
    public function getCallDetails($callId) {
        try {
            $call = $this->getCallById($callId);
            
            if (!$call) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'المكالمة غير موجودة'
                ]);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $call
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'خطأ في جلب تفاصيل المكالمة: ' . $e->getMessage()
            ]);
        }
    }
    
    // Get call history
    public function getCallHistory() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;
            
            $stmt = $this->db->prepare("
                SELECT 
                    vc.*,
                    caller.name as caller_name,
                    caller.avatar as caller_avatar,
                    receiver.name as receiver_name,
                    receiver.avatar as receiver_avatar
                FROM video_calls vc
                LEFT JOIN users caller ON vc.caller_id = caller.id
                LEFT JOIN users receiver ON vc.receiver_id = receiver.id
                WHERE vc.caller_id = ? OR vc.receiver_id = ?
                ORDER BY vc.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$this->userId, $this->userId, $limit, $offset]);
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $calls
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'خطأ في جلب سجل المكالمات: ' . $e->getMessage()
            ]);
        }
    }
    
    // Helper: Get call by ID
    private function getCallById($callId) {
        $stmt = $this->db->prepare("
            SELECT 
                vc.*,
                caller.name as caller_name,
                caller.avatar as caller_avatar,
                receiver.name as receiver_name,
                receiver.avatar as receiver_avatar
            FROM video_calls vc
            LEFT JOIN users caller ON vc.caller_id = caller.id
            LEFT JOIN users receiver ON vc.receiver_id = receiver.id
            WHERE vc.id = ?
        ");
        $stmt->execute([$callId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Helper: Generate Agora token (simplified version)
    // In production, use proper Agora token generation library
    private function generateAgoraToken($channelName) {
        // This is a simplified version
        // In production, use Agora's official token generation library
        // For now, return a placeholder or use token server
        return 'temp_token_' . md5($channelName . time());
    }
}

