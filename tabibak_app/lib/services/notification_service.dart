import '../config/api_config.dart';
import '../models/notification.dart';
import 'api_service.dart';

class NotificationService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getNotifications({
    int page = 1,
    int limit = 20,
  }) async {
    // TEMPORARY: Mock data for development
    print('🔔 Mock getNotifications');
    await Future.delayed(const Duration(milliseconds: 500));

    final mockNotifications = [
      AppNotification(
        id: 1,
        userId: 1,
        title: 'تأكيد موعد',
        message: 'تم تأكيد موعدك مع د. أحمد محمود في 2024-12-25 الساعة 10:00',
        type: 'appointment',
        isRead: false,
        createdAt: DateTime.now().subtract(const Duration(hours: 2)),
      ),
      AppNotification(
        id: 2,
        userId: 1,
        title: 'منشور جديد',
        message: 'د. فاطمة علي نشرت مقالاً جديداً: التغذية السليمة للأطفال',
        type: 'health_post',
        isRead: false,
        createdAt: DateTime.now().subtract(const Duration(hours: 5)),
      ),
      AppNotification(
        id: 3,
        userId: 1,
        title: 'تذكير بموعد',
        message: 'لديك موعد غداً مع د. أحمد محمود الساعة 10:00',
        type: 'reminder',
        isRead: true,
        createdAt: DateTime.now().subtract(const Duration(days: 1)),
      ),
    ];

    return {
      'notifications': mockNotifications,
      'total': mockNotifications.length,
      'page': page,
      'pages': 1,
    };

    /* REAL API CODE
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
    */
  }

  Future<int> getUnreadCount() async {
    // TEMPORARY: Mock data for development
    print('🔔 Mock getUnreadCount');
    await Future.delayed(const Duration(milliseconds: 300));
    return 2;

    /* REAL API CODE
    final response = await _apiService.get(
      ApiConfig.unreadCount,
      requiresAuth: true,
    );

    return response['count'] ?? 0;
    */
  }

  Future<void> markAsRead(int notificationId) async {
    // TEMPORARY: Mock data for development
    print('🔔 Mock markAsRead: $notificationId');
    await Future.delayed(const Duration(milliseconds: 300));

    /* REAL API CODE
    await _apiService.post(
      ApiConfig.markAsRead(notificationId),
      body: {},
      requiresAuth: true,
    );
    */
  }

  Future<void> markAllAsRead() async {
    // TEMPORARY: Mock data for development
    print('🔔 Mock markAllAsRead');
    await Future.delayed(const Duration(milliseconds: 300));

    /* REAL API CODE
    await _apiService.post(
      ApiConfig.markAllAsRead,
      body: {},
      requiresAuth: true,
    );
    */
  }
}

