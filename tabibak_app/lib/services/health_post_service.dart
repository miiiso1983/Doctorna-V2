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

