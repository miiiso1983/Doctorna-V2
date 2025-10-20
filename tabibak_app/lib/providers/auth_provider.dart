import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  final AuthService _authService = AuthService();
  final ApiService _apiService = ApiService();

  User? _user;
  Map<String, dynamic>? _profile;
  bool _isLoading = false;
  String? _error;

  User? get user => _user;
  Map<String, dynamic>? get profile => _profile;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;
  bool get isDoctor => _user?.isDoctor ?? false;
  bool get isPatient => _user?.isPatient ?? false;

  // Initialize
  Future<void> init() async {
    await _apiService.init();
    
    if (_authService.isAuthenticated) {
      await loadCurrentUser();
    }
  }

  // Login
  Future<bool> login({
    required String email,
    required String password,
  }) async {
    _setLoading(true);
    _error = null;

    try {
      final result = await _authService.login(
        email: email,
        password: password,
      );

      _user = result['user'];
      _profile = result['profile'];
      
      _setLoading(false);
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _setLoading(false);
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'حدث خطأ غير متوقع';
      _setLoading(false);
      notifyListeners();
      return false;
    }
  }

  // Register
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String phone,
    required String role,
  }) async {
    _setLoading(true);
    _error = null;

    try {
      final result = await _authService.register(
        name: name,
        email: email,
        password: password,
        phone: phone,
        role: role,
      );

      _user = result['user'];
      
      _setLoading(false);
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _setLoading(false);
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'حدث خطأ غير متوقع';
      _setLoading(false);
      notifyListeners();
      return false;
    }
  }

  // Logout
  Future<void> logout() async {
    await _authService.logout();
    _user = null;
    _profile = null;
    notifyListeners();
  }

  // Load current user
  Future<void> loadCurrentUser() async {
    try {
      final result = await _authService.getCurrentUser();
      _user = result['user'];
      _profile = result['profile'];
      notifyListeners();
    } catch (e) {
      // If failed to load user, logout
      await logout();
    }
  }

  // Update profile
  void updateProfile(Map<String, dynamic> newProfile) {
    _profile = newProfile;
    notifyListeners();
  }

  // Set loading
  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  // Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }
}

