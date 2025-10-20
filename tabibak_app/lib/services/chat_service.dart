import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import '../config/api_config.dart';
import '../models/chat_message.dart';
import 'api_service.dart';

class ChatService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getConversations({
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
      '${ApiConfig.chatConversations}?$queryString',
      requiresAuth: true,
    );

    final conversations = (response['data'] as List)
        .map((json) => Conversation.fromJson(json))
        .toList();

    return {
      'conversations': conversations,
      'total': response['total'] ?? 0,
      'page': response['page'] ?? page,
      'pages': response['pages'] ?? 1,
    };
  }

  Future<Map<String, dynamic>> getMessages({
    required int conversationId,
    int page = 1,
    int limit = 50,
  }) async {
    final queryParams = {
      'page': page.toString(),
      'limit': limit.toString(),
    };

    final queryString = queryParams.entries
        .map((e) => '${e.key}=${Uri.encodeComponent(e.value)}')
        .join('&');

    final response = await _apiService.get(
      '${ApiConfig.chatMessages(conversationId)}?$queryString',
      requiresAuth: true,
    );

    final messages = (response['data'] as List)
        .map((json) => ChatMessage.fromJson(json))
        .toList();

    return {
      'messages': messages,
      'total': response['total'] ?? 0,
      'page': response['page'] ?? page,
      'pages': response['pages'] ?? 1,
    };
  }

  Future<ChatMessage> sendMessage({
    required int receiverId,
    String? message,
    String? attachmentUrl,
    String? attachmentType,
  }) async {
    final response = await _apiService.post(
      ApiConfig.chatSend,
      body: {
        'receiver_id': receiverId,
        if (message != null && message.isNotEmpty) 'message': message,
        if (attachmentUrl != null) 'attachment_url': attachmentUrl,
        if (attachmentType != null) 'attachment_type': attachmentType,
      },
      requiresAuth: true,
    );

    return ChatMessage.fromJson(response['data']);
  }

  Future<void> markAsRead(int conversationId) async {
    await _apiService.post(
      ApiConfig.chatMarkRead(conversationId),
      body: {},
      requiresAuth: true,
    );
  }

  Future<int> getUnreadCount() async {
    final response = await _apiService.get(
      ApiConfig.chatUnreadCount,
      requiresAuth: true,
    );

    return response['count'] ?? 0;
  }

  Future<Map<String, String>> uploadAttachment(File file) async {
    try {
      final token = await _apiService.getToken();
      if (token == null) {
        throw Exception('لم يتم تسجيل الدخول');
      }

      final request = http.MultipartRequest(
        'POST',
        Uri.parse(ApiConfig.chatUpload),
      );

      request.headers['Authorization'] = 'Bearer $token';

      // Determine content type
      String? mimeType;
      final extension = file.path.split('.').last.toLowerCase();
      if (extension == 'jpg' || extension == 'jpeg') {
        mimeType = 'image/jpeg';
      } else if (extension == 'png') {
        mimeType = 'image/png';
      } else if (extension == 'gif') {
        mimeType = 'image/gif';
      } else if (extension == 'pdf') {
        mimeType = 'application/pdf';
      }

      request.files.add(
        await http.MultipartFile.fromPath(
          'file',
          file.path,
          contentType: mimeType != null ? MediaType.parse(mimeType) : null,
        ),
      );

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = _apiService.parseResponse(response.body);
        return {
          'url': data['url'] as String,
          'type': data['type'] as String,
        };
      } else {
        final error = _apiService.parseResponse(response.body);
        throw Exception(error['message'] ?? 'فشل في رفع الملف');
      }
    } catch (e) {
      throw Exception('فشل في رفع الملف: $e');
    }
  }
}

