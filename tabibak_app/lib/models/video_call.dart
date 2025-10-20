class VideoCall {
  final int id;
  final int callerId;
  final int receiverId;
  final String callerName;
  final String receiverName;
  final String? callerAvatar;
  final String? receiverAvatar;
  final String channelName;
  final String token;
  final String status; // pending, accepted, rejected, ended, missed
  final DateTime createdAt;
  final DateTime? startedAt;
  final DateTime? endedAt;
  final int? duration; // in seconds

  VideoCall({
    required this.id,
    required this.callerId,
    required this.receiverId,
    required this.callerName,
    required this.receiverName,
    this.callerAvatar,
    this.receiverAvatar,
    required this.channelName,
    required this.token,
    required this.status,
    required this.createdAt,
    this.startedAt,
    this.endedAt,
    this.duration,
  });

  factory VideoCall.fromJson(Map<String, dynamic> json) {
    return VideoCall(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      callerId: json['caller_id'] is String 
          ? int.parse(json['caller_id']) 
          : json['caller_id'],
      receiverId: json['receiver_id'] is String 
          ? int.parse(json['receiver_id']) 
          : json['receiver_id'],
      callerName: json['caller_name'] ?? '',
      receiverName: json['receiver_name'] ?? '',
      callerAvatar: json['caller_avatar'],
      receiverAvatar: json['receiver_avatar'],
      channelName: json['channel_name'] ?? '',
      token: json['token'] ?? '',
      status: json['status'] ?? 'pending',
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : DateTime.now(),
      startedAt: json['started_at'] != null 
          ? DateTime.parse(json['started_at']) 
          : null,
      endedAt: json['ended_at'] != null 
          ? DateTime.parse(json['ended_at']) 
          : null,
      duration: json['duration'] is String 
          ? int.tryParse(json['duration']) 
          : json['duration'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'caller_id': callerId,
      'receiver_id': receiverId,
      'caller_name': callerName,
      'receiver_name': receiverName,
      'caller_avatar': callerAvatar,
      'receiver_avatar': receiverAvatar,
      'channel_name': channelName,
      'token': token,
      'status': status,
      'created_at': createdAt.toIso8601String(),
      'started_at': startedAt?.toIso8601String(),
      'ended_at': endedAt?.toIso8601String(),
      'duration': duration,
    };
  }

  String get formattedDuration {
    if (duration == null) return '00:00';
    final minutes = duration! ~/ 60;
    final seconds = duration! % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  bool get isActive => status == 'accepted';
  bool get isPending => status == 'pending';
  bool get isEnded => status == 'ended' || status == 'rejected' || status == 'missed';
}

