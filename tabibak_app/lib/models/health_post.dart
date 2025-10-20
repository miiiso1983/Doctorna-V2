class HealthPost {
  final int id;
  final int doctorId;
  final String title;
  final String content;
  final String category;
  final String status;
  final int views;
  final String? image;
  final String authorName;
  final String? authorAvatar;
  final String? specializationName;
  final DateTime createdAt;

  HealthPost({
    required this.id,
    required this.doctorId,
    required this.title,
    required this.content,
    required this.category,
    required this.status,
    required this.views,
    this.image,
    required this.authorName,
    this.authorAvatar,
    this.specializationName,
    required this.createdAt,
  });

  factory HealthPost.fromJson(Map<String, dynamic> json) {
    return HealthPost(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      doctorId: json['doctor_id'] is String ? int.parse(json['doctor_id']) : json['doctor_id'],
      title: json['title'] ?? '',
      content: json['content'] ?? '',
      category: json['category'] ?? '',
      status: json['status'] ?? 'pending',
      views: json['views'] is String ? int.parse(json['views']) : (json['views'] ?? 0),
      image: json['image'],
      authorName: json['author_name'] ?? '',
      authorAvatar: json['author_avatar'],
      specializationName: json['specialization_name'],
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'doctor_id': doctorId,
      'title': title,
      'content': content,
      'category': category,
      'status': status,
      'views': views,
      'image': image,
      'author_name': authorName,
      'author_avatar': authorAvatar,
      'specialization_name': specializationName,
      'created_at': createdAt.toIso8601String(),
    };
  }

  String get categoryArabic {
    switch (category) {
      case 'general':
        return 'عام';
      case 'nutrition':
        return 'تغذية';
      case 'fitness':
        return 'لياقة';
      case 'mental_health':
        return 'صحة نفسية';
      case 'diseases':
        return 'أمراض';
      case 'prevention':
        return 'وقاية';
      default:
        return category;
    }
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
    } else {
      return '${createdAt.year}-${createdAt.month.toString().padLeft(2, '0')}-${createdAt.day.toString().padLeft(2, '0')}';
    }
  }
}

