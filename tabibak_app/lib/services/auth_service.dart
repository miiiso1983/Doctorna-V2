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
    // TEMPORARY: Mock login for development
    // TODO: Remove this and use real API
    print('🔐 Mock Login: $email');

    // Simulate network delay
    await Future.delayed(const Duration(seconds: 1));

    // Determine role based on email
    String role = 'patient'; // Default
    String name = 'مستخدم تجريبي';

    if (email.contains('admin')) {
      role = 'super_admin';
      name = 'مدير النظام';
    } else if (email.contains('doctor') || email.contains('dr')) {
      role = 'doctor';
      name = 'د. أحمد محمود';
    } else {
      role = 'patient';
      name = 'مريض تجريبي';
    }

    // Mock successful login
    final mockUser = User(
      id: 1,
      name: name,
      email: email,
      phone: '1234567890',
      role: role,
      status: 'active',
      createdAt: DateTime.now(),
    );

    // Save mock tokens
    await _apiService.saveTokens(
      'mock_token_123',
      'mock_refresh_token_456',
    );

    return {
      'user': mockUser,
      'profile': null,
    };

    /* REAL API CODE - Uncomment when API is working
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
    */
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

