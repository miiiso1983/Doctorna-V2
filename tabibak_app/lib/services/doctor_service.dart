import '../config/api_config.dart';
import '../models/doctor.dart';
import '../models/specialization.dart';
import 'api_service.dart';

class DoctorService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getDoctors({
    int page = 1,
    int limit = 10,
    int? specializationId,
    String? city,
    double? minRating,
    String? search,
  }) async {
    final queryParams = <String, String>{
      'page': page.toString(),
      'limit': limit.toString(),
    };

    if (specializationId != null) {
      queryParams['specialization_id'] = specializationId.toString();
    }
    if (city != null && city.isNotEmpty) {
      queryParams['city'] = city;
    }
    if (minRating != null) {
      queryParams['min_rating'] = minRating.toString();
    }
    if (search != null && search.isNotEmpty) {
      queryParams['search'] = search;
    }

    final response = await _apiService.get(
      ApiConfig.doctorsList,
      queryParams: queryParams,
      requiresAuth: false,
    );

    final doctors = (response['data']['doctors'] as List)
        .map((json) => Doctor.fromJson(json))
        .toList();

    return {
      'doctors': doctors,
      'total': response['data']['total'] ?? 0,
      'page': response['data']['page'] ?? 1,
      'pages': response['data']['pages'] ?? 1,
    };
  }

  Future<Doctor> getDoctorDetails(int doctorId) async {
    final response = await _apiService.get(
      ApiConfig.doctorDetails(doctorId),
      requiresAuth: false,
    );

    return Doctor.fromJson(response['data']);
  }

  Future<List<String>> getDoctorAvailability(int doctorId, DateTime date) async {
    final dateStr = date.toIso8601String().split('T')[0];
    final response = await _apiService.get(
      '${ApiConfig.doctorAvailability(doctorId)}?date=$dateStr',
      requiresAuth: false,
    );

    return (response['data']['available_slots'] as List)
        .map((slot) => slot.toString())
        .toList();
  }

  Future<List<Specialization>> getSpecializations() async {
    final response = await _apiService.get(
      ApiConfig.doctorsSpecializations,
      requiresAuth: false,
    );

    return (response['data'] as List)
        .map((json) => Specialization.fromJson(json))
        .toList();
  }

  Future<List<Doctor>> searchDoctors(String query) async {
    final response = await _apiService.get(
      '${ApiConfig.doctorsSearch}?q=$query',
      requiresAuth: false,
    );

    return (response['data'] as List)
        .map((json) => Doctor.fromJson(json))
        .toList();
  }
}

