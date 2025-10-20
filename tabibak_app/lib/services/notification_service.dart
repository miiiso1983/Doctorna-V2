import '../config/api_config.dart';
import '../models/notification.dart';
import 'api_service.dart';

class NotificationService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getNotifications({
    int page = 1,
    int limit = 20,
  }) async {
    final queryParams = {
      'page': page.toString(),
      'limit': limit.toString(),
    };

    final queryString = queryParams.entries
        .map((e) => '${e.key}=${Uri.encodeComponent(e.value)}')
        .join('&');

    final response = await _apiService.get(
      '${ApiConfig.notificationsList}?$queryString',
      requiresAuth: true,
    );

    final notifications = (response['data'] as List)
        .map((json) => AppNotification.fromJson(json))
        .toList();

    return {
      'notifications': notifications,
      'total': response['total'] ?? 0,
      'page': response['page'] ?? page,
      'pages': response['pages'] ?? 1,
    };
  }

  Future<int> getUnreadCount() async {
    final response = await _apiService.get(
      ApiConfig.unreadCount,
      requiresAuth: true,
    );

    return response['count'] ?? 0;
  }

  Future<void> markAsRead(int notificationId) async {
    await _apiService.post(
      ApiConfig.markAsRead(notificationId),
      body: {},
      requiresAuth: true,
    );
  }

  Future<void> markAllAsRead() async {
    await _apiService.post(
      ApiConfig.markAllAsRead,
      body: {},
      requiresAuth: true,
    );
  }
}

