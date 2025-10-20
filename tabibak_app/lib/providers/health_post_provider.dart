import 'package:flutter/material.dart';
import '../models/health_post.dart';
import '../services/health_post_service.dart';

class HealthPostProvider with ChangeNotifier {
  final HealthPostService _healthPostService = HealthPostService();

  List<HealthPost> _posts = [];
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  int _totalPages = 1;
  String? _selectedCategory;

  List<HealthPost> get posts => _posts;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get currentPage => _currentPage;
  int get totalPages => _totalPages;
  bool get hasMore => _currentPage < _totalPages;
  String? get selectedCategory => _selectedCategory;

  Future<void> loadPosts({String? category, bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _posts.clear();
      _selectedCategory = category;
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final result = await _healthPostService.getHealthPosts(
        page: _currentPage,
        category: category,
      );

      if (refresh) {
        _posts = result['posts'];
      } else {
        _posts.addAll(result['posts']);
      }

      _totalPages = result['pages'];
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMore() async {
    if (!hasMore || _isLoading) return;

    _currentPage++;
    await loadPosts(category: _selectedCategory);
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  void reset() {
    _posts = [];
    _currentPage = 1;
    _totalPages = 1;
    _selectedCategory = null;
    _error = null;
    _isLoading = false;
    notifyListeners();
  }
}

