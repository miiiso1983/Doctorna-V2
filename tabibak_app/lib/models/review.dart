class Review {
  final int id;
  final int doctorId;
  final int patientId;
  final String patientName;
  final String? patientAvatar;
  final int rating;
  final String? comment;
  final DateTime createdAt;

  Review({
    required this.id,
    required this.doctorId,
    required this.patientId,
    required this.patientName,
    this.patientAvatar,
    required this.rating,
    this.comment,
    required this.createdAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      doctorId: json['doctor_id'] is String ? int.parse(json['doctor_id']) : json['doctor_id'],
      patientId: json['patient_id'] is String ? int.parse(json['patient_id']) : json['patient_id'],
      patientName: json['patient_name'] ?? '',
      patientAvatar: json['patient_avatar'],
      rating: json['rating'] is String ? int.parse(json['rating']) : json['rating'],
      comment: json['comment'],
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'doctor_id': doctorId,
      'patient_id': patientId,
      'patient_name': patientName,
      'patient_avatar': patientAvatar,
      'rating': rating,
      'comment': comment,
      'created_at': createdAt.toIso8601String(),
    };
  }

  String get formattedDate {
    final now = DateTime.now();
    final difference = now.difference(createdAt);

    if (difference.inDays == 0) {
      if (difference.inHours == 0) {
        return 'منذ ${difference.inMinutes} دقيقة';
      }
      return 'منذ ${difference.inHours} ساعة';
    } else if (difference.inDays < 7) {
      return 'منذ ${difference.inDays} يوم';
    } else if (difference.inDays < 30) {
      return 'منذ ${(difference.inDays / 7).floor()} أسبوع';
    } else {
      return '${createdAt.year}-${createdAt.month.toString().padLeft(2, '0')}-${createdAt.day.toString().padLeft(2, '0')}';
    }
  }
}

