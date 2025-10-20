import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import '../../config/app_colors.dart';
import '../../models/review.dart';
import '../../services/review_service.dart';

class DoctorReviewsScreen extends StatefulWidget {
  final int doctorId;
  final String doctorName;

  const DoctorReviewsScreen({
    super.key,
    required this.doctorId,
    required this.doctorName,
  });

  @override
  State<DoctorReviewsScreen> createState() => _DoctorReviewsScreenState();
}

class _DoctorReviewsScreenState extends State<DoctorReviewsScreen> {
  final ReviewService _reviewService = ReviewService();
  final ScrollController _scrollController = ScrollController();

  List<Review> _reviews = [];
  Map<String, dynamic>? _ratingSummary;
  bool _isLoading = true;
  bool _isLoadingMore = false;
  String? _error;
  int _currentPage = 1;
  int _totalPages = 1;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadData();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      if (!_isLoadingMore && _currentPage < _totalPages) {
        _loadMore();
      }
    }
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final summaryFuture = _reviewService.getDoctorRatingSummary(widget.doctorId);
      final reviewsFuture = _reviewService.getDoctorReviews(
        doctorId: widget.doctorId,
        page: 1,
      );

      final results = await Future.wait([summaryFuture, reviewsFuture]);
      final summary = results[0];
      final reviewsData = results[1];

      setState(() {
        _ratingSummary = summary;
        _reviews = reviewsData['reviews'];
        _currentPage = reviewsData['page'];
        _totalPages = reviewsData['pages'];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _loadMore() async {
    setState(() {
      _isLoadingMore = true;
    });

    try {
      final result = await _reviewService.getDoctorReviews(
        doctorId: widget.doctorId,
        page: _currentPage + 1,
      );

      setState(() {
        _reviews.addAll(result['reviews']);
        _currentPage = result['page'];
        _isLoadingMore = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingMore = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('تقييمات ${widget.doctorName}'),
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
                      Text('حدث خطأ', style: TextStyle(fontSize: 18, color: Colors.grey[600])),
                      const SizedBox(height: 8),
                      Text(_error!, style: TextStyle(fontSize: 14, color: Colors.grey[500])),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadData,
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadData,
                  child: ListView(
                    controller: _scrollController,
                    children: [
                      // Rating Summary
                      if (_ratingSummary != null) _buildRatingSummary(),

                      const Divider(height: 1),

                      // Reviews List
                      if (_reviews.isEmpty)
                        Padding(
                          padding: const EdgeInsets.all(32),
                          child: Center(
                            child: Column(
                              children: [
                                Icon(Icons.rate_review_outlined, size: 64, color: Colors.grey[400]),
                                const SizedBox(height: 16),
                                Text(
                                  'لا توجد تقييمات بعد',
                                  style: TextStyle(fontSize: 16, color: Colors.grey[600]),
                                ),
                              ],
                            ),
                          ),
                        )
                      else
                        ..._reviews.map((review) => _buildReviewCard(review)),

                      if (_isLoadingMore)
                        const Padding(
                          padding: EdgeInsets.all(16),
                          child: Center(child: CircularProgressIndicator()),
                        ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildRatingSummary() {
    final totalReviews = _ratingSummary!['totalReviews'] as int;
    final averageRating = _ratingSummary!['averageRating'] as double;
    final fiveStars = _ratingSummary!['fiveStars'] as int;
    final fourStars = _ratingSummary!['fourStars'] as int;
    final threeStars = _ratingSummary!['threeStars'] as int;
    final twoStars = _ratingSummary!['twoStars'] as int;
    final oneStar = _ratingSummary!['oneStar'] as int;

    return Container(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Average Rating
              Expanded(
                child: Column(
                  children: [
                    Text(
                      averageRating.toStringAsFixed(1),
                      style: const TextStyle(
                        fontSize: 48,
                        fontWeight: FontWeight.bold,
                        color: AppColors.primary,
                      ),
                    ),
                    RatingBarIndicator(
                      rating: averageRating,
                      itemBuilder: (context, index) => const Icon(
                        Icons.star,
                        color: Colors.amber,
                      ),
                      itemCount: 5,
                      itemSize: 24,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      '$totalReviews تقييم',
                      style: TextStyle(fontSize: 14, color: Colors.grey[600]),
                    ),
                  ],
                ),
              ),

              const SizedBox(width: 24),

              // Rating Distribution
              Expanded(
                flex: 2,
                child: Column(
                  children: [
                    _buildRatingBar(5, fiveStars, totalReviews),
                    _buildRatingBar(4, fourStars, totalReviews),
                    _buildRatingBar(3, threeStars, totalReviews),
                    _buildRatingBar(2, twoStars, totalReviews),
                    _buildRatingBar(1, oneStar, totalReviews),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRatingBar(int stars, int count, int total) {
    final percentage = total > 0 ? (count / total) : 0.0;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Text('$stars', style: const TextStyle(fontSize: 12)),
          const SizedBox(width: 4),
          const Icon(Icons.star, size: 14, color: Colors.amber),
          const SizedBox(width: 8),
          Expanded(
            child: LinearProgressIndicator(
              value: percentage,
              backgroundColor: Colors.grey[300],
              valueColor: const AlwaysStoppedAnimation<Color>(Colors.amber),
            ),
          ),
          const SizedBox(width: 8),
          Text('$count', style: TextStyle(fontSize: 12, color: Colors.grey[600])),
        ],
      ),
    );
  }

  Widget _buildReviewCard(Review review) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundColor: AppColors.primaryLight,
                  backgroundImage: review.patientAvatar != null
                      ? NetworkImage(review.patientAvatar!)
                      : null,
                  child: review.patientAvatar == null
                      ? const Icon(Icons.person, color: Colors.white)
                      : null,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        review.patientName,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      Text(
                        review.formattedDate,
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                ),
                RatingBarIndicator(
                  rating: review.rating.toDouble(),
                  itemBuilder: (context, index) => const Icon(
                    Icons.star,
                    color: Colors.amber,
                  ),
                  itemCount: 5,
                  itemSize: 16,
                ),
              ],
            ),
            if (review.comment != null && review.comment!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                review.comment!,
                style: const TextStyle(fontSize: 14, height: 1.5),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

