import '../config/api_config.dart';
import '../models/user.dart';
import 'api_service.dart';

class AuthService {
  final ApiService _apiService = ApiService();

  // Login
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await _apiService.post(
      ApiConfig.login,
      body: {
        'email': email,
        'password': password,
      },
    );

    if (response['success'] == true) {
      final data = response['data'];
      
      // Save tokens
      await _apiService.saveTokens(
        data['token'],
        data['refresh_token'],
      );

      return {
        'user': User.fromJson(data['user']),
        'profile': data['profile'],
      };
    }

    throw ApiException(
      message: response['message'] ?? 'فشل تسجيل الدخول',
      statusCode: 401,
    );
  }

  // Register
  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String phone,
    required String role,
  }) async {
    final response = await _apiService.post(
      ApiConfig.register,
      body: {
        'name': name,
        'email': email,
        'password': password,
        'phone': phone,
        'role': role,
      },
    );

    if (response['success'] == true) {
      final data = response['data'];
      
      // Save tokens
      await _apiService.saveTokens(
        data['token'],
        data['refresh_token'],
      );

      return {
        'user': User.fromJson(data['user']),
      };
    }

    throw ApiException(
      message: response['message'] ?? 'فشل التسجيل',
      statusCode: 400,
    );
  }

  // Logout
  Future<void> logout() async {
    try {
      await _apiService.post(
        ApiConfig.logout,
        body: {},
        requiresAuth: true,
      );
    } catch (e) {
      // Ignore errors on logout
    } finally {
      await _apiService.clearTokens();
    }
  }

  // Get current user
  Future<Map<String, dynamic>> getCurrentUser() async {
    final response = await _apiService.get(
      ApiConfig.me,
      requiresAuth: true,
    );

    if (response['success'] == true) {
      final data = response['data'];
      return {
        'user': User.fromJson(data['user']),
        'profile': data['profile'],
      };
    }

    throw ApiException(
      message: response['message'] ?? 'فشل جلب بيانات المستخدم',
      statusCode: 401,
    );
  }

  // Check if authenticated
  bool get isAuthenticated => _apiService.isAuthenticated;
}

