class ChatMessage {
  final int id;
  final int senderId;
  final int receiverId;
  final String message;
  final String? attachmentUrl;
  final String? attachmentType;
  final bool isRead;
  final DateTime createdAt;

  ChatMessage({
    required this.id,
    required this.senderId,
    required this.receiverId,
    required this.message,
    this.attachmentUrl,
    this.attachmentType,
    required this.isRead,
    required this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      senderId: json['sender_id'] is String 
          ? int.parse(json['sender_id']) 
          : json['sender_id'],
      receiverId: json['receiver_id'] is String 
          ? int.parse(json['receiver_id']) 
          : json['receiver_id'],
      message: json['message'] ?? '',
      attachmentUrl: json['attachment_url'],
      attachmentType: json['attachment_type'],
      isRead: json['is_read'] == 1 || json['is_read'] == true,
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'sender_id': senderId,
      'receiver_id': receiverId,
      'message': message,
      'attachment_url': attachmentUrl,
      'attachment_type': attachmentType,
      'is_read': isRead ? 1 : 0,
      'created_at': createdAt.toIso8601String(),
    };
  }

  String get formattedTime {
    final now = DateTime.now();
    final difference = now.difference(createdAt);

    if (difference.inDays == 0) {
      return '${createdAt.hour.toString().padLeft(2, '0')}:${createdAt.minute.toString().padLeft(2, '0')}';
    } else if (difference.inDays == 1) {
      return 'أمس';
    } else if (difference.inDays < 7) {
      return 'منذ ${difference.inDays} يوم';
    } else {
      return '${createdAt.year}-${createdAt.month.toString().padLeft(2, '0')}-${createdAt.day.toString().padLeft(2, '0')}';
    }
  }
}

class Conversation {
  final int userId;
  final String userName;
  final String? userAvatar;
  final String? lastMessage;
  final DateTime? lastMessageTime;
  final int unreadCount;

  Conversation({
    required this.userId,
    required this.userName,
    this.userAvatar,
    this.lastMessage,
    this.lastMessageTime,
    required this.unreadCount,
  });

  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      userId: json['user_id'] is String ? int.parse(json['user_id']) : json['user_id'],
      userName: json['user_name'] ?? '',
      userAvatar: json['user_avatar'],
      lastMessage: json['last_message'],
      lastMessageTime: json['last_message_time'] != null 
          ? DateTime.parse(json['last_message_time']) 
          : null,
      unreadCount: json['unread_count'] is String 
          ? int.parse(json['unread_count']) 
          : (json['unread_count'] ?? 0),
    );
  }

  String get formattedTime {
    if (lastMessageTime == null) return '';
    
    final now = DateTime.now();
    final difference = now.difference(lastMessageTime!);

    if (difference.inDays == 0) {
      return '${lastMessageTime!.hour.toString().padLeft(2, '0')}:${lastMessageTime!.minute.toString().padLeft(2, '0')}';
    } else if (difference.inDays == 1) {
      return 'أمس';
    } else if (difference.inDays < 7) {
      return 'منذ ${difference.inDays} يوم';
    } else {
      return '${lastMessageTime!.year}-${lastMessageTime!.month.toString().padLeft(2, '0')}-${lastMessageTime!.day.toString().padLeft(2, '0')}';
    }
  }
}

