import 'package:flutter/material.dart';
import '../../config/app_colors.dart';
import '../../models/health_post.dart';
import '../../services/health_post_service.dart';

class HealthPostDetailsScreen extends StatefulWidget {
  final int postId;

  const HealthPostDetailsScreen({super.key, required this.postId});

  @override
  State<HealthPostDetailsScreen> createState() => _HealthPostDetailsScreenState();
}

class _HealthPostDetailsScreenState extends State<HealthPostDetailsScreen> {
  final HealthPostService _healthPostService = HealthPostService();
  HealthPost? _post;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadPost();
  }

  Future<void> _loadPost() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final post = await _healthPostService.getHealthPostDetails(widget.postId);
      setState(() {
        _post = post;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('تفاصيل المنشور'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                      const SizedBox(height: 16),
                      Text(
                        'حدث خطأ',
                        style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _error!,
                        style: TextStyle(fontSize: 14, color: Colors.grey[500]),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadPost,
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : _post == null
                  ? const Center(child: Text('المنشور غير موجود'))
                  : SingleChildScrollView(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Header Image (if available)
                          if (_post!.image != null)
                            Image.network(
                              _post!.image!,
                              width: double.infinity,
                              height: 200,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  height: 200,
                                  color: Colors.grey[300],
                                  child: const Icon(Icons.image, size: 64),
                                );
                              },
                            ),

                          Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Category Badge
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 12,
                                    vertical: 6,
                                  ),
                                  decoration: BoxDecoration(
                                    color: AppColors.primary.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    _post!.categoryArabic,
                                    style: const TextStyle(
                                      color: AppColors.primary,
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),

                                const SizedBox(height: 16),

                                // Title
                                Text(
                                  _post!.title,
                                  style: const TextStyle(
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.textPrimary,
                                  ),
                                ),

                                const SizedBox(height: 16),

                                // Author Info
                                Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 20,
                                      backgroundColor: AppColors.primaryLight,
                                      backgroundImage: _post!.authorAvatar != null
                                          ? NetworkImage(_post!.authorAvatar!)
                                          : null,
                                      child: _post!.authorAvatar == null
                                          ? const Icon(Icons.person, color: Colors.white)
                                          : null,
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            _post!.authorName,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 14,
                                            ),
                                          ),
                                          if (_post!.specializationName != null)
                                            Text(
                                              _post!.specializationName!,
                                              style: TextStyle(
                                                fontSize: 12,
                                                color: Colors.grey[600],
                                              ),
                                            ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),

                                const SizedBox(height: 16),

                                // Meta Info
                                Row(
                                  children: [
                                    Icon(Icons.access_time, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Text(
                                      _post!.formattedDate,
                                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                    ),
                                    const SizedBox(width: 16),
                                    Icon(Icons.visibility, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Text(
                                      '${_post!.views} مشاهدة',
                                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                    ),
                                  ],
                                ),

                                const Divider(height: 32),

                                // Content
                                Text(
                                  _post!.content,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    height: 1.8,
                                    color: AppColors.textPrimary,
                                  ),
                                ),

                                const SizedBox(height: 32),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
    );
  }
}

