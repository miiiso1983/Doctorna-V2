import '../config/api_config.dart';
import '../models/health_post.dart';
import 'api_service.dart';

class HealthPostService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getHealthPosts({
    int page = 1,
    int limit = 10,
    String? category,
  }) async {
    // TEMPORARY: Mock data for development
    print('📰 Mock getHealthPosts');
    await Future.delayed(const Duration(milliseconds: 500));

    final mockPosts = [
      HealthPost(
        id: 1,
        doctorId: 1,
        authorName: 'د. أحمد محمود',
        specializationName: 'طب القلب',
        title: 'نصائح للحفاظ على صحة القلب',
        content: 'القلب هو أهم عضو في جسم الإنسان، ويجب الاهتمام به من خلال:\n\n1. ممارسة الرياضة بانتظام\n2. تناول طعام صحي\n3. تجنب التدخين\n4. الفحص الدوري',
        category: 'general',
        status: 'published',
        image: 'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?w=800',
        views: 1250,
        createdAt: DateTime.now().subtract(const Duration(days: 2)),
      ),
      HealthPost(
        id: 2,
        doctorId: 2,
        authorName: 'د. فاطمة علي',
        specializationName: 'طب الأطفال',
        title: 'التغذية السليمة للأطفال',
        content: 'التغذية الصحية للأطفال مهمة جداً لنموهم:\n\n1. الحليب ومشتقاته\n2. الفواكه والخضروات\n3. البروتينات\n4. الحبوب الكاملة',
        category: 'nutrition',
        status: 'published',
        image: 'https://images.unsplash.com/photo-1490818387583-1baba5e638af?w=800',
        views: 980,
        createdAt: DateTime.now().subtract(const Duration(days: 5)),
      ),
      HealthPost(
        id: 3,
        doctorId: 3,
        authorName: 'د. محمد حسن',
        specializationName: 'طب الأسنان',
        title: 'العناية بالأسنان اليومية',
        content: 'للحفاظ على أسنان صحية:\n\n1. تنظيف الأسنان مرتين يومياً\n2. استخدام الخيط الطبي\n3. زيارة طبيب الأسنان كل 6 أشهر\n4. تجنب السكريات',
        category: 'general',
        status: 'published',
        image: 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?w=800',
        views: 1450,
        createdAt: DateTime.now().subtract(const Duration(days: 7)),
      ),
    ];

    return {
      'posts': mockPosts,
      'total': mockPosts.length,
      'page': page,
      'pages': 1,
    };

    /* REAL API CODE
    final queryParams = {
      'page': page.toString(),
      'limit': limit.toString(),
      if (category != null && category.isNotEmpty) 'category': category,
    };

    final queryString = queryParams.entries
        .map((e) => '${e.key}=${Uri.encodeComponent(e.value)}')
        .join('&');

    final response = await _apiService.get(
      '${ApiConfig.healthPostsList}?$queryString',
      requiresAuth: false,
    );

    final posts = (response['data'] as List)
        .map((json) => HealthPost.fromJson(json))
        .toList();

    return {
      'posts': posts,
      'total': response['total'] ?? 0,
      'page': response['page'] ?? page,
      'pages': response['pages'] ?? 1,
    };
    */
  }

  Future<HealthPost> getHealthPostDetails(int postId) async {
    final response = await _apiService.get(
      ApiConfig.healthPostDetails(postId),
      requiresAuth: false,
    );

    return HealthPost.fromJson(response);
  }

  Future<Map<String, dynamic>> createHealthPost({
    required String title,
    required String content,
    required String category,
  }) async {
    final response = await _apiService.post(
      ApiConfig.createHealthPost,
      body: {
        'title': title,
        'content': content,
        'category': category,
      },
      requiresAuth: true,
    );

    return response;
  }

  Future<void> updateHealthPost({
    required int postId,
    String? title,
    String? content,
    String? category,
  }) async {
    await _apiService.post(
      ApiConfig.updateHealthPost(postId),
      body: {
        if (title != null) 'title': title,
        if (content != null) 'content': content,
        if (category != null) 'category': category,
      },
      requiresAuth: true,
    );
  }

  Future<void> deleteHealthPost(int postId) async {
    await _apiService.post(
      ApiConfig.deleteHealthPost(postId),
      body: {},
      requiresAuth: true,
    );
  }
}

