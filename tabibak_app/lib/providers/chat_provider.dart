import 'dart:async';
import 'package:flutter/material.dart';
import '../models/chat_message.dart';
import '../services/chat_service.dart';

class ChatProvider with ChangeNotifier {
  final ChatService _chatService = ChatService();

  // Conversations
  List<Conversation> _conversations = [];
  bool _isLoadingConversations = false;
  String? _conversationsError;
  int _conversationsPage = 1;
  int _conversationsTotalPages = 1;

  // Messages
  Map<int, List<ChatMessage>> _messagesByConversation = {};
  Map<int, bool> _isLoadingMessages = {};
  Map<int, String?> _messagesError = {};
  Map<int, int> _messagesPage = {};
  Map<int, int> _messagesTotalPages = {};

  // Unread count
  int _unreadCount = 0;

  // Auto-refresh timer
  Timer? _refreshTimer;

  // Getters
  List<Conversation> get conversations => _conversations;
  bool get isLoadingConversations => _isLoadingConversations;
  String? get conversationsError => _conversationsError;
  int get unreadCount => _unreadCount;

  List<ChatMessage> getMessages(int conversationId) {
    return _messagesByConversation[conversationId] ?? [];
  }

  bool isLoadingMessages(int conversationId) {
    return _isLoadingMessages[conversationId] ?? false;
  }

  String? getMessagesError(int conversationId) {
    return _messagesError[conversationId];
  }

  ChatProvider() {
    // Start auto-refresh every 10 seconds
    _startAutoRefresh();
  }

  void _startAutoRefresh() {
    _refreshTimer = Timer.periodic(const Duration(seconds: 10), (timer) {
      loadUnreadCount();
      if (_conversations.isNotEmpty) {
        loadConversations(refresh: true);
      }
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  Future<void> loadConversations({bool refresh = false}) async {
    if (refresh) {
      _conversationsPage = 1;
      _conversations.clear();
    }

    _isLoadingConversations = true;
    _conversationsError = null;
    notifyListeners();

    try {
      final result = await _chatService.getConversations(
        page: _conversationsPage,
      );

      if (refresh) {
        _conversations = result['conversations'];
      } else {
        _conversations.addAll(result['conversations']);
      }

      _conversationsTotalPages = result['pages'];
      _isLoadingConversations = false;
      notifyListeners();
    } catch (e) {
      _conversationsError = e.toString();
      _isLoadingConversations = false;
      notifyListeners();
    }
  }

  Future<void> loadMoreConversations() async {
    if (_conversationsPage < _conversationsTotalPages && !_isLoadingConversations) {
      _conversationsPage++;
      await loadConversations();
    }
  }

  Future<void> loadMessages(int conversationId, {bool refresh = false}) async {
    if (refresh) {
      _messagesPage[conversationId] = 1;
      _messagesByConversation[conversationId] = [];
    }

    _isLoadingMessages[conversationId] = true;
    _messagesError[conversationId] = null;
    notifyListeners();

    try {
      final result = await _chatService.getMessages(
        conversationId: conversationId,
        page: _messagesPage[conversationId] ?? 1,
      );

      if (refresh) {
        _messagesByConversation[conversationId] = result['messages'];
      } else {
        final existingMessages = _messagesByConversation[conversationId] ?? [];
        _messagesByConversation[conversationId] = [
          ...result['messages'],
          ...existingMessages,
        ];
      }

      _messagesTotalPages[conversationId] = result['pages'];
      _isLoadingMessages[conversationId] = false;
      notifyListeners();

      // Mark as read
      await _chatService.markAsRead(conversationId);
      await loadUnreadCount();
    } catch (e) {
      _messagesError[conversationId] = e.toString();
      _isLoadingMessages[conversationId] = false;
      notifyListeners();
    }
  }

  Future<void> loadMoreMessages(int conversationId) async {
    final currentPage = _messagesPage[conversationId] ?? 1;
    final totalPages = _messagesTotalPages[conversationId] ?? 1;

    if (currentPage < totalPages && !(_isLoadingMessages[conversationId] ?? false)) {
      _messagesPage[conversationId] = currentPage + 1;
      await loadMessages(conversationId);
    }
  }

  Future<void> sendMessage({
    required int receiverId,
    String? message,
    String? attachmentUrl,
    String? attachmentType,
    int? conversationId,
  }) async {
    try {
      final sentMessage = await _chatService.sendMessage(
        receiverId: receiverId,
        message: message,
        attachmentUrl: attachmentUrl,
        attachmentType: attachmentType,
      );

      // Add message to local list
      if (conversationId != null) {
        final messages = _messagesByConversation[conversationId] ?? [];
        _messagesByConversation[conversationId] = [...messages, sentMessage];
        notifyListeners();
      }

      // Refresh conversations to update last message
      await loadConversations(refresh: true);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> loadUnreadCount() async {
    try {
      _unreadCount = await _chatService.getUnreadCount();
      notifyListeners();
    } catch (e) {
      // Silently fail
    }
  }

  void clearConversationsError() {
    _conversationsError = null;
    notifyListeners();
  }

  void clearMessagesError(int conversationId) {
    _messagesError[conversationId] = null;
    notifyListeners();
  }

  void reset() {
    _conversations = [];
    _messagesByConversation = {};
    _isLoadingConversations = false;
    _isLoadingMessages = {};
    _conversationsError = null;
    _messagesError = {};
    _conversationsPage = 1;
    _conversationsTotalPages = 1;
    _messagesPage = {};
    _messagesTotalPages = {};
    _unreadCount = 0;
    notifyListeners();
  }
}

