import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/video_call.dart';
import 'api_service.dart';

class VideoCallService {
  final ApiService _apiService = ApiService();

  // Initiate a video call
  Future<VideoCall> initiateCall(int receiverId) async {
    try {
      final token = await _apiService.getToken();
      if (token == null) {
        throw Exception('غير مصرح. يرجى تسجيل الدخول.');
      }

      final response = await http.post(
        Uri.parse(ApiConfig.videoCallInitiate),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({
          'receiver_id': receiverId,
        }),
      );

      final data = _apiService.parseResponse(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return VideoCall.fromJson(data['data']);
      } else {
        throw Exception(data['message'] ?? 'فشل بدء المكالمة');
      }
    } catch (e) {
      throw Exception('خطأ في بدء المكالمة: $e');
    }
  }

  // Accept a video call
  Future<VideoCall> acceptCall(int callId) async {
    try {
      final token = await _apiService.getToken();
      if (token == null) {
        throw Exception('غير مصرح. يرجى تسجيل الدخول.');
      }

      final response = await http.post(
        Uri.parse(ApiConfig.videoCallAccept(callId)),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = _apiService.parseResponse(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return VideoCall.fromJson(data['data']);
      } else {
        throw Exception(data['message'] ?? 'فشل قبول المكالمة');
      }
    } catch (e) {
      throw Exception('خطأ في قبول المكالمة: $e');
    }
  }

  // Reject a video call
  Future<bool> rejectCall(int callId) async {
    try {
      final token = await _apiService.getToken();
      if (token == null) {
        throw Exception('غير مصرح. يرجى تسجيل الدخول.');
      }

      final response = await http.post(
        Uri.parse(ApiConfig.videoCallReject(callId)),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = _apiService.parseResponse(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return true;
      } else {
        throw Exception(data['message'] ?? 'فشل رفض المكالمة');
      }
    } catch (e) {
      throw Exception('خطأ في رفض المكالمة: $e');
    }
  }

  // End a video call
  Future<bool> endCall(int callId) async {
    try {
      final token = await _apiService.getToken();
      if (token == null) {
        throw Exception('غير مصرح. يرجى تسجيل الدخول.');
      }

      final response = await http.post(
        Uri.parse(ApiConfig.videoCallEnd(callId)),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = _apiService.parseResponse(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return true;
      } else {
        throw Exception(data['message'] ?? 'فشل إنهاء المكالمة');
      }
    } catch (e) {
      throw Exception('خطأ في إنهاء المكالمة: $e');
    }
  }

  // Get call details
  Future<VideoCall> getCallDetails(int callId) async {
    try {
      final token = await _apiService.getToken();
      if (token == null) {
        throw Exception('غير مصرح. يرجى تسجيل الدخول.');
      }

      final response = await http.get(
        Uri.parse(ApiConfig.videoCallDetails(callId)),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = _apiService.parseResponse(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return VideoCall.fromJson(data['data']);
      } else {
        throw Exception(data['message'] ?? 'فشل جلب تفاصيل المكالمة');
      }
    } catch (e) {
      throw Exception('خطأ في جلب تفاصيل المكالمة: $e');
    }
  }

  // Get call history
  Future<List<VideoCall>> getCallHistory({int page = 1, int limit = 20}) async {
    try {
      final token = await _apiService.getToken();
      if (token == null) {
        throw Exception('غير مصرح. يرجى تسجيل الدخول.');
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.videoCallHistory}?page=$page&limit=$limit'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = _apiService.parseResponse(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final List<dynamic> callsJson = data['data'] ?? [];
        return callsJson.map((json) => VideoCall.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'فشل جلب سجل المكالمات');
      }
    } catch (e) {
      throw Exception('خطأ في جلب سجل المكالمات: $e');
    }
  }
}

