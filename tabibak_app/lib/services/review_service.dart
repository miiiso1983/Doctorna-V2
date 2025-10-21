import '../config/api_config.dart';
import '../models/review.dart';
import 'api_service.dart';

class ReviewService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getDoctorReviews({
    required int doctorId,
    int page = 1,
    int limit = 10,
  }) async {
    // TEMPORARY: Mock data for development
    print('⭐ Mock getDoctorReviews: $doctorId');
    await Future.delayed(const Duration(milliseconds: 500));

    final mockReviews = [
      Review(
        id: 1,
        doctorId: doctorId,
        patientId: 1,
        patientName: 'أحمد علي',
        rating: 5,
        comment: 'طبيب ممتاز ومتعاون جداً. شرح لي حالتي بالتفصيل وأعطاني العلاج المناسب.',
        createdAt: DateTime.now().subtract(const Duration(days: 3)),
      ),
      Review(
        id: 2,
        doctorId: doctorId,
        patientId: 2,
        patientName: 'فاطمة حسن',
        rating: 4,
        comment: 'خدمة جيدة ووقت الانتظار قصير. أنصح بزيارته.',
        createdAt: DateTime.now().subtract(const Duration(days: 7)),
      ),
      Review(
        id: 3,
        doctorId: doctorId,
        patientId: 3,
        patientName: 'محمد سعيد',
        rating: 5,
        comment: 'من أفضل الأطباء الذين زرتهم. خبرة عالية واهتمام بالمريض.',
        createdAt: DateTime.now().subtract(const Duration(days: 14)),
      ),
    ];

    return {
      'reviews': mockReviews,
      'total': mockReviews.length,
      'page': page,
      'pages': 1,
    };

    /* REAL API CODE
    final queryParams = {
      'page': page.toString(),
      'limit': limit.toString(),
    };

    final queryString = queryParams.entries
        .map((e) => '${e.key}=${Uri.encodeComponent(e.value)}')
        .join('&');

    final response = await _apiService.get(
      '${ApiConfig.doctorReviews(doctorId)}?$queryString',
      requiresAuth: false,
    );

    final reviews = (response['data'] as List)
        .map((json) => Review.fromJson(json))
        .toList();

    return {
      'reviews': reviews,
      'total': response['total'] ?? 0,
      'page': response['page'] ?? page,
      'pages': response['pages'] ?? 1,
    };
    */
  }

  Future<Map<String, dynamic>> getDoctorRatingSummary(int doctorId) async {
    final response = await _apiService.get(
      ApiConfig.doctorRatingSummary(doctorId),
      requiresAuth: false,
    );

    return {
      'totalReviews': response['total_reviews'] ?? 0,
      'averageRating': response['average_rating'] != null 
          ? double.parse(response['average_rating'].toString()) 
          : 0.0,
      'fiveStars': response['five_stars'] ?? 0,
      'fourStars': response['four_stars'] ?? 0,
      'threeStars': response['three_stars'] ?? 0,
      'twoStars': response['two_stars'] ?? 0,
      'oneStar': response['one_star'] ?? 0,
    };
  }

  Future<Map<String, dynamic>> addReview({
    required int doctorId,
    required int rating,
    String? comment,
  }) async {
    final response = await _apiService.post(
      ApiConfig.addReview,
      body: {
        'doctor_id': doctorId,
        'rating': rating,
        if (comment != null && comment.isNotEmpty) 'comment': comment,
      },
      requiresAuth: true,
    );

    return response;
  }

  Future<void> deleteReview(int reviewId) async {
    await _apiService.post(
      ApiConfig.deleteReview(reviewId),
      body: {},
      requiresAuth: true,
    );
  }

  Future<Review?> getMyReview(int doctorId) async {
    final response = await _apiService.get(
      ApiConfig.myReview(doctorId),
      requiresAuth: true,
    );

    if (response['review'] != null) {
      return Review.fromJson(response['review']);
    }
    return null;
  }
}

