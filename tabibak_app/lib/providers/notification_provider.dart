import 'package:flutter/material.dart';
import '../models/notification.dart';
import '../services/notification_service.dart';

class NotificationProvider with ChangeNotifier {
  final NotificationService _notificationService = NotificationService();

  List<AppNotification> _notifications = [];
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  int _totalPages = 1;
  int _unreadCount = 0;

  List<AppNotification> get notifications => _notifications;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get currentPage => _currentPage;
  int get totalPages => _totalPages;
  bool get hasMore => _currentPage < _totalPages;
  int get unreadCount => _unreadCount;

  Future<void> loadNotifications({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _notifications.clear();
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final result = await _notificationService.getNotifications(
        page: _currentPage,
      );

      if (refresh) {
        _notifications = result['notifications'];
      } else {
        _notifications.addAll(result['notifications']);
      }

      _totalPages = result['pages'];
      _isLoading = false;
      notifyListeners();

      // Load unread count
      await loadUnreadCount();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMore() async {
    if (!hasMore || _isLoading) return;

    _currentPage++;
    await loadNotifications();
  }

  Future<void> loadUnreadCount() async {
    try {
      _unreadCount = await _notificationService.getUnreadCount();
      notifyListeners();
    } catch (e) {
      // Ignore error for unread count
    }
  }

  Future<void> markAsRead(int notificationId) async {
    try {
      await _notificationService.markAsRead(notificationId);

      // Update local notification
      final index = _notifications.indexWhere((n) => n.id == notificationId);
      if (index != -1 && !_notifications[index].isRead) {
        _notifications[index] = AppNotification(
          id: _notifications[index].id,
          userId: _notifications[index].userId,
          type: _notifications[index].type,
          title: _notifications[index].title,
          message: _notifications[index].message,
          data: _notifications[index].data,
          isRead: true,
          createdAt: _notifications[index].createdAt,
        );
        _unreadCount = (_unreadCount - 1).clamp(0, 999);
        notifyListeners();
      }
    } catch (e) {
      // Ignore error
    }
  }

  Future<void> markAllAsRead() async {
    try {
      await _notificationService.markAllAsRead();

      // Update all local notifications
      _notifications = _notifications.map((n) {
        return AppNotification(
          id: n.id,
          userId: n.userId,
          type: n.type,
          title: n.title,
          message: n.message,
          data: n.data,
          isRead: true,
          createdAt: n.createdAt,
        );
      }).toList();

      _unreadCount = 0;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      notifyListeners();
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  void reset() {
    _notifications = [];
    _currentPage = 1;
    _totalPages = 1;
    _unreadCount = 0;
    _error = null;
    _isLoading = false;
    notifyListeners();
  }
}

