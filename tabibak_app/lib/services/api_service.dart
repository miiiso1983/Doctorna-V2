import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String? _token;
  String? _refreshToken;

  // Initialize token from storage
  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('token');
    _refreshToken = prefs.getString('refresh_token');
  }

  // Save tokens
  Future<void> saveTokens(String token, String refreshToken) async {
    _token = token;
    _refreshToken = refreshToken;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);
    await prefs.setString('refresh_token', refreshToken);
  }

  // Clear tokens
  Future<void> clearTokens() async {
    _token = null;
    _refreshToken = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('refresh_token');
  }

  // Get headers
  Map<String, String> _getHeaders({bool includeAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    
    if (includeAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    
    return headers;
  }

  // GET request
  Future<Map<String, dynamic>> get(
    String url, {
    bool requiresAuth = false,
    Map<String, String>? queryParams,
  }) async {
    try {
      final uri = Uri.parse(url).replace(queryParameters: queryParams);
      
      final response = await http.get(
        uri,
        headers: _getHeaders(includeAuth: requiresAuth),
      ).timeout(ApiConfig.connectionTimeout);

      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }

  // POST request
  Future<Map<String, dynamic>> post(
    String url, {
    required Map<String, dynamic> body,
    bool requiresAuth = false,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _getHeaders(includeAuth: requiresAuth),
        body: jsonEncode(body),
      ).timeout(ApiConfig.connectionTimeout);

      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }

  // PUT request
  Future<Map<String, dynamic>> put(
    String url, {
    required Map<String, dynamic> body,
    bool requiresAuth = false,
  }) async {
    try {
      final response = await http.put(
        Uri.parse(url),
        headers: _getHeaders(includeAuth: requiresAuth),
        body: jsonEncode(body),
      ).timeout(ApiConfig.connectionTimeout);

      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }

  // DELETE request
  Future<Map<String, dynamic>> delete(
    String url, {
    bool requiresAuth = false,
  }) async {
    try {
      final response = await http.delete(
        Uri.parse(url),
        headers: _getHeaders(includeAuth: requiresAuth),
      ).timeout(ApiConfig.connectionTimeout);

      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Handle response
  Map<String, dynamic> _handleResponse(http.Response response) {
    final data = jsonDecode(utf8.decode(response.bodyBytes));
    
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return data;
    } else {
      throw ApiException(
        message: data['message'] ?? 'حدث خطأ غير متوقع',
        statusCode: response.statusCode,
        errors: data['errors'],
      );
    }
  }

  // Handle errors
  Exception _handleError(dynamic error) {
    if (error is ApiException) {
      return error;
    }
    return ApiException(
      message: 'فشل الاتصال بالخادم. يرجى التحقق من اتصال الإنترنت.',
      statusCode: 0,
    );
  }

  // Refresh token
  Future<bool> refreshAccessToken() async {
    if (_refreshToken == null) return false;

    try {
      final response = await post(
        ApiConfig.refreshToken,
        body: {'refresh_token': _refreshToken},
      );

      if (response['success'] == true) {
        final data = response['data'];
        await saveTokens(data['token'], data['refresh_token']);
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  bool get isAuthenticated => _token != null;
}

class ApiException implements Exception {
  final String message;
  final int statusCode;
  final Map<String, dynamic>? errors;

  ApiException({
    required this.message,
    required this.statusCode,
    this.errors,
  });

  @override
  String toString() => message;
}

